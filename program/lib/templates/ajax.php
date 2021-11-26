<?php

/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL
 * Cette application est un doodle-like permettant aux utilisateurs
 * d'effectuer des sondages sur des dates ou bien d'autres criteres
 *
 * L'application est écrite en PHP5,HTML et Javascript
 * et utilise une base de données postgresql et un annuaire LDAP pour l'authentification
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Program\Lib\Templates;

// Utilisation des namespaces

use DateInterval;
use Program\Lib\Request\Localization as l, Program\Lib\Request\Request as r, Program\Lib\Request\Session as s, Program\Lib\Request\Output as o, Program\Lib\Request\Cookie as c;

/**
 * Classe de gestion des appels ajax
 *
 * @package Lib
 * @subpackage Request
 */
class Ajax
{

  /**
   * Si la requête ajax a réussi ou non
   *
   * @var boolean
   */
  private static $success;

  /**
   * Message à retourner à l'appel ajax
   *
   * @var string
   */
  private static $message;

  /**
   * Texte à retourner à l'appel ajax
   *
   * @var string
   */
  private static $text;

  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct()
  {
  }

  /**
   * Execution de la requête
   */
  public static function Process()
  {
    self::$success = true;
    if (o::get_env("action") == ACT_GET_USER_EVENTS) {
      self::get_json_events();
    }

    $csrf_token = trim(strtolower(r::getInputValue("token", POLL_INPUT_GPC)));
    if (!s::validateCSRFToken($csrf_token)) {
      self::$success = false;
      self::$message = "Invalid request";
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::Process Invalid request : $csrf_token");
      self::Send();
    }
    if (o::get_env("action") == ACT_VALIDATE_PROP || o::get_env("action") == ACT_UNVALIDATE_PROP) {
      self::validate_proposal();
    } elseif (o::get_env("action") == ACT_ADD_CALENDAR) {
      self::add_calendar();
    } elseif (o::get_env("action") == ACT_ADD_TENTATIVE_CALENDAR) {
      self::add_tentative_calendar();
    } elseif (o::get_env("action") == ACT_DELETE_TENTATIVES) {
      self::delete_tentatives_calendar();
    } elseif (o::get_env("action") == ACT_GET_VALID_PROPOSALS) {
      self::$text = Show::GetValidateProposalsText(false);
    } else if (o::get_env("action") == ACT_GET_USER_FREEBUSY) {
      self::get_user_freebusy();
    } else {
      self::$success = false;
      self::$message = "Invalid request";
    }
    self::Send();
  }

  /**
   * Réponse à la requête ajax
   */
  public static function Send()
  {
    header('Content-Type: application/json');
    echo o::json_serialize(array("success" => self::$success, "message" => l::g(self::$message, false), "text" => self::$text));
    // set output asap
    ob_flush();
    flush();
    exit();
  }

