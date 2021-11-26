<?php

namespace Program\Lib\Utils;

use Program\Lib\Request\Request as Request, Program\Lib\Request\Session as Session, Program\Lib\Request\Output as o;

class Utils
{

  public function __construct()
  {
  }

  /**
   * Génération d'une chaine de caractères aléatoire
   * @return string
   */
  public static function random_string($nbCharacters = 20)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $nbCharacters; $i++) {
      $randstring .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randstring;
  }

  public static function add_tentative_calendar($cal, $prop_keys, $user = null)
  {

    // Récupération du calendrier en input
    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
    $nb_attendeed_per_prop = \Program\Data\Poll::get_nb_attendees_per_prop(\Program\Data\Poll::get_current_poll());
    // Charge le eventslist depuis la base de données
    $new_eventslist = false;
    if (!\Program\Data\EventsList::isset_current_eventslist()) {
      $new_eventslist = true;
      \Program\Data\EventsList::set_current_eventslist(new \Program\Data\EventsList(['poll_id' => \Program\Data\Poll::get_current_poll()->poll_id, 'user_id' => \Program\Data\User::get_current_user()->user_id, 'events' => "", 'events_status' => "", 'settings' => "", 'modified_time' => date('Y-m-d H:i:s')]));
      $events = [];
    } else {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
    }

    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Utils::add_tentative_calendar() prop_keys : " . var_export($prop_keys, true));
    // Parcours les propositions du sondage
    foreach ($proposals as $prop_key => $proposal) {
      if (is_array($prop_keys) && in_array($prop_key, $prop_keys)) {
        // La proposition est validée, on l'ajoute en provisoire
        if (\Program\Data\Poll::get_current_poll()->type == 'date') {
          $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, null, \Program\Data\Event::STATUS_TENTATIVE, null, $cal);
        } elseif (\Program\Data\Poll::get_current_poll()->type == 'rdv') {
          //On l'ajoute en confirmé dans l'agenda de l'organisateur  
          if (\Program\Data\Poll::get_current_poll()->prop_in_agenda) {
            //Uniquement s'il souhaite les afficher dans son agenda
            \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), null, null, null, true);
          }
          //On l'ajoute en confirmé dans l'agenda de l'utilisateur passé en paramètre (null par défaut)
          if (\Program\Data\Poll::get_current_poll()->organizer_id != $user->user_id) {
            $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, $user, null, \Program\Data\Event::PARTSTAT_ACCEPTED, $cal, true);
          }
        }
        // Enregistre l'event_uid dans la table des events
        if (!is_null($event_uid)) {
          $events[$proposals[$prop_key]] = $event_uid;
        }
      }
      //Dans le cas d'une modification de réponse par un utilisateur non authentifié on supprime la proposition uniquement dans l'agenda de l'organisateur
      elseif (Session::is_set("user_noauth_old_response")) {
        if ($proposals[$prop_key] == Session::get("user_noauth_old_response")) {
          // L'événement existe, il faut donc le supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), null, $cal)) {
            // Supprime la date de la liste des events
            if (\Program\Data\Poll::get_current_poll()->type == 'rdv') {
              //On le remet en provisoire uniquement si l'organisateur souhaite les afficher dans son agenda
              if (\Program\Data\Poll::get_current_poll()->prop_in_agenda) {
                //S'il y a des participants sur cet évènement
                if (isset($nb_attendeed_per_prop[$prop_key])) {
                  \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, $user, $cal);
                  \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), null, null, null, true);
                } else {
                  \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposal, null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), \Program\Data\Event::STATUS_TENTATIVE, null, null, null);
                }
              }
            }
          }
        }
      } else {
        // La proposition n'est pas validée, il faut peut être la supprimer
        if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, $user, null, $cal)) {
          // L'événement existe, il faut donc le supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, $user, $cal)) {
            // Supprime la date de la liste des events
            if (isset($events[$proposals[$prop_key]])) {
              if (\Program\Data\Poll::get_current_poll()->type == 'rdv') {
                //On le remet en provisoire uniquement si l'organisateur souhaite les afficher dans son agenda
                if (\Program\Data\Poll::get_current_poll()->prop_in_agenda) {
                  if (isset($nb_attendeed_per_prop[$prop_key])) {
                    //On supprime puis remet l'évènement afin d'enlever le participant modifié
                    \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, $user, $cal);
                    \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), null, null, null, true);
                  } else {
                    //Si aucun participant on remet l'évènement en provisoire
                    \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposal, null, \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), \Program\Data\Event::STATUS_TENTATIVE, null, null, null);
                  }
                }
                if (isset($user)) {
                  \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key], (isset($events[$proposals[$prop_key]]) ? $events[$proposals[$prop_key]] : null), null, $user, $cal);
                  if (isset($events[$proposal])) {
                    unset($events[$proposal]);
                }
                }
              } else {
                unset($events[$proposals[$prop_key]]);
              }
            }
          }
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
    // Enregistre les modifications sur le current eventslist
    \Program\Data\EventsList::get_current_eventslist()->events = serialize($events);
    \Program\Data\EventsList::get_current_eventslist()->events_status = \Program\Data\Event::STATUS_TENTATIVE;
    \Program\Data\EventsList::get_current_eventslist()->events_part_status = "";
    \Program\Data\EventsList::get_current_eventslist()->modified_time = date('Y-m-d H:i:s');
    if ($new_eventslist) {
      \Program\Drivers\Driver::get_driver()->addPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
    } else {
      \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
    }
  }

  public static function add_rdv_props_to_calendar($new_proposals = [])
  {
    //On ajoute l'évènement en provisoire dans le calendrier
    if (\Program\Data\Poll::get_current_poll()->type == "rdv" && \Program\Data\Poll::get_current_poll()->prop_in_agenda) {
      $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
      $new_eventslist = false;
      if (!\Program\Data\EventsList::isset_current_eventslist()) {
        $new_eventslist = true;
        \Program\Data\EventsList::set_current_eventslist(new \Program\Data\EventsList(['poll_id' => \Program\Data\Poll::get_current_poll()->poll_id, 'user_id' => \Program\Data\User::get_current_user()->user_id, 'events' => "", 'events_status' => "", 'settings' => "", 'modified_time' => date('Y-m-d H:i:s')]));
        $events = [];
      } else {
        $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
      }

      $unserialized_responses = [];
      $user_title = [];
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);

      foreach ($responses as $response) {
        // Unserialize les réponses de l'utilisateur
        $unserialized_responses[$response->user_id] = array_key_first(unserialize($response->response));
      }

      foreach ($proposals as $prop_key => $proposal) {
        if (in_array($proposal, $unserialized_responses)) {
          $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, null, \Program\Data\Event::STATUS_CONFIRMED);
        } else {
          $event_uid = \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, null, \Program\Data\Event::STATUS_TENTATIVE);
        }
        if (!is_null($event_uid)) {
          $events[$proposals[$prop_key]] = $event_uid;
        }
      }



      foreach ($events as $proposal => $event_uid) {
        if (!in_array($proposal, $proposals) && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, $event_uid)) {
          // L'événement existe, il faut donc le supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, $event_uid)) {
            // Supprime la date de la liste des events
            unset($events[$proposal]);
          }
        }
      }
      // Enregistre les modifications sur le current eventslist
      \Program\Data\EventsList::get_current_eventslist()->events = serialize($events);
      \Program\Data\EventsList::get_current_eventslist()->events_status = \Program\Data\Event::STATUS_TENTATIVE;
      \Program\Data\EventsList::get_current_eventslist()->events_part_status = "";
      \Program\Data\EventsList::get_current_eventslist()->modified_time = date('Y-m-d H:i:s');
      if ($new_eventslist) {
        \Program\Drivers\Driver::get_driver()->addPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
      } else {
        \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
      }
    }
    //On supprime les propositions dans l'agenda si l'organisateur à décoché la case
    else if (\Program\Data\Poll::get_current_poll()->type == "rdv" && !\Program\Data\Poll::get_current_poll()->prop_in_agenda) {
      \Program\Data\EventsList::get_current_eventslist()->events_status = \Program\Data\Event::STATUS_TENTATIVE;
      \Program\Lib\Templates\Edit_end::delete_tentatives_calendar($proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals));
    }
  }
}
