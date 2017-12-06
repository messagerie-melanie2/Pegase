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
namespace Program\Lib\Event\Drivers\Office365;

use
    Program\Lib\Request\Output as Output,
    Program\Lib\Request\Request as Request,
    Program\Lib\Request\Session as Session,
    Program\Lib\Request\Localization as Localization;

/**
 * Classe pour le driver de gestion des évènements
 * les drivers doivent être implémentée à partir de cette classe
 */
class Office365 extends \Program\Lib\Event\Drivers\Driver {
  /**
   * Timezone de l'utilisateur
   * @var string
   */
  private static $timezone;

  /**
   * Constructeur du driver
   */
  function __construct() {

  }

  /**
   * Génération de la liste des disponibilités pour l'utilisateur
   * Les disponibilités peuvent se situer entre une date de début et une date de fin
   * Retourne une liste d'objet Event
   * @param \DateTime $start Début des disponibilités
   * @param \DateTime $end Fin des disponibilités
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \Program\Data\Event[] Tableau contenant la liste des évènements
   */
  public function get_user_freebusy($start = null, $end = null, $user = null) {
  	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->get_user_freebusy()");
  	if (!isset($user)) {
  		$user = \Program\Data\User::get_current_user();
  	}
  	if (!Session::is_set('accessToken')) {
  		return [];
  	}
	$msevents = \Api\SSO\Office365\Office365Service::getEventsForRangeDates(Session::get('accessToken'), $start, $end);
	if (isset($msevents['error'])) {
  		return [];
  	}
  	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->get_user_freebusy() msevents : " . var_export($msevents, true));
	$events = [];
	foreach ($msevents['value'] as $msevent) {
		$event = new \Program\Data\Event();
		$event->uid = $msevent['Id'];
		$event->title = $msevent['Subject'];
		try {
			self::$timezone = new \DateTimeZone(\Api\SSO\Office365\Timezone::GetFromMS($msevent['StartTimeZone']));
		}
		catch (Exception $ex) {
			\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::ERROR, "Driver\Office365->get_user_freebusy() Exception : " . $ex->getTraceAsString());
		}
		$event->start = new \DateTime($msevent['Start'], new \DateTimeZone('UTC'));
		$event->end = new \DateTime($msevent['End'], new \DateTimeZone('UTC'));
		switch ($msevent['ShowAs']) {
			case 'Busy':
			default:
				$event->status = \Program\Data\Event::STATUS_CONFIRMED;
				break;
			case 'Tentative':
				$event->status = \Program\Data\Event::STATUS_TENTATIVE;
				break;
			case 'Free':
			case 'Oof':
				$event->status = \Program\Data\Event::STATUS_NONE;
				break;
		}
		$event->description = $msevent['BodyPreview'];
		$event->location = $msevent['Location']['DisplayName'];
		$event->allday = $msevent['IsAllDay'];
		if (!$event->allday) {
			$event->start->setTimezone(self::$timezone);
			$event->end->setTimezone(self::$timezone);
		}
		$events[] = $event;
	}
	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->get_user_freebusy() events : " . var_export($msevents, true));
	return $events;
  }

  /**
   * Retourne le timezone de l'utilisateur
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return DateTimeZone Timezone de l'utilisateur
   */
  public function get_user_timezone($user = null) {
    if (isset(self::$timezone)) {
      return self::$timezone;
    }
    self::$timezone = new \DateTimeZone(date_default_timezone_get());
    return self::$timezone;
  }

  /**
   * Enregistre dans l'agenda de l'utilisateur l'évènement lié au sondage
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @param boolean $selected_date Date retenue par l'organisateur ?
   * @return boolean True si la création s'est bien passée, False sinon
  */
  public function add_to_calendar($date, $poll = null, $user = null, $status = null, $part_status = null, $calendar = null, $selected_date = false) {
  	if (!Session::is_set('accessToken')) {
  		return false;
  	}
  	if (!isset($user)) {
  		$user = \Program\Data\User::get_current_user();
  	}
  	if (!isset($poll)) {
  		$poll = \Program\Data\Poll::get_current_poll();
  	}
  	$reminder = 15;
  	if (isset($status)) {
  		switch($status) {
  			case \Program\Data\Event::STATUS_CONFIRMED:
  			default:
  				$mstatus = 'Busy';
  				break;
  			case \Program\Data\Event::STATUS_NONE:
  			case \Program\Data\Event::STATUS_CANCELLED:
  				$mstatus = 'Free';
  				$reminder = null;
  				break;
  			case \Program\Data\Event::STATUS_TENTATIVE:
  				$mstatus = 'Tentative';
  				$reminder = null;
  				break;
  		}
  	}
  	else {
  		$mstatus = 'Busy';
  	}
  	// Récupération de la date de début et de la date de fin
  	list($start, $end, $allday) = $this->date_to_start_end($date);
  	$start->setTimezone(new \DateTimeZone('UTC'));
  	$end->setTimezone(new \DateTimeZone('UTC'));
  	// Creation de l'objet
  	$msevent = [
  		'Subject' => $poll->title,
  		'ShowAs' => $mstatus,
  		'Start' => $start->format('Y-m-d\TH:i:s\Z'),
  		'End' => $end->format('Y-m-d\TH:i:s\Z'),
  		'IsAllDay' => $allday,
  		'Importance' => 'Normal',
  		'Reminder' => $reminder,
  		"Location" => array("DisplayName" => $poll->location),
  		'StartTimeZone' => \Api\SSO\Office365\Timezone::GetFromPHP($this->get_user_timezone()->getName()),
  		'EndTimeZone' => \Api\SSO\Office365\Timezone::GetFromPHP($this->get_user_timezone()->getName()),
  	];
  	$poll_organizer = Output::get_env("poll_organizer");
  	if (!isset($poll_organizer)) {
  		$poll_organizer = \Program\Drivers\Driver::get_driver()->getUser($poll->organizer_id);
  	}
  	if (!isset($status)
  			&& $poll->organizer_id == $user->user_id) {
  		$msevent['IsOrganizer'] = true;
  		$msevent['Organizer'] = [
  				'EmailAddress' => [
  						'Address' => $poll_organizer->email,
  						'Name' => $poll_organizer->fullname,
  				],
  		];
  	}

  	// Récupération des réponses du sondage
  	$responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
  	$attendees = [];
  	$description = "[".\Config\IHM::$TITLE."] " . Localization::g('URL to the poll', false) . " : " . Output::get_poll_url($poll);
  	$attendees_list = Localization::g('Poll attendees list', false) . " : \n";
  	// Parcourt les réponses pour ajouter les participants
  	foreach ($responses as $response) {
  		$attendee = [];
  		if (isset($user)
  				&& $response->user_id == $user->user_id) {
  					$user_resp = $user;
  		} else {
  			$user_resp = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
  		}
  		if (!empty($user_resp->email)
  				&& $user_resp->user_id != $poll->organizer_id) {
  			$name = isset($user_resp->fullname) && $user_resp->fullname != "" ? $user_resp->fullname : $user_resp->username;
  			$attendee = [
  				'EmailAddress' => [
  					'Address' => $user_resp->email,
  					'Name' => $name,
  				],
  				'Type' => "Required",
  			];
  			// Unserialize les réponses de l'utilisateur
  			$resp = unserialize($response->response);
  			if (isset($resp[$date])
  					&& $resp[$date]) {
  				if (isset($user) && $response->user_id == $user->user_id)
  					$attendee['Status']['Response'] = "Accepted";
  				$attendees_list .= "[".Localization::g('Yes', false)."] $name\n";
  			} elseif (isset($resp["$date:if_needed"])
  					&& $resp["$date:if_needed"]) {
  				if (isset($user) && $response->user_id == $user->user_id)
  					$attendee['Status']['Response'] = "TentativelyAccepted";
  				$attendees_list .= "[".Localization::g('If needed', false)."] $name\n";
  			} else {
  				if (isset($user) && $response->user_id == $user->user_id)
  					$attendee['Status']['Response'] = "Declined";
  				$attendees_list .= "[".Localization::g('No', false)."] $name\n";
  			}
  			$attendees[] = $attendee;
  		} else {
  			$name = isset($user_resp->fullname) && $user_resp->fullname != "" ? $user_resp->fullname : $user_resp->username;
  			// Unserialize les réponses de l'utilisateur
  			$resp = unserialize($response->response);
  			if (isset($resp[$date])
  					&& $resp[$date]) {
  				$attendees_list .= "[".Localization::g('Yes', false)."] $name\n";
  			} else {
  				$attendees_list .= "[".Localization::g('No', false)."] $name\n";
  			}
  		}
  	}
  	if (!isset($status)
  			&& $poll->organizer_id == $user->user_id) {
  		$msevent['Attendees'] = $attendees;
  	}
  	if (isset($poll->description))
  		$description .= "\n\n" . $poll->description . "\n\n";
  	$description .= $attendees_list;
  	// Create a static body.
  	$htmlBody = "<html><body>".str_replace("\n", "<br>", $description)."</body></html>";
  	$msevent["Body"] = array("ContentType" => "HTML", "Content" => $htmlBody);
  	$event = $this->get_event($date, $poll, $user);
  	if (is_null($event)) {
  		return \Api\SSO\Office365\Office365Service::addEventToCalendar(Session::get('accessToken'), $msevent);
  	}
  	else {
  		return \Api\SSO\Office365\Office365Service::updateEvent(Session::get('accessToken'), $msevent, $event['Id']);
  	}
  }

  /**
   * Permet la génération d'un fichier ICS en fonction du sondage et de la date de la proposition
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return string Contenu ICS
  */
  public function generate_ics($date, $poll = null, $user = null) {
    return true;
  }

  /**
   * Permet de tester si la réponse est déjà enregistré dans l'agenda de l'utilisateur
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return boolean True si l'évènement existe, False sinon
  */
  public function event_exists($date, $poll = null, $user = null, $status = null) {
  	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->event_exists()");
  	$event = $this->get_event($date, $poll, $user, $status);
  	return !is_null($event);
  }

  /**
   * Supprime l'événement créé en fonction du sondage
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return boolean True si l'événement a bien été supprimé, False sinon
   */
  public function delete_event($date, $poll = null, $user = null) {
  	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->delete_event()");
  	if (!Session::is_set('accessToken')) {
  		return false;
  	}
  	$event = $this->get_event($date, $poll, $user);
  	if (is_null($event)) {
  		return false;
  	}
  	$response = \Api\SSO\Office365\Office365Service::deleteEvent(Session::get('accessToken'), $event['Id']);
    return !isset($response['error']);
  }

  /**
   * Récupération d'un evenement en fonction de la date et du sondage
   * Permet l'appel de event_exists ou delete_event
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return array $event
   */
  private function get_event($date, $poll = null, $user = null, $status = null) {
  	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Driver\Office365->get_event()");
  	if (!Session::is_set('accessToken')) {
  		return null;
  	}
  	if (!isset($user)) {
  		$user = \Program\Data\User::get_current_user();
  	}
  	if (!isset($poll)) {
  		$poll = \Program\Data\Poll::get_current_poll();
  	}
  	// Récupération de la date de début et de la date de fin
  	list($start, $end, $allday) = $this->date_to_start_end($date);
  	$start_utc = clone $start;
  	$start_utc->setTimezone(new \DateTimeZone('UTC'));
  	$msevents = \Api\SSO\Office365\Office365Service::getEventsForRangeDates(Session::get('accessToken'), $start_utc, $end);
  	if (isset($msevents['error'])) {
  		return [];
  	}
  	if ($allday) {
  		$end->add(new \DateInterval('P1D'));
  	}
  	else {
  		$end->setTimezone(new \DateTimeZone('UTC'));
  	}
  	foreach ($msevents['value'] as $msevent) {
  		if (!isset($msevent['Subject'])) {
  			continue;
  		}
  		$event_start = new \DateTime($msevent['Start'], new \DateTimeZone('UTC'));
  		$event_end = new \DateTime($msevent['End'], new \DateTimeZone('UTC'));
  		if ($msevent['Subject'] == $poll->title
  				&& $msevent['IsAllDay'] == $allday
  				&& ($allday
  						&& $event_start->format('Y-m-d') == $start->format('Y-m-d')
  						&& $event_end->format('Y-m-d') == $end->format('Y-m-d')
  						|| !$allday
  						&& $event_start->format('Y-m-d H:i:s') == $start_utc->format('Y-m-d H:i:s')
  						&& $event_end->format('Y-m-d H:i:s') == $end->format('Y-m-d H:i:s'))) {
  							if (isset($status)) {
  								switch ($msevent['ShowAs']) {
  									case 'Busy':
  									default:
  										$event_status = \Program\Data\Event::STATUS_CONFIRMED;
  										break;
  									case 'Tentative':
  										$event_status = \Program\Data\Event::STATUS_TENTATIVE;
  										break;
  									case 'Free':
  									case 'Oof':
  										$event_status = \Program\Data\Event::STATUS_NONE;
  										break;
  								}
  								if ($event_status == $status) {
  									return $msevent;
  								}
  							}
  							else {
  								return $msevent;
  							}
  						}
  	}
  	return null;
  }
}