  /**
   * Validation ou dévalidation de la proposition
   */
  private static function validate_proposal()
  {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::validate_proposal()");
    if (!\Program\Data\Poll::isset_current_poll()) {
      self::$success = false;
      self::$message = "Poll does not exist";
      self::Send();
    } elseif (!\Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      self::$success = false;
      self::$message = "You have no right to access to this resource";
      self::Send();
    }
    // Récupération du calendrier en input
    $cal = r::getInputValue("_c", POLL_INPUT_GET);
    // Récupération des propositions validées
    $validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
    $prop_key = r::getInputValue("prop_key", POLL_INPUT_POST);
    if (o::get_env("action") == ACT_VALIDATE_PROP) {
      if (isset($proposals[$prop_key]) && !isset($validate_proposals[$proposals[$prop_key]])) {
        $validate_proposals[$proposals[$prop_key]] = true;
        \Program\Data\Poll::get_current_poll()->validate_proposals = $validate_proposals;
        if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
          self::$message = "Proposal has been validate for this poll";
          $send_email = r::getInputValue("send_mail", POLL_INPUT_POST);
          if ($send_email == 'true' && \Program\Lib\Mail\Mail::SendValidateProposalNotificationMail(\Program\Data\Poll::get_current_poll(), $prop_key)) {
            self::$message = "Proposal has been validate for this poll. E-mail has been sent to attendees.";
          }
          \Program\Lib\Mail\Mail::SendValidateProposalOrganizerMail(\Program\Data\Poll::get_current_poll(), $prop_key, $send_email == 'true');
        } else {
          self::$success = false;
          self::$message = "Error while modifying the poll";
        }
      } else {
        self::$success = false;
        self::$message = "Error while modifying the poll";
      }
    } elseif (o::get_env("action") == ACT_UNVALIDATE_PROP) {
      if (isset($proposals[$prop_key]) && isset($validate_proposals[$proposals[$prop_key]])) {
        unset($validate_proposals[$proposals[$prop_key]]);
        \Program\Data\Poll::get_current_poll()->validate_proposals = $validate_proposals;
        if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
          self::$message = "Proposal has been unvalidate for this poll";
          // Il faut supprimer l'événement lié
          if (\Program\Data\Poll::get_current_poll()->type == "date" && \Program\Data\EventsList::isset_current_eventslist()) {
            $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
            if (isset($events[$proposals[$prop_key]])) {
              // L'événement existe et la proposition n'est plus validée, il faut donc le supprimer
              if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], $events[$proposals[$prop_key]], null, null, $cal)) {
                // Supprime la date de la liste des events
                unset($events[$proposals[$prop_key]]);
                // Enregistre les modifications sur le current eventslist
                \Program\Data\EventsList::get_current_eventslist()->events = serialize($events);
                \Program\Data\EventsList::get_current_eventslist()->modified_time = date('Y-m-d H:i:s');
                \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
              }
              // TODO: Supprime aussi le tentative des participants si besoin
              if (\Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id && \Config\IHM::$ORGANIZER_DELETE_TENTATIVES_ATTENDEES) {
                // Supprimer automatiquement les tentatives des participants
                $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
                foreach ($responses as $response) {
                  if ($response->user_id != \Program\Data\Poll::get_current_poll()->organizer_id) {
                    $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
                    if ($user->auth == 1) {
                      // Récupère les événements enregistrés depuis la base de données
                      $user_eventslist = \Program\Drivers\Driver::get_driver()->getPollUserEventsList($user->user_id, \Program\Data\Poll::get_current_poll()->poll_id);
                      if (isset($user_eventslist) && $user_eventslist->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
                        $events = unserialize($user_eventslist->events);
                        if (isset($events[$proposals[$prop_key]])) {
                          // L'événement existe et la proposition n'est plus validée, il faut donc le supprimer
                          if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], $events[$proposals[$prop_key]], null, $user, $response->calendar_id)) {
                            // Supprime la date de la liste des events
                            unset($events[$proposals[$prop_key]]);
                            // Enregistre les modifications sur le eventslist de l'utilisateur
                            $user_eventslist->events = serialize($events);
                            $user_eventslist->modified_time = date('Y-m-d H:i:s');
                            \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList($user_eventslist);
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        } else {
          self::$success = false;
          self::$message = "Error while modifying the poll";
        }
      } else {
        self::$success = false;
        self::$message = "Error while modifying the poll";
      }
    }
  }

  /**
   * Ajout de la proposition dans le calendrier
   */
  private static function add_calendar()
  {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_calendar()");
    if (!\Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->locked == 1) {
      self::$success = false;
      self::$message = "Poll does not exist";
      self::Send();
    } elseif (!\Program\Data\User::isset_current_user()) {
      self::$success = false;
      self::$message = "You have no right to access to this resource";
      self::Send();
    } elseif (!\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
      self::$success = false;
      self::$message = "";
      self::Send();
    }
    // Charge le eventslist depuis la base de données
    $new_eventslist = false;
    if (!\Program\Data\EventsList::isset_current_eventslist()) {
      $new_eventslist = true;
      \Program\Data\EventsList::set_current_eventslist(new \Program\Data\EventsList(['poll_id' => \Program\Data\Poll::get_current_poll()->poll_id, 'user_id' => \Program\Data\User::get_current_user()->user_id, 'events' => "", 'events_status' => "", 'settings' => "", 'modified_time' => date('Y-m-d H:i:s')]));
      $events = [];
    } else {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
    }
    // Récupération du calendrier en input
    $cal = r::getInputValue("_c", POLL_INPUT_GET);
    if (!isset($cal)) {
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      // Parcourir les réponses pour positionner le calendar_id
      foreach ($responses as $response) {
        if ($response->user_id == \Program\Data\User::get_current_user()->user_id) {
          $cal = $response->calendar_id;
        }
      }
    }
    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
    $prop_key = r::getInputValue("prop_key", POLL_INPUT_POST);
    $part_status = r::getInputValue("part_status", POLL_INPUT_POST);
    // Récupération des propositions validées
    $validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;

    // Parcours les propositions du sondage
    foreach ($proposals as $proposal_key => $proposal) {
      // La proposition n'est pas validée, il faut peut être la supprimer
      if (!isset($validate_proposals[$proposal]) && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, null, \Program\Data\Event::STATUS_TENTATIVE, $cal)) {
        // L'événement existe et la proposition n'est pas validée, il faut donc le supprimer
        \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, null, $cal);
        // Supprime la date de la liste des events
        if (isset($events[$proposal])) {
          unset($events[$proposal]);
        }
      }
    }
    // Supprimer les provisoires qui ne serait plus dans les propositions
    foreach ($events as $proposal => $event_uid) {
      if (!in_array($proposal, $proposals) && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, $event_uid, null, null, null, $cal)) {
        // L'événement existe, il faut donc le supprimer
        if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, $event_uid, null, null, $cal)) {
          // Supprime la date de la liste des events
          unset($events[$proposal]);
        }
      }
    }
    if (isset($proposals[$prop_key]) && isset($validate_proposals[$proposals[$prop_key]])) {
      $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, null, null, $part_status, $cal);
      if (!is_null($event_uid)) {
        self::$message = "Event has been saved in your calendar";
        $events[$proposals[$prop_key]] = $event_uid;
        if (isset($part_status)) {
          $events_part_status = \Program\Data\EventsList::get_current_eventslist()->events_part_status;
          $events_part_status[$proposals[$prop_key]] = $part_status;
          \Program\Data\EventsList::get_current_eventslist()->events_part_status = $events_part_status;
        } else {
          \Program\Data\EventsList::get_current_eventslist()->events_part_status = "";
        }
      } else {
        self::$message = "Error while saving the event in your calendar";
        self::$success = false;
      }
    } else {
      self::$message = "The date is no more validate, please refresh the poll";
      self::$success = false;
      self::Send();
    }
    // Enregistre les modifications sur le current eventslist
    \Program\Data\EventsList::get_current_eventslist()->events = serialize($events);
    \Program\Data\EventsList::get_current_eventslist()->events_status = \Program\Data\Event::STATUS_CONFIRMED;
    \Program\Data\EventsList::get_current_eventslist()->modified_time = date('Y-m-d H:i:s');
    if ($new_eventslist) {
      \Program\Drivers\Driver::get_driver()->addPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
    } else {
      \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
    }

    if (\Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id && \Config\IHM::$ORGANIZER_DELETE_TENTATIVES_ATTENDEES) {
      // Supprimer automatiquement les tentatives des participants
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      foreach ($responses as $response) {
        if ($response->user_id != \Program\Data\Poll::get_current_poll()->organizer_id) {
          $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
          if ($user->auth == 1) {
            // Récupère les événements enregistrés depuis la base de données
            $user_eventslist = \Program\Drivers\Driver::get_driver()->getPollUserEventsList($user->user_id, \Program\Data\Poll::get_current_poll()->poll_id);
            if (isset($user_eventslist) && $user_eventslist->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
              $events = unserialize($user_eventslist->events);
              // Parcours les événéments pour supprimer ceux qui doivent l'être
              // Parcours les propositions du sondage
              foreach ($proposals as $proposal_key => $proposal) {
                // La proposition n'est pas validée, il faut peut être la supprimer
                if (!isset($validate_proposals[$proposal]) && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, $user, \Program\Data\Event::STATUS_TENTATIVE, $response->calendar_id)) {
                  // L'événement existe et la proposition n'est pas validée, il faut donc le supprimer
                  \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, $user, $response->calendar_id);
                  // Supprime la date de la liste des events
                  if (isset($events[$proposal])) {
                    unset($events[$proposal]);
                  }
                }
              }
              // Supprimer les provisoires qui ne serait plus dans les propositions
              foreach ($events as $proposal => $event_uid) {
                if (!in_array($proposal, $proposals) && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, $event_uid, null, $user, null, $response->calendar_id)) {
                  // L'événement existe, il faut donc le supprimer
                  if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, $event_uid, null, $user, $response->calendar_id)) {
                    // Supprime la date de la liste des events
                    unset($events[$proposal]);
                  }
                }
              }
              // Doit on créer l'événement en provisoire ?
              // Unserialize les réponses de l'utilisateur
              $resp = unserialize($response->response);
              if (isset($resp[$proposals[$prop_key]]) && !isset($events[$proposals[$prop_key]])) {
                // La proposition est validée, acceptée mais pas dans le calendrier, il faut la créer en provisoire
                $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, $user, \Program\Data\Event::STATUS_TENTATIVE, null, $response->calendar_id, true);
                // Enregistre l'event_uid dans la table des events
                if (!is_null($event_uid)) {
                  $events[$proposals[$prop_key]] = $event_uid;
                }
              } else if (isset($resp[$proposals[$prop_key]])) {
                // Modifier le nom de la proposition
                \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, $user, \Program\Data\Event::STATUS_TENTATIVE, null, $response->calendar_id, true);
              }

              // Enregistre les modifications sur le eventslist de l'utilisateur
              $user_eventslist->events = serialize($events);
              $user_eventslist->modified_time = date('Y-m-d H:i:s');
              \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList($user_eventslist);
            }
          }
        }
      }
    }
  }

  /**
   * Création des propositions de l'utilisateur en provisoire
   */
  private static function add_tentative_calendar()
  {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar()");
        if (!\Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->locked == 0) {
          self::$success = false;
          self::$message = "Poll does not exist";
          \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar() Error : Poll does not exist");
          self::Send();
        } elseif (!\Program\Data\User::isset_current_user()) {
          self::$success = false;
          self::$message = "You have no right to access to this resource";
          \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar() Error : You have no right to access to this resource");
          self::Send();
        } elseif (!\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
          self::$success = false;
          self::$message = "";
          self::Send();
        }
        $cal = r::getInputValue("_c", POLL_INPUT_GET);
        $prop_keys = r::getInputValue("prop_keys", POLL_INPUT_POST);

        \Program\Lib\Utils\Utils::add_tentative_calendar($cal,$prop_keys);

  }

  /**
   * Suppression des événements provisoires
   */
  private static function delete_tentatives_calendar()
  {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::delete_tentatives_calendar()");
    if (!\Program\Data\Poll::isset_current_poll()) {
      self::$success = false;
      self::$message = "Poll does not exist";
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::delete_tentatives_calendar() Error : Poll does not exist");
      self::Send();
    } elseif (!\Program\Data\User::isset_current_user()) {
      self::$success = false;
      self::$message = "You have no right to access to this resource";
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::delete_tentatives_calendar() Error : You have no right to access to this resource");
      self::Send();
    } elseif (!\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
      self::$success = false;
      self::$message = "";
      self::Send();
    }
    // Récupération du calendrier en input
    $cal = r::getInputValue("_c", POLL_INPUT_GET);
    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
    // Charge le eventslist depuis la base de données
    $new_eventslist = false;
    if (!\Program\Data\EventsList::isset_current_eventslist()) {
      $new_eventslist = true;
      $events = [];
    } else {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
    }

    // Parcours les propositions du sondage
    foreach ($proposals as $prop_key => $proposal) {
      // La proposition n'est pas validée, il faut peut être la supprimer
      if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, null, \Program\Data\Event::STATUS_TENTATIVE, $cal)) {
        // L'événement existe, il faut donc le supprimer
        if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, null, $cal)) {
          // Supprime la date de la liste des events
          if (isset($events[$proposals[$prop_key]])) {
            unset($events[$proposals[$prop_key]]);
          }
        }
      }
    }
    
    // Supprimer les provisoires restants
    if (count($events) > 0 && \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
      foreach ($events as $proposal => $event_uid) {
        if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, $event_uid, null, null, \Program\Data\Event::STATUS_TENTATIVE, $cal)) {
          // L'événement existe, il faut donc le supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, $event_uid, null, null, $cal)) {
            // Supprime la date de la liste des events
            unset($events[$proposal]);
          }
        }
      }
    }

    // Supprime le eventslist qui doit être vide
    if (!$new_eventslist && \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
      \Program\Drivers\Driver::get_driver()->deletePollUserEventsList(\Program\Data\User::get_current_user()->user_id, \Program\Data\Poll::get_current_poll()->poll_id);
    }
  }

  /**
   * Génération de l'affichage des freebusy pour l'utilisateur courant
   */
  private static function get_user_freebusy()
  {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::get_user_freebusy()");
    if (!\Program\Data\Poll::isset_current_poll()) {
      self::$success = false;
      self::$message = "Poll does not exist";
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::get_user_freebusy() Error : Poll does not exist");
      self::Send();
    } elseif (!\Program\Data\User::isset_current_user()) {
      self::$success = false;
      self::$message = "You have no right to access to this resource";
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::get_user_freebusy() Error : You have no right to access to this resource");
      self::Send();
    } elseif ((\Program\Data\Poll::get_current_poll()->type != "date" && \Program\Data\Poll::get_current_poll()->type != "rdv") || !\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GET_FREEBUSY) {
      self::$success = false;
      self::$message = "Calendar is not supported";
      self::Send();
    }
    try {
      // Récupération du calendrier en input
      $cal = r::getInputValue("_c", POLL_INPUT_GET);
      // Récupération du timezone depuis le Driver
      $timezone = \Program\Lib\Event\Drivers\Driver::get_driver()->get_user_timezone();
      // Récupération des événements depuis le Driver
      $events = \Program\Lib\Event\Drivers\Driver::get_driver()->get_user_freebusy(new \DateTime(\Program\Data\Poll::get_current_poll()->date_start, $timezone), new \DateTime(\Program\Data\Poll::get_current_poll()->date_end, $timezone), null, $cal);
      // Liste des propositions du sondage
      $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
      // List des freebusy
      $freebusy_list = [];

      // Parcours les événements
      foreach ($events as $event) {
        // Parcours les propositions pour la comparaison
        foreach ($proposals as $prop_key => $prop_value) {
          if (strpos($prop_value, ' - ')) {
            $prop = explode(' - ', $prop_value, 2);
            if ($event->allday) {
              $prop_start = new \DateTime($prop[0], new \DateTimeZone('UTC'));
              $prop_end = new \DateTime($prop[1], new \DateTimeZone('UTC'));
            } else {
              $prop_start = new \DateTime($prop[0], $timezone);
              $prop_end = new \DateTime($prop[1], $timezone);
            }
          } else {
            if ($event->allday) {
              $prop_start = new \DateTime($prop_value, new \DateTimeZone('UTC'));
            } else {
              $prop_start = new \DateTime($prop_value, $timezone);
            }
            $prop_end = clone $prop_start;
            $prop_end->add(new \DateInterval('P1D'));
          }
          if ($event->start <= $prop_start && $event->end > $prop_start || $event->start < $prop_end && $event->end >= $prop_end || $event->start >= $prop_start && $event->start < $prop_end || $event->end > $prop_start && $event->end <= $prop_end) {
            if (!isset($freebusy[$prop_key])) {
              $freebusy[$prop_key] = array();
            }
            if (isset($event->status))
              $status = ucfirst(strtolower($event->status));
            else
              $status = 'None';
            if ($event->allday) {
              $event_end_date = clone $event->end;
              $event_end_date->sub(new \DateInterval('P1D'));
              if ($event->start->format('d/m/Y') == $event_end_date->format('d/m/Y')) {
                $date = $event->start->format('d/m/Y');
              } else {
                $date = $event->start->format('d/m/Y') . ' - ' . $event_end_date->format('d/m/Y');
              }
            } else {
              if ($event->start->format('dmY') != $event->end->format('dmY')) {
                $date = $event->start->format('d/m/Y H:i') . ' - ' . $event->end->format('d/m/Y H:i');
              } else {
                $date = $event->start->format('H:i') . ' - ' . $event->end->format('H:i');
              }
            }
            // Ajoute l'évènement à la liste des freebusy
            $freebusy[$prop_key][] = array('status' => $status, 'title' => $event->title, 'date' => $date);
          }
        }
      }
      // Parcourir les propositions
      foreach ($proposals as $prop_key => $prop_value) {
        $status = 'None';
        $title = '';
        $count = '';
        if (isset($freebusy[$prop_key])) {
          foreach ($freebusy[$prop_key] as $event) {
            if ($status == 'None' || $event['status'] == 'Confirmed')
              $status = $event['status'];
            if ($title != "")
              $title .= " / ";
            $title .= $event['date'] . " " . $event['title'];
          }
          $count = ' (' . count($freebusy[$prop_key]) . ')';
          // Si c'est annulé, on le rend libre
          if ($status == 'Cancelled')
            $status = 'None';
        } else {
          $title = l::g($status, false);
        }
        $freebusy_list[] = ["title" => $title, "class" => "freebusy_" . strtolower($status) . " freebusy_prop_$prop_key customtooltip_bottom tooltipstered", "text" => l::g($status, false) . $count, "prop_key" => $prop_key];
      }
      // Send data
      self::$success = true;
      self::$message = "";
      self::$text = $freebusy_list;
      self::Send();
    } catch (\Exception $ex) {
      return;
    }
  }

  /**
   * Récupération des données de l'agenda Mélanie2 pour l'utilisateur courant
   */
  private static function get_json_events()
  {
    if (!\Program\Data\User::isset_current_user()) {
      self::$success = false;
      self::$message = "You have no right to access to this resource";
      self::Send();
    } elseif (!\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GET_FREEBUSY) {
      self::$success = false;
      self::$message = "";
      self::Send();
    }
    // Retourne du JSON
    header('Content-Type: application/json');
    // Récupération des paramètres
    $start = trim(strtolower(r::getInputValue("start", POLL_INPUT_POST)));
    $end = trim(strtolower(r::getInputValue("end", POLL_INPUT_POST)));
    // Génération des DateTime
    $startDate = new \DateTime($start);
    $endDate = new \DateTime($end);

    // Récupération des événements depuis le Driver
    $events = \Program\Lib\Event\Drivers\Driver::get_driver()->get_user_freebusy($startDate, $endDate);
    // Récupération du timezone depuis le Driver
    $timezone = \Program\Lib\Event\Drivers\Driver::get_driver()->get_user_timezone();
    $result = [];
    // Parcour les événements
    foreach ($events as $event) {
      $_e = [];
      $_e['id'] = $event->uid;
      $_e['title'] = $event->title;
      $_e['allDay'] = $event->allday;
      if ($_e['allDay']) {
        $event->end->sub(new \DateInterval('P1D'));
      }
      $_e['start'] = $event->start->format('Y-m-d H:i:s');
      if (!$event->allday) {
        $_e['end'] = $event->end->format('Y-m-d H:i:s');
      }
      else {
        $endDateAllDay = $event->end->add(new DateInterval('P1D'));
        $_e['end'] = $endDateAllDay->format('Y-m-d H:i:s');
      }
      if (isset($event->status)) {
        switch ($event->status) {
          case \Program\Data\Event::STATUS_CONFIRMED:
            $_e['color'] = '#DBE3FC';
            $_e['borderColor'] = '#96A2C4';
            $_e['textColor'] = '#14214A';
            break;
          case \Program\Data\Event::STATUS_TENTATIVE:
            $_e['color'] = '#F5F5E0';
            $_e['borderColor'] = '#B6B77C';
            $_e['textColor'] = '#4B2112';
            break;
          case \Program\Data\Event::STATUS_CANCELLED:
            $_e['title'] = '[' . l::g('Cancelled', false) . '] ' . $_e['title'];
          default:
            $_e['color'] = '#E2F2D0';
            $_e['borderColor'] = '#94A97B';
            $_e['textColor'] = '#006100';
            break;
        }
      } else {
        $_e['color'] = '#E2F2D0';
        $_e['borderColor'] = '#94A97B';
        $_e['textColor'] = '#006100';
      }
      $result[] = $_e;
    }
    echo o::json_serialize($result);
    // set output asap
    ob_flush();
    flush();
    exit();
  }
}
