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
namespace Program\Lib\Event;

// Configuration du nom de l'application pour l'ORM
if (!defined('CONFIGURATION_APP_LIBM2')) {
    define('CONFIGURATION_APP_LIBM2', 'roundcube');
}
// Inclusion de l'ORM
include_once 'includes/libm2.php';

use LibMelanie\Api\Melanie2 as Melanie2;
use Program\Lib\Request\Output as Output;
use Program\Lib\Request\Request as Request;
use Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion des evenements
 * Permet de générer un fichier ICS ou d'ajouter l'évènement à l'agenda M2
 *
 * @package    Lib
 * @subpackage Event
 */
class Event {
	/**
	 * Evenement Melanie2
	 * @var \LibMelanie\Api\Melanie2\Event
	 */
	private static $event;
	/**
	 * Evenement Melanie2
	 * @var \LibMelanie\Api\Melanie2\User
	 */
	private static $user;
	/**
	 * Evenement Melanie2
	 * @var \LibMelanie\Api\Melanie2\Calendar
	 */
	private static $calendar;

	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Initialisation de l'évènement
	 * Passe la date en paramètre pour instancier l'évènement
	 * Utilise le current poll pour les autres informations
	 * @param string $date
	 */
	public static function Init($date) {
		// Si le sondage courant n'est pas défini
		if (!\Program\Data\Poll::isset_current_poll())
			return;

		// Génération de la date
		$tmp = explode(' - ', $date);
		$start = new \DateTime($tmp[0]);
		if (isset($tmp[1])) {
			$end = new \DateTime($tmp[1]);
			// Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
			if (strlen($tmp[1]) == 10)
			    $end->add(new \DateInterval('P1D'));
		} else {
			$end = clone $start;
			// Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
			if (strlen($tmp[0]) == 10)
			    $end->add(new \DateInterval('P1D'));
			else
			    $end->add(new \DateInterval('PT1H'));
		}
		// Création de l'évènement
		self::$event = new Melanie2\Event();
		self::$event->start = $start->format("Y-m-d H:i:s");
		self::$event->end = $end->format("Y-m-d H:i:s");
		self::$event->uid = md5(\Program\Data\Poll::get_current_poll()->created) . "-" . md5($date) . "-" . \Program\Data\Poll::get_current_poll()->poll_uid . "@" . \Config\IHM::$TITLE;
		self::$event->title = \Program\Data\Poll::get_current_poll()->title;
		self::$event->class = Melanie2\Event::CLASS_PUBLIC;
		self::$event->status = Melanie2\Event::STATUS_CONFIRMED;
		self::$event->modified = time();
		// Récupération de l'organisateur
		$poll_organizer = Output::get_env("poll_organizer");
		if (!isset($poll_organizer)) {
		    $poll_organizer = \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id);
		}
		$organizer = new Melanie2\Organizer(self::$event);
		$organizer->email = $poll_organizer->email;
		$organizer->name = $poll_organizer->fullname;
		self::$event->organizer = $organizer;
		// Récupération des réponses du sondage
		$responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
		$attendees = array();
		$description = "[".\Config\IHM::$TITLE."] " . Localization::g('URL to the poll', false) . " : " . Output::get_poll_url();
		$attendees_list = Localization::g('Poll attendees list', false) . " : \n";
		// Parcour les réponses pour ajouter les participants
		foreach ($responses as $response) {
			$attendee = new Melanie2\Attendee();
			if ($response->user_id == \Program\Data\User::get_current_user()->user_id) {
				$user = \Program\Data\User::get_current_user();
			} else {
				$user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
			}
			if (!empty($user->email)
		          && $user->user_id != \Program\Data\Poll::get_current_poll()->organizer_id) {
				$name = isset($user->fullname) && $user->fullname != "" ? $user->fullname : $user->username;
				$attendee->email = $user->email;
				$attendee->role = Melanie2\Attendee::ROLE_REQ_PARTICIPANT;
				$attendee->name = $name;
				// Unserialize les réponses de l'utilisateur
				$resp = unserialize($response->response);
				if (isset($resp[$date])
						&& $resp[$date]) {
					$attendee->response = Melanie2\Attendee::RESPONSE_ACCEPTED;
					$attendees_list .= "[".Localization::g('Yes', false)."] $name\n";
				} else {
					$attendee->response = Melanie2\Attendee::RESPONSE_DECLINED;
					$attendees_list .= "[".Localization::g('No', false)."] $name\n";
				}
				$attendees[] = $attendee;
			} else {
				$name = isset($user->fullname) && $user->fullname != "" ? $user->fullname : $user->username;
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
		self::$event->attendees = $attendees;
		if (isset(\Program\Data\Poll::get_current_poll()->description))
			$description .= "\n\n" . \Program\Data\Poll::get_current_poll()->description . "\n\n";
		$description .= $attendees_list;
		self::$event->description = $description;
		if (isset(\Program\Data\Poll::get_current_poll()->location))
			self::$event->location = \Program\Data\Poll::get_current_poll()->location;
	}
	/**
	 * Génération de l'ICS à partir de l'évènement généré
	 */
	public static function ToICS() {
	    try {
	        return self::$event->getICS();
	    } catch(\Exception $ex) {
	        return "";
	    }
	}
	/**
	 * Création dans l'agenda M2 de l'utilisateur de l'évènement généré
	 */
	public static function AddToM2() {
	    try {
	        // Si il n'y a pas d'utilisateur connecté
	        if (!\Program\Data\User::isset_current_user())
	            return false;

	        if (!isset(self::$user)) {
	            // Création de l'utilisateur M2
	            self::$user = new Melanie2\User();
	            self::$user->uid = \Program\Data\User::get_current_user()->username;
	        }
	        if (!isset(self::$calendar)) {
	            // Récupération de l'agenda par défaut de l'utilisateur
	            self::$calendar = self::$user->getDefaultCalendar();
	            // Si l'agenda par défaut n'existe pas
	            if (!isset(self::$calendar))
	                return false;
	        }
	        // Définition des élèments manquants de l'évènement
	        self::$event->setUserMelanie(self::$user);
	        self::$event->setCalendarMelanie(self::$calendar->getObjectMelanie());

	        $ret = self::$event->save();
	        return !is_null($ret);
	    } catch(\Exception $ex) {
	        return false;
	    }
	}
	/**
	 * Défini si l'évènement existe déjà dans l'agenda Melanie2 par défaut de l'utilisateur courant
	 * @param string $date
	 */
	public static function IsEventExists($date) {
	    try {
	        // Si il n'y a pas d'utilisateur connecté
	        if (!\Program\Data\User::isset_current_user())
	            return false;

	        if (!isset(self::$user)) {
	            // Création de l'utilisateur M2
	            self::$user = new Melanie2\User();
	            self::$user->uid = \Program\Data\User::get_current_user()->username;
	        }
	        if (!isset(self::$calendar)) {
	            // Récupération de l'agenda par défaut de l'utilisateur
	            self::$calendar = self::$user->getDefaultCalendar();
	            // Si l'agenda par défaut n'existe pas
	            if (!isset(self::$calendar))
	                return false;
	        }
	        // Création de l'évènement
	        $event = new Melanie2\Event(self::$user, self::$calendar);
	        $event->uid = md5(\Program\Data\Poll::get_current_poll()->created) . "-" . md5($date) . "-" . \Program\Data\Poll::get_current_poll()->poll_uid . "@" . \Config\IHM::$TITLE;
	        return $event->exists();
	    } catch(\Exception $ex) {
	        return false;
	    }
	}
}