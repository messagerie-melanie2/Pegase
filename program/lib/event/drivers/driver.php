<?php

/**
 * Classe abstraite pour le driver de gestion des évènements pour l'application
 * 
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 *         This program is free software: you can redistribute it and/or modify
 *         it under the terms of the GNU Affero General Public License as
 *         published by the Free Software Foundation, either version 3 of the
 *         License, or (at your option) any later version.
 *         This program is distributed in the hope that it will be useful,
 *         but WITHOUT ANY WARRANTY; without even the implied warranty of
 *         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *         GNU Affero General Public License for more details.
 *         You should have received a copy of the GNU Affero General Public License
 *         along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Program\Lib\Event\Drivers;

// Appel les namespaces
use Config;

/**
 * Classe abstraite pour le driver
 * les drivers doivent être implémentée à partir de cette classe
 */
abstract class Driver {
  /**
   * Défini si le driver est configurable
   * 
   * @var bool
   */
  const CONFIGURABLE = false;
  
  /**
   * Est-ce que le driver permet l'affichage des freebusy
   * 
   * @var boolean
   */
  public $CAN_GET_FREEBUSY = false;
  
  /**
   * Est-ce que le driver permet d'écrire dans le calendrier
   * 
   * @var boolean
   */
  public $CAN_WRITE_CALENDAR = false;
  
  /**
   * Est-ce que le driver permet de générer un fichier ICS
   * 
   * @var boolean
   */
  public $CAN_GENERATE_ICS = true;
  
  /**
   * Identifiant de l'outil utilisant l'ICS (pour la génération)
   * 
   * @var string
   */
  const PRODID = '-//Pegase/Sabre/PHP/PNE Messagerie/MEDDE';
  /**
   * Version ICalendar utilisé pour la génération de l'ICS
   * 
   * @var string
   */
  const VERSION = '2.0';
  
  /**
   * Constructeur du par défaut driver
   * A modifier dans le driver
   */
  function __construct() {
    if (isset(\Config\IHM::$FREEBUSY_URL) && !empty(\Config\IHM::$FREEBUSY_URL) || \Program\Data\User::isset_current_user() && isset(\Program\Data\User::get_current_user()->freebusy_url)) {
      $this->CAN_GET_FREEBUSY = true;
    } elseif (\Config\IHM::$SHOW_OTHERS_POLLS_FREEBUSY) {
      $this->CAN_GET_FREEBUSY = true;
    }
  }
  
  /**
   * Instance des drivers
   * 
   * @var Driver[]
   */
  private static $drivers = [];
  /**
   * Récupère l'instance du driver à utiliser pour l'utilisateur courant
   * 
   * @param string $name
   *          [Optionnel] Nom du driver à récupérer
   * @return Driver
   */
  public static function get_driver($name = null) {
    // Récupération la liste des drivers
    self::get_drivers();
    if (!isset($name) && count(self::$drivers) == 1) {
      // Le nom n'est pas fourni, il n'y en a qu'un
      foreach (self::$drivers as $driver) {
        return $driver;
      }
    } elseif (!isset($name)) {
      // Le nom n'est pas fourni, on retourne le premier driver non configurable
      foreach (self::$drivers as $driver) {
        if (!$driver::CONFIGURABLE) {
          return $driver;
        }
      }
    } elseif (isset(self::$drivers[$name])) {
      // Le nom est fourni, on retourne le driver en fonction du nom
      return self::$drivers[$name];
    } else {
      // Pas de driver trouvé, retourne null
      return null;
    }
  }
  /**
   * Récupère la liste des drivers pour l'utilisateur courant
   * 
   * @return Driver[]
   */
  public static function get_drivers() {
    // Le tableau d'instance doit être initialisé
    if (!isset(self::$drivers)) {
      self::$drivers = [];
    }
    // Parcour les drivers configuré
    $drivers = \Config\Driver::$Drivers_event;
    if (!is_array($drivers)) {
      $drivers = [
          $drivers
      ];
    }
    // Parcours les drivers pour générer les instances
    foreach ($drivers as $driver) {
      $driver_class = strtolower($driver);
      $driver_class = "\\Program\\Lib\\Event\\Drivers\\$driver_class\\$driver_class";
      self::$drivers[$driver] = new $driver_class();
    }
    // Retourne la liste des drivers
    return self::$drivers;
  }
  
