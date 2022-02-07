<?php

/**
 * Classe pour le driver de gestion des évènements pour l'application
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

namespace Program\Lib\Event\Drivers\Melanie2;

use LibMelanie\Api as Api,
  Program\Lib\Request\Output as Output,
  Program\Lib\Request\Request as Request,
  Program\Lib\Request\Localization as Localization,
  Program\Lib\Request\Session as Session;

/**
 * Classe pour le driver de gestion des évènements
 * les drivers doivent être implémentée à partir de cette classe
 */
class Melanie2 extends \Program\Lib\Event\Drivers\Driver
{

  /**
   * Est-ce que le driver permet l'affichage des freebusy
   *
   * @var boolean
   */
  public $CAN_GET_FREEBUSY = true;

  /**
   * Est-ce que le driver permet d'écrire dans le calendrier
   *
   * @var boolean
   */
  public $CAN_WRITE_CALENDAR = true;

  /**
   * Est-ce que le driver permet de générer un fichier ICS
   *
   * @var boolean
   */
  public $CAN_GENERATE_ICS = true;

  /**
   * Evenement Melanie2
   *
   * @var \LibMelanie\Api\Melanie2\Event
   */
  private static $event;

  /**
   * Evenement Melanie2
   *
   * @var \LibMelanie\Api\Melanie2\User
   */
  private static $user;

  /**
   * Timezone de l'utilisateur
   *
   * @var string
   */
  private static $timezone;