  /**
   * Méthode pour uniformiser la génération de l'uid pour un événement
   * 
   * @param string $date
   *          Date de l'évènement
   * @param \Program\Data\Poll $poll
   *          [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @return string $uid
   */
  public function generate_event_uid($date, $poll = null) {
    // Récupération du poll courant
    if (!isset($poll) && \Program\Data\Poll::isset_current_poll()) {
      $poll = \Program\Data\Poll::get_current_poll();
    }
    if (isset($poll)) {
      // Retourne l'uid formaté
      return md5($poll->created) . "-" . md5($date) . "-" . $poll->poll_uid . "@" . \Config\IHM::$TITLE;
    } else {
      return null;
    }
  }
  
  /**
   * Permet de formater une date en date de début et date de fin
   * 
   * @param string $date
   *          Date de l'évènement
   * @param string $timezone Timezone du sondage         
   * @return array $start,$end,$allday
   */
  public function date_to_start_end($date, $timezone) {
    // Génération de la date
    $tmp = explode(' - ', $date);
    $start = new \DateTime($tmp[0], new \DateTimeZone($timezone));
    $allday = false;
    if (isset($tmp[1])) {
      $end = new \DateTime($tmp[1], new \DateTimeZone($timezone));
      // Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
      if (strlen($tmp[1]) == 10) {
        $end->add(new \DateInterval('P1D'));
        $allday = true;
      }
    } else {
      $end = clone $start;
      // Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
      if (strlen($tmp[0]) == 10) {
        $end->add(new \DateInterval('P1D'));
        $allday = true;
      } else {
        $end->add(new \DateInterval('PT1H'));
      }
    }
    
    return [
        $start,
        $end,
        $allday
    ];
  }
  
  /**
   * **** METHODES ABSTRAITES *****
   */
  /**
   * Génération de la liste des disponibilités pour l'utilisateur
   * Les disponibilités peuvent se situer entre une date de début et une date de fin
   * Retourne une liste d'objet Event
   * 
   * @param \DateTime $start
   *          Début des disponibilités
   * @param \DateTime $end
   *          Fin des disponibilités
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @param \Program\Data\Calendar $calendar
   *          [Optionnel] Calendrier si ce n'est pas le courant
   * @return \Program\Data\Event[] Tableau contenant la liste des évènements
   */
  function get_user_freebusy($start = null, $end = null, $user = null, $calendar = null) {
    // Récupération de l'utilisateur
    if (!isset($user) && \Program\Data\User::isset_current_user()) {
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      } else {
        return null;
      }
    }
    $freebusy_url = $user->freebusy_url;
    // Récupération de l'url de freebusy
    if (isset($freebusy_url)) {
      $fburl = $freebusy_url;
    } elseif (isset(\Config\IHM::$FREEBUSY_URL) && !empty(\Config\IHM::$FREEBUSY_URL)) {
      $fburl = \Config\IHM::$FREEBUSY_URL;
    }
    if (isset($fburl)) {
      // Données utilisateur
      $fburl = str_replace("%%username%%", $user->username, $fburl);
      $fburl = str_replace("%%email%%", $user->email, $fburl);
      // Gestion des dates
      if (strpos($fburl, "%%start%%") !== false && isset($start)) {
        $fburl = str_replace("%%start%%", $start->format('U'), $fburl);
      }
      if (strpos($fburl, "%%end%%") !== false && isset($end)) {
        $fburl = str_replace("%%end%%", $end->format('U'), $fburl);
      }
      // Récupération des données de freebusy
      $fbdata = @file_get_contents($fburl);
    }
    
    $events = [];
    if (isset($fbdata) && $fbdata !== false) {
      $vcalendar = \Sabre\VObject\Reader::read($fbdata);
      // Parcours les vevents
      foreach ($vcalendar->VEVENT as $vevent) {
        // Génération de l'événement
        $event = new \Program\Data\Event([
            'uid' => $vevent->UID,
            'start' => new \DateTime($vevent->DTSTART),
            'end' => new \DateTime($vevent->DTEND),
            'status' => $vevent->STATUS,
            'title' => $vevent->SUMMARY
        ]);
        // Ajoute l'évènement au tableau
        $events[] = $event;
      }
      // Parcours les freebusy
      foreach ($vcalendar->VFREEBUSY as $vfreebusy) {
        foreach ($vfreebusy->FREEBUSY as $prop) {
          // Récupération des dates
          $freebusy_time = explode('/', $prop->getValue(), 2);
          $event_start = new \DateTime($freebusy_time[0]);
          if (isset($freebusy_time[1])) {
            $event_end = new \DateTime($freebusy_time[1]);
          } else {
            $event_end = clone $event_start;
          }
          $freebusy = $prop->parameters;
          // Récupération du status
          if (isset($freebusy['FBTYPE'])) {
            switch ($freebusy['FBTYPE']) {
              default :
              case 'BUSY' :
              case 'BUSY-UNAVAILABLE' :
                $status = \Program\Data\Event::STATUS_CONFIRMED;
                break;
              case 'FREE' :
                $status = \Program\Data\Event::STATUS_NONE;
                break;
              case 'BUSY-TENTATIVE' :
                $status = \Program\Data\Event::STATUS_TENTATIVE;
                break;
            }
          } else {
            $status = \Program\Data\Event::STATUS_CONFIRMED;
          }
          
          // Génération de l'événement
          $event = new \Program\Data\Event([
              'uid' => $this->generate_event_uid($prop->getValue()),
              'start' => $event_start,
              'end' => $event_end,
              'status' => $status,
              'title' => \Program\Lib\Request\Localization::g(ucfirst(strtolower($status)), false)
          ]);
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
      // Parcours les VEvents
      foreach ($vcalendar->VEVENT as $vevent) {
        // Génération de l'événement
        $event = new \Program\Data\Event([
            'uid' => $vevent->UID->getValue(),
            'start' => $vevent->DTSTART->getDateTime(),
            'end' => $vevent->DTEND->getDateTime(),
            'status' => isset($vevent->STATUS) ? $vevent->STATUS->getValue() : \Program\Data\Event::STATUS_CONFIRMED,
            'title' => isset($vevent->SUMMARY) ? $vevent->SUMMARY->getValue() : ""
        ]);
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
    // Génération des disponibilités en fonction des autres sondages
    if (\Config\IHM::$SHOW_OTHERS_POLLS_FREEBUSY && $user->user_id == \Program\Data\User::get_current_user()->user_id) {
      $responses = \Program\Drivers\Driver::get_driver()->getResponsesByRange($user->user_id, $start, $end);
      foreach ($responses as $response) {
        if ($response->poll_id == \Program\Data\Poll::get_current_poll()->poll_id) {
          continue;
        }
        $response_data = unserialize($response->response);
        foreach ($response_data as $prop_value => $resp) {
          if ($resp) {
            if (strpos($prop_value, ":if_needed") !== false) {
              // Supprime le if_needed
              $prop_value = str_replace(":if_needed", "", $prop_value);
            }
            try {
              // Gestion des dates
              // Récupération de la date de début et de la date de fin
              list($start, $end, $allday) = $this->date_to_start_end($prop_value, $user->timezone);
              
              // Génération de l'événement
              $event = new \Program\Data\Event([
                  'uid' => $this->generate_event_uid($prop_value),
                  'start' => $start,
                  'end' => $end,
                  'allday' => $allday,
                  'status' => \Program\Data\Event::STATUS_TENTATIVE,
                  'title' => "[" . \Config\IHM::$TITLE . "] " . $response->poll_title
              ]);
              // Ajoute l'évènement au tableau
              $events[] = $event;
            } catch ( \Exception $ex ) {
            }
          }
        }
      }
    }
    return $events;
  }
  
  /**
   * Retourne le timezone de l'utilisateur
   * 
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \DateTimeZone Timezone de l'utilisateur
   */
  function get_user_timezone($user = null) {
    return new \DateTimeZone(date_default_timezone_get());
  }
  
  /**
   * Enregistre dans l'agenda de l'utilisateur l'évènement lié au sondage
   * 
   * @param string $date
   *          Date de l'évènement
   * @param \Program\Data\Poll $poll
   *          [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status
   *          \Program\Data\Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status
   *          \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param \Program\Data\Calendar $calendar
   *          [Optionnel] Calendrier si ce n'est pas le courant
   * @param boolean $selected_date
   *          Date retenue par l'organisateur ?
   * @return string UID de l'événement créé si OK, null sinon
   * @return \Program\Data\Calendar $organizer_calendar [Optionnel] Calendrier de l'organisateur
   * 
   */
  abstract function add_to_calendar($date, $poll = null, $user = null, $status = null, $part_status = null, $calendar = null, $selected_date = false, $organizer_calendar = null);
  
  /**
   * Liste les calendriers accessibles pour l'utilisateur (en lecture/écriture)
   * 
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \Program\Data\Calendar[] Tableau contenant la liste des calendriers
   */
  abstract function list_user_calendars($user = null);
  
  /**
   * Permet la génération d'un fichier ICS en fonction du sondage et de la date de la proposition
   * 
   * @param string $date
   *          Date de l'évènement
   * @param \Program\Data\Poll $poll
   *          [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status
   *          \Program\Data\Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status
   *          \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param boolean $request
   *          S'agit il d'une request, permet de définir METHOD:REQUEST
   * @param \Program\Data\Calendar $calendar
   *          [Optionnel] Calendrier si ce n'est pas le courant
   * @return string Contenu ICS
   */
  function generate_ics($date, $poll = null, $user = null, $status = null, $part_status = null, $request = false, $calendar = null) {
    // Récupération du sondage
    if (!isset($poll)) {
      if (\Program\Data\Poll::isset_current_poll()) {
        $poll = \Program\Data\Poll::get_current_poll();
      } else {
        return null;
      }
    }
    // Récupération de l'utilisateur
    if (!isset($user) && \Program\Data\User::isset_current_user()) {
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      } else {
        return null;
      }
    }
    
    // Génération du component VCalendar
    $vcalendar = new \Sabre\VObject\Component\VCalendar();
    // PRODID et Version
    $vcalendar->PRODID = self::PRODID;
    $vcalendar->VERSION = self::VERSION;
    // METHOD
    if ($request) {
      $vcalendar->add('METHOD', 'REQUEST');
    }
    // Création de l'objet VEVENT
    $vevent = $vcalendar->add('VEVENT');
    $vevent->UID = $this->generate_event_uid($date, $poll);
    $vevent->SUMMARY = $poll->title;
    $vevent->LOCATION = $poll->location;
    $vevent->DESCRIPTION = $poll->description;
    $vevent->STATUS = isset($status) ? $status : 'CONFIRMED';
    $vevent->DTSTAMP = new \DateTime();
    
    // Gestion des dates
    // Récupération de la date de début et de la date de fin
    list($start, $end, $allday) = $this->date_to_start_end($date, $poll->timezone);    
    if ($allday) {
      // All day event
      $vevent->add("DTSTART", $start->format('Ymd'), [
          "VALUE" => "DATE"
      ]);
      $vevent->add("DTEND", $end->format('Ymd'), [
          "VALUE" => "DATE"
      ]);
    } else {
      if ($user->timezone != $poll->timezone) {
        // Récupération du timezone
        $timezone = $this->get_user_timezone($user);
        $start->setTimezone($timezone);
        $end->setTimezone($timezone);
      }      
      // Si c'est une date/time
      $vevent->DTSTART = $start;
      $vevent->DTEND = $end;
    }
    
    // Création de l'organisateur
    if ($user->user_id == $poll->organizer_id) {
      $organizer = $user;
    } else {
      $organizer = \Program\Drivers\Driver::get_driver()->getUser($poll->organizer_id);
    }
    
    // Add organizer
    $vevent->add('ORGANIZER', 'mailto:' . $organizer->email, [
        'CN' => $organizer->fullname,
        'ROLE' => 'CHAIR',
        'PARTSTAT' => 'ACCEPTED',
        'RSVP' => 'TRUE'
    ]);
    
    //si sondage rdv  n'ajouter que le participant à l'ics
    if ($poll->type == "rdv") {
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);

      // Parcours les réponses pour ajouter les participants
      foreach ($responses as $response) {
        // Ne pas ajouter l'organisateur
        if ($response->user_id == $poll->organizer_id) {
          continue;
        }
        if (key(unserialize($response->response)) == $date){
          // Récupération de l'utilisateur
          if ($response->user_id == $user->user_id) {
            $attendee = $user;
            $partstat = isset($part_status) ? $part_status : 'NEED-ACTION';
          } else {
            $attendee = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
            $partstat = 'NEED-ACTION';
          }
          // Add attendee
          $vevent->add('ATTENDEE', 'mailto:' . $attendee->email, [
              'CN' => isset($attendee->fullname) ? $attendee->fullname : $attendee->username,
              'ROLE' => 'REQ-PARTICIPANT',
              'PARTSTAT' => $partstat,
              'RSVP' => 'TRUE'
          ]);
        }
      }
    }else{
      // Récupération des réponses du sondage
      $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
      
      // Parcours les réponses pour ajouter les participants
      foreach ($responses as $response) {
        // Ne pas ajouter l'organisateur
        if ($response->user_id == $poll->organizer_id) {
          continue;
        }
        // Récupération de l'utilisateur
        if ($response->user_id == $user->user_id) {
          $attendee = $user;
          $partstat = isset($part_status) ? $part_status : 'NEED-ACTION';
        } else {
          $attendee = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
          $partstat = 'NEED-ACTION';
        }
          // Add attendee
        $vevent->add('ATTENDEE', 'mailto:' . $attendee->email, [
            'CN' => isset($attendee->fullname) ? $attendee->fullname : $attendee->username,
            'ROLE' => 'REQ-PARTICIPANT',
            'PARTSTAT' => $partstat,
            'RSVP' => 'TRUE'
        ]);
      }
    }
    
    return $vcalendar->serialize();
  }
  
  /**
   * Permet de tester si la réponse est déjà enregistré dans l'agenda de l'utilisateur
   * 
   * @param string $date
   *          Date de l'évènement
   * @param string $event_uid
   *          [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll
   *          [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status
   *          Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param \Program\Data\Calendar $calendar
   *          [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'évènement existe, False sinon
   */
  abstract function event_exists($date, $event_uid = null, $poll = null, $user = null, $status = null, $calendar = null);
  
  /**
   * Supprime l'événement créé en fonction du sondage
   * 
   * @param string $date
   *          Date de l'évènement
   * @param string $event_uid
   *          [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll
   *          [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user
   *          [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status
   *          Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param \Program\Data\Calendar $calendar
   *          [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'événement a bien été supprimé, False sinon
   */
  abstract function delete_event($date, $event_uid = null, $poll = null, $user = null, $calendar = null);
}