  /**
   * Constructeur du driver
   * Permet d'instancier les logs de l'ORM Mélanie2
   */
  function __construct()
  {
    $debuglog = function ($message) {
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "[LibM2] $message");
    };
    $infolog = function ($message) {
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "[LibM2] $message");
    };
    $errorlog = function ($message) {
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::ERROR, "[LibM2] $message");
    };
    \LibMelanie\Log\M2Log::InitDebugLog($debuglog);
    \LibMelanie\Log\M2Log::InitErrorLog($errorlog);
    \LibMelanie\Log\M2Log::InitInfoLog($infolog);
  }

  /**
   * Génération de la liste des disponibilités pour l'utilisateur
   * Les disponibilités peuvent se situer entre une date de début et une date de fin
   * Retourne une liste d'objet Event
   *
   * @param \DateTime $start Début des disponibilités
   * @param \DateTime $end Fin des disponibilités
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return \Program\Data\Event[] Tableau contenant la liste des évènements
   */
  public function get_user_freebusy($start = null, $end = null, $user = null, $calendar = null)
  {
    // Récupération de l'utilisateur
    if (!isset($user)) {
      // Si il n'y a pas d'utilisateur connecté
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      } else {
        return [];
      }
    }
    // Utilisateur Cerbère
    if ($user->is_cerbere) {
      return parent::get_user_freebusy($start, $end, $user, $calendar);
    }

    if (!isset(self::$user) || self::$user->uid != $user->username) {
      // Création de l'utilisateur M2
      self::$user = new Api\Melanie2\User();
      self::$user->uid = $user->username;
    }
    // Récupération du calendrier
    $_calendar = new Api\Melanie2\Calendar(self::$user);
    if (isset($calendar) && is_object($calendar)) {
      $_calendar->id = $calendar->id;
    } else if (isset($calendar)) {
      $_calendar->id = $calendar;
    } else {
      $_calendar->id = self::$user->uid;
    }
    // Si le calendrier existe et que les droits sont bons (lecture)
    if (!$_calendar->load() || !$_calendar->asRight(\LibMelanie\Config\ConfigMelanie::READ)) {
      return [];
    }

    // Récupère les évènements
    $_events = $_calendar->getRangeEvents($start->format('Y-m-d'), $end->format('Y-m-d'));
    $events = [];
    foreach ($_events as $_event) {
      try {
        // Si récurrence, on utilise l'objet VObject
        $vcalendar = $_event->vcalendar;
        if ($_event->recurrence->type != Api\Melanie2\Recurrence::RECURTYPE_NORECUR) {
          $vcalendar->expand($start, $end, $this->get_user_timezone($user));
        }
        // Parcourir les évènements
        if (isset($vcalendar->VEVENT) && count($vcalendar->VEVENT)) {
          foreach ($vcalendar->VEVENT as $vevent) {
            if (!isset($vevent->DTSTART) || !isset($vevent->DTEND)) {
              continue;
            }
            $event = new \Program\Data\Event(['uid' => $vevent->UID->getValue(), 'start' => $vevent->DTSTART->getDateTime(), 'end' => $vevent->DTEND->getDateTime(), 'status' => isset($vevent->STATUS) ? $vevent->STATUS->getValue() : \Program\Data\Event::STATUS_NONE, 'title' => $vevent->SUMMARY->getValue()]);
            if ($event->start->format('H:i:s') == '00:00:00' && $event->end->format('H:i:s') == '00:00:00') {
              $event->allday = true;
            } else {
              $event->allday = false;
              $event->start->setTimezone($this->get_user_timezone($user));
              $event->end->setTimezone($this->get_user_timezone($user));
            }
            // Ajoute l'évènement au tableau
            $events[] = $event;
          }
        }
      } catch (\Exception $ex) {
        continue;
      }
    }
    return $events;
  }

  /**
   * Retourne le timezone de l'utilisateur
   *
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \DateTimeZone Timezone de l'utilisateur
   */
  public function get_user_timezone($user = null)
  {
    if (isset(self::$timezone)) {
      return self::$timezone;
    }
    // Récupération de l'utilisateur
    if (!isset($user)) {
      // Si il n'y a pas d'utilisateur connecté
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      } else {
        return false;
      }
    }

    // Récupère le timezone depuis la base de données
    self::$timezone = new \DateTimeZone($user->timezone);
    return self::$timezone;
  }

  /**
   * Enregistre dans l'agenda de l'utilisateur l'évènement lié au sondage
   *
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status \Program\Data\Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @param boolean $selected_date Date retenue par l'organisateur ?
   * @return string UID de l'événement créé si OK, null sinon
   */
  public function add_to_calendar($date, $poll = null, $user = null, $status = null, $part_status = null, $calendar = null, $selected_date = false, $user_title = null)
  {
    try {
      // Récupération du sondage
      if (!isset($poll)) {
        if (\Program\Data\Poll::isset_current_poll()) {
          $poll = \Program\Data\Poll::get_current_poll();
        } else {
          return null;
        }
      }
      // Récupération de l'utilisateur
      if (!isset($user)) {
        if (\Program\Data\User::isset_current_user()) {
          $user = \Program\Data\User::get_current_user();
        } else {
          return null;
        }
      }

      if (!isset(self::$user) || self::$user->uid != $user->username) {
        // Création de l'utilisateur M2
        self::$user = new Api\Melanie2\User();
        self::$user->uid = $user->username;
      }
      // Récupération du calendrier
      $_calendar = new Api\Melanie2\Calendar(self::$user);
      if (isset($calendar) && is_object($calendar)) {
        $_calendar->id = $calendar->id;
      } else if (isset($calendar)) {
        $_calendar->id = $calendar;
      } else {
        $_calendar->id = self::$user->uid;
      }
      // Si le calendrier existe et que les droits sont bons (écriture)
      if (!$_calendar->load() || !$_calendar->asRight(\LibMelanie\Config\ConfigMelanie::WRITE)) {
        return [];
      }

      // Initialisation de l'événement
      $this->init_event($date, $poll, $user, $status, $part_status, $selected_date, $user_title);
      // Définition des élèments manquants de l'évènement
      self::$event->setUserMelanie(self::$user);
      self::$event->setCalendarMelanie($_calendar->getObjectMelanie());
      self::$event->owner = self::$user->uid;
      self::$event->created = time();
      $ret = self::$event->save();
      if (!is_null($ret)) {
        return self::$event->uid;
      } else {
        return null;
      }
    } catch (\Exception $ex) {
      return null;
    }
  }

  /**
   * Liste les calendriers accessibles pour l'utilisateur (en lecture/écriture)
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \Program\Data\Calendar[] Tableau contenant la liste des calendriers
   */
  public function list_user_calendars($user = null)
  {
    // Récupération de l'utilisateur
    if (!isset($user)) {
      // Si il n'y a pas d'utilisateur connecté
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      } else {
        return [];
      }
    }

    if (!isset(self::$user) || self::$user->uid != $user->username) {
      // Création de l'utilisateur M2
      self::$user = new Api\Melanie2\User();
      self::$user->uid = $user->username;
    }

    // Récupération de la liste des calendriers Mélanie2
    $_calendars = self::$user->getSharedCalendars();
    $calendars = [];

    foreach ($_calendars as $_cal) {
      if (
        $_cal->asRight(\LibMelanie\Config\ConfigMelanie::WRITE)
        && $_cal->id == $_cal->owner
      ) {
        $calendars[] = new \Program\Data\Calendar([
          'id' => $_cal->id,
          'owner' => $_cal->owner,
          'name' => $_cal->name,
          'show_name' => self::$user->uid == $_cal->owner ? $_cal->name : $this->_set_calendar_show_name($user->fullname, $_cal->name),
        ]);
      }
    }

    // Retourne la liste des calendriers
    return $calendars;
  }

  /**
   * Retourne un nom d'agenda formatté pour l'affichage
   * @param string $username Nom de l'utilisateur courant
   * @param string $calendar_name Nom du calendrier
   * @return string
   */
  private function _set_calendar_show_name($username, $calendar_name)
  {
    $names = explode(' - ', $username, 2);
    return str_replace(' - ', ' (' . \Program\Lib\Request\Localization::g('created by', false) . ' ' . $names[0] . ') - ', $calendar_name);
  }

  /**
   * Permet la génération d'un fichier ICS en fonction du sondage et de la date de la proposition
   *
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status \Program\Data\Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param boolean $request S'agit il d'une request, permet de définir METHOD:REQUEST
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return string Contenu ICS
   */
  public function generate_ics($date, $poll = null, $user = null, $status = null, $part_status = null, $request = false, $calendar = null)
  {
    try {
      // Génération de l'ICS
      return parent::generate_ics($date, $poll, $user, $status, $part_status, $request, $calendar);
    } catch (\Exception $ex) {
      return null;
    }
  }

  /**
   * Permet de tester si la réponse est déjà enregistré dans l'agenda de l'utilisateur
   *
   * @param string $date Date de l'évènement
   * @param string $event_uid [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param boolean $request S'agit il d'une request, permet de définir METHOD:REQUEST
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'évènement existe, False sinon
   */
  public function event_exists($date, $event_uid = null, $poll = null, $user = null, $status = null, $calendar = null)
  {
    try {
      // Récupération de l'utilisateur
      if (!isset($user)) {
        // Si il n'y a pas d'utilisateur connecté
        if (\Program\Data\User::isset_current_user()) {
          $user = \Program\Data\User::get_current_user();
        } else {
          return false;
        }
      }
      // Récupération du sondage
      if (!isset($poll)) {
        if (\Program\Data\Poll::isset_current_poll()) {
          $poll = \Program\Data\Poll::get_current_poll();
        } else {
          return false;
        }
      }

      if (!isset(self::$user) || self::$user->uid != $user->username) {
        // Création de l'utilisateur M2
        self::$user = new Api\Melanie2\User();
        self::$user->uid = $user->username;
      }
      // Récupération du calendrier
      $_calendar = new Api\Melanie2\Calendar(self::$user);
      if (isset($calendar) && is_object($calendar)) {
        $_calendar->id = $calendar->id;
      } else if (isset($calendar)) {
        $_calendar->id = $calendar;
      } else {
        $_calendar->id = self::$user->uid;
      }
      // Si le calendrier existe et que les droits sont bons (lecture)
      if (!$_calendar->load() || !$_calendar->asRight(\LibMelanie\Config\ConfigMelanie::READ)) {
        return [];
      }
      // Création de l'évènement
      $event = new Api\Melanie2\Event(self::$user, $_calendar);
      $event->uid = isset($event_uid) ? $event_uid : $this->generate_event_uid($date, $poll);
      if (!$event->load() || isset($status) && strtolower($status) != $event->status) {
        return false;
      } else {
        return true;
      }
    } catch (\Exception $ex) {
      return false;
    }
  }

  /**
   * Supprime l'événement créé en fonction du sondage
   *
   * @param string $date Date de l'évènement
   * @param string $event_uid [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'événement a bien été supprimé, False sinon
   */
  public function delete_event($date, $event_uid = null, $poll = null, $user = null, $calendar = null)
  {
    try {
      // Récupération de l'utilisateur
      if (!isset($user)) {
        // Si il n'y a pas d'utilisateur connecté
        if (\Program\Data\User::isset_current_user()) {
          $user = \Program\Data\User::get_current_user();
        } else {
          return false;
        }
      }
      // Récupération du sondage
      if (!isset($poll)) {
        if (\Program\Data\Poll::isset_current_poll()) {
          $poll = \Program\Data\Poll::get_current_poll();
        } else {
          return false;
        }
      }

      if (!isset(self::$user) || self::$user->uid != $user->username) {
        // Création de l'utilisateur M2
        self::$user = new Api\Melanie2\User();
        self::$user->uid = $user->username;
      }
      // Récupération du calendrier
      $_calendar = new Api\Melanie2\Calendar(self::$user);
      if (isset($calendar) && is_object($calendar)) {
        $_calendar->id = $calendar->id;
      } else if (isset($calendar)) {
        $_calendar->id = $calendar;
      } else {
        $_calendar->id = self::$user->uid;
      }
      // Si le calendrier existe et que les droits sont bons (lecture)
      if (!$_calendar->load() || !$_calendar->asRight(\LibMelanie\Config\ConfigMelanie::READ)) {
        return [];
      }
      // Création de l'évènement
      $event = new Api\Melanie2\Event(self::$user, $_calendar);
      $event->uid = isset($event_uid) ? $event_uid : $this->generate_event_uid($date, $poll);
      return $event->delete();
    } catch (\Exception $ex) {
      return false;
    }
  }

  /**
   * Initialisation de l'évènement Mélanie2 en fonction des informations passées en paramètres
   *
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param boolean $selected_date Date retenue par l'organisateur ?
   */
  private function init_event($date, $poll, $user = null, $status = null, $part_status = null, $selected_date = false,  $user_title = null)
  {
    // Récupération de la date de début et de la date de fin
    list($start, $end, $allday) = $this->date_to_start_end($date, $poll->timezone);
    // Création de l'évènement
    self::$event = new Api\Melanie2\Event();
    if ($user->timezone != $poll->timezone) {
      $start->setTimezone(new \DateTimeZone($user->timezone));
      $end->setTimezone(new \DateTimeZone($user->timezone));
    }
    self::$event->start = $start->format("Y-m-d H:i:s");
    self::$event->end = $end->format("Y-m-d H:i:s");
    self::$event->all_day = $allday;
    self::$event->timezone = $user->timezone;
    self::$event->uid = $this->generate_event_uid($date, $poll);

    self::$event->class = Api\Melanie2\Event::CLASS_PUBLIC;
    if (isset($status)) {
      self::$event->status = strtolower($status);
    } else if (isset($part_status) && strtolower($part_status) == Api\Melanie2\Attendee::RESPONSE_DECLINED) {
      self::$event->status = Api\Melanie2\Event::STATUS_NONE;
    } else {
      self::$event->status = Api\Melanie2\Event::STATUS_CONFIRMED;
    }

    self::$event->modified = time();
    // Récupération de l'organisateur
    $poll_organizer = Output::get_env("poll_organizer");
    if (!isset($poll_organizer)) {
      $poll_organizer = \Program\Drivers\Driver::get_driver()->getUser($poll->organizer_id);
    }
    // Permet de savoir si l'évenement de l'organisateur existe pour l'ajout des participants
    $organizer_event_exists = true;
    if ($poll->organizer_id != $user->user_id) {
      // Generation de l'événement pour l'agenda par défaut de l'organisateur
      $organizer_user = new Api\Melanie2\User();
      $organizer_user->uid = $poll_organizer->username;
      $organizer_calendar = $organizer_user->getDefaultCalendar();
      $organizer_event = new Api\Melanie2\Event($organizer_user, $organizer_calendar);
      $organizer_event->uid = self::$event->uid;
      // Chargement de l'évenement de l'organisateur
      $organizer_event_exists = $organizer_event->load();
    }
    if (!isset($status) && $organizer_event_exists) {
      $organizer = new Api\Melanie2\Organizer(self::$event);
      $organizer->email = $poll_organizer->email;
      $organizer->name = $poll_organizer->fullname;
      $organizer->uid = $poll_organizer->username;
      $organizer->extern = false;
      self::$event->organizer = $organizer;
    }
    // Modification de l'événement provisoire
    if (isset($status) && strtolower($status) == Api\Melanie2\Event::STATUS_TENTATIVE && !$selected_date) {
      self::$event->title = "[" . \Config\IHM::$TITLE . " " . Localization::g(ucfirst(strtolower($status)), false) . "] " . self::$event->title;
      $description = Localization::g('This event has status tentative, you can view more information about the poll by opening this link', false) . " : " . Output::get_poll_url($poll);
      $description .= "\n\n" . Localization::g('You can delete tentative events of this poll by opening this link', false) . " : " . Output::get_delete_tentatives_poll_url($poll);
      self::$event->attendees = [];
      if (isset($poll->description))
        $description .= "\n\n" . $poll->description;
    } else if (isset($status) && strtolower($status) == Api\Melanie2\Event::STATUS_TENTATIVE && $selected_date) {
      self::$event->title = "[" . Localization::g('Keep date', false) . "] " . self::$event->title;
      $description = Localization::g('This event has status tentative, you can view more information about the poll by opening this link', false) . " : " . Output::get_poll_url($poll);
      if (isset($poll->description))
        $description .= "\n\n" . $poll->description;
    } else {
      // Récupération des réponses du sondage
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
      $attendees = array();
      $attendees_title = array();

      $attendees_list = Localization::g('Poll attendees list', false) . " : \n";
      // Parcours les réponses pour ajouter les participants
      foreach ($responses as $response) {
        // Unserialize les réponses de l'utilisateur
        $resp = unserialize($response->response);
        if (!isset($resp[$date]) && \Program\Data\Poll::get_current_poll()->type == 'rdv') {
          continue;
        } else {
          $attendee = new Api\Melanie2\Attendee();
          if (isset($user) && $response->user_id == $user->user_id) {
            $user_resp = $user;
          } else {
            $user_resp = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
          }
          //Affichage des participants dans le titre du sondage
          if ($poll->type == "rdv" && strtolower($status) != Api\Melanie2\Event::STATUS_TENTATIVE) {
            if ($user_resp->fullname) {
              $username = explode(" ", $user_resp->fullname)[0];
            } else {
              $username = $user_resp->username;
            }
            array_push($attendees_title, $username);
          }
          if (!empty($user_resp->email) && $user_resp->user_id != $poll->organizer_id) {
            // Nom du calendrier ?
            $calendar_name = $response->calendar_name;
            if (isset($calendar_name)) {
              $name = $calendar_name;
            } else {
              $name = isset($user_resp->fullname) && $user_resp->fullname != "" ? $user_resp->fullname : $user_resp->username;
            }
            $attendee->email = $user_resp->email;
            $attendee->role = Api\Melanie2\Attendee::ROLE_REQ_PARTICIPANT;
            $attendee->name = $name;
            // Unserialize les réponses de l'utilisateur
            $attendee->response = Api\Melanie2\Attendee::RESPONSE_NEED_ACTION;
            // Force automatiquement le status du participant
            if ($response->user_id == $user->user_id && isset($part_status)) {
              $attendee->response = strtolower($part_status);
            }
            if (isset($resp[$date]) && $resp[$date]) {
              $attendees_list .= "[" . Localization::g('Yes', false) . "] $name\n";
            } elseif (isset($resp["$date:if_needed"]) && $resp["$date:if_needed"]) {
              $attendees_list .= "[" . Localization::g('If needed', false) . "] $name\n";
            } else {
              $attendees_list .= "[" . Localization::g('No', false) . "] $name\n";
            }
            $attendees[] = $attendee;
          } else {
            // Nom du calendrier ?
            $calendar_name = $response->calendar_name;
            if (isset($calendar_name)) {
              $name = $calendar_name;
            } else {
              $name = isset($user_resp->fullname) && $user_resp->fullname != "" ? $user_resp->fullname : $user_resp->username;
            }
            if (isset($resp[$date]) && $resp[$date]) {
              $attendees_list .= "[" . Localization::g('Yes', false) . "] $name\n";
            } else {
              $attendees_list .= "[" . Localization::g('No', false) . "] $name\n";
            }
          }
        }
        if (!isset($status) && $organizer_event_exists) {
          self::$event->attendees = $attendees;
        }
        $description = "[" . \Config\IHM::$TITLE . "] " . Localization::g('URL to the poll', false) . " : " . Output::get_poll_url($poll);
        if (isset($poll->description)) {
          $description .= "\n\n" . $poll->description;
          $description .= "\n\n" . $attendees_list;
        }
      }
    }

    if (isset($attendees_title) && $poll->organizer_id == $user->user_id) {
      $attendees_title = implode('-', $attendees_title);
      self::$event->title =  self::$event->title . ' ' .  $poll->title . " : " . $attendees_title;
    } else {
      self::$event->title = self::$event->title . ' ' . $poll->title;
    }

    self::$event->description = $description;
    if (isset($poll->location))
      self::$event->location = $poll->location;
  }
}
