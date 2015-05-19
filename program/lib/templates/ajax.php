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
use
    Program\Lib\Request\Localization as l,
    Program\Lib\Request\Request as r,
    Program\Lib\Request\Session as s,
    Program\Lib\Request\Output as o,
    Program\Lib\Request\Cookie as c;

/**
 * Classe de gestion des appels ajax
 *
 * @package    Lib
 * @subpackage Request
 */
class Ajax {
    /**
     * Si la requête ajax a réussi ou non
     * @var boolean
     */
    private static $success;
    /**
     * Message à retourner à l'appel ajax
     * @var string
     */
    private static $message;
    /**
     * Texte à retourner à l'appel ajax
     * @var string
     */
    private static $text;

	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Execution de la requête
	 */
	public static function Process() {
    self::$success = true;
    o::set_env("ajax_ok", true);
    if (o::get_env("action") == ACT_GET_USER_EVENTS) {
      self::get_json_events();
    }
		$csrf_token = trim(strtolower(r::getInputValue("token", POLL_INPUT_POST)));
		if (!s::validateCSRFToken($csrf_token)) {
		    self::$success = false;
		    self::$message = "Invalid request";
		    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::Process Invalid request : $csrf_token");
		    self::Send();
		}
		if (o::get_env("action") == ACT_VALIDATE_PROP
		      || o::get_env("action") == ACT_UNVALIDATE_PROP) {
		    self::validate_proposal();
		} elseif (o::get_env("action") == ACT_ADD_CALENDAR) {
		    self::add_calendar();
    } elseif (o::get_env("action") == ACT_ADD_TENTATIVE_CALENDAR) {
      self::add_tentative_calendar();
    } elseif (o::get_env("action") == ACT_GET_VALID_PROPOSALS) {
        self::$text = Show::GetValidateProposalsText(false);
    } else {
		    self::$success = false;
		    self::$message = "Invalid request";
		}
		self::Send();
	}
	/**
	 * Réponse à la requête ajax
	 */
	public static function Send() {
	    header('Content-Type: application/json');
	    echo o::json_serialize(
	            array(
	                    "success" => self::$success,
	                    "message" => l::g(self::$message, false),
	                    "text" => self::$text,
	           )
	    );
	    // set output asap
	    ob_flush();
	    flush();
      exit;
	}

	/**
	 * Validation ou dévalidation de la proposition
	 */
	private static function validate_proposal() {
	  \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::validate_proposal()");
	    if (!\Program\Data\Poll::isset_current_poll()) {
	        self::$success = false;
	        self::$message = "Poll does not exist";
	        self::Send();
	    } elseif (!\Program\Data\User::isset_current_user()
	            && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
	        self::$success = false;
	        self::$message = "You have no right to access to this resource";
	        self::Send();
	    }
	    // Récupération des propositions validées
	    $validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
	    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
	    $prop_key = r::getInputValue("prop_key", POLL_INPUT_POST);
	    if (o::get_env("action") == ACT_VALIDATE_PROP) {
            if (isset($proposals[$prop_key])
                    && !isset($validate_proposals[$proposals[$prop_key]])) {
                $validate_proposals[$proposals[$prop_key]] = true;
                \Program\Data\Poll::get_current_poll()->validate_proposals = $validate_proposals;
                if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
                    self::$message = "Proposal has been validate for this poll";
                    $send_email = r::getInputValue("send_mail", POLL_INPUT_POST);
                    if ($send_email == 'true'
                            && \Program\Lib\Mail\Mail::SendValidateProposalNotificationMail(\Program\Data\Poll::get_current_poll(), $prop_key)) {
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
            if (isset($proposals[$prop_key])
                    && isset($validate_proposals[$proposals[$prop_key]])) {
                unset($validate_proposals[$proposals[$prop_key]]);
                \Program\Data\Poll::get_current_poll()->validate_proposals = $validate_proposals;
                if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
                    self::$message = "Proposal has been unvalidate for this poll";
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
    private static function add_calendar() {
      	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_calendar()");
        if (!\Program\Data\Poll::isset_current_poll()) {
            self::$success = false;
            self::$message = "Poll does not exist";
            self::Send();
        } elseif (!\Program\Data\User::isset_current_user()) {
            self::$success = false;
            self::$message = "You have no right to access to this resource";
            self::Send();
        } elseif (!\Config\IHM::$ADD_TO_CALENDAR) {
	      	self::$success = false;
	        self::$message = "";
	        self::Send();
	      }
        $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
        $prop_key = r::getInputValue("prop_key", POLL_INPUT_POST);
        if (isset($proposals[$prop_key])) {
            if (\Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key]))
                self::$message = "Event has been saved in your calendar";
            else {
                self::$message = "Error while saving the event in your calendar";
                self::$success = false;
            }
        } else {
            self::$message = "Error while saving the event in your calendar";
            self::$success = false;
        }
        // Récupération des propositions validées
        $validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
        // Parcour les propositions du sondage
        foreach($proposals as $proposal_key => $proposal) {
          // La proposition n'est pas validée, il faut peut être la supprimer
          if ($proposal_key != $prop_key
              && !isset($validate_proposals[$proposals[$proposal_key]])
              && \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposals[$proposal_key])) {
            // L'événement existe et la proposition n'est pas validée, il faut donc le supprimer
            \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$proposal_key]);
          }
        }
    }

    /**
     * Création des propositions de l'utilisateur en provisoire
     */
    private static function add_tentative_calendar() {
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar()");
      if (!\Program\Data\Poll::isset_current_poll()) {
        self::$success = false;
        self::$message = "Poll does not exist";
        \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar() Error : Poll does not exist");
        self::Send();
      } elseif (!\Program\Data\User::isset_current_user()) {
        self::$success = false;
        self::$message = "You have no right to access to this resource";
        \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar() Error : You have no right to access to this resource");
        self::Send();
      } elseif (!\Config\IHM::$ADD_TO_CALENDAR) {
      	self::$success = false;
        self::$message = "";
        self::Send();
      }
      $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
      $prop_keys = r::getInputValue("prop_keys", POLL_INPUT_POST);
      \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Ajax::add_tentative_calendar() prop_keys : " . var_export($prop_keys, true));
      // Parcour les propositions du sondage
      foreach($proposals as $prop_key => $proposal) {
        if (in_array($prop_key, $prop_keys)) {
          // La proposition est validée, on l'ajoute en provisoire
          \Program\Lib\Event\Drivers\Driver::get_driver()->add_to_calendar($proposals[$prop_key], null, null, \Program\Data\Event::STATUS_TENTATIVE);
        }
        else {
          // La proposition n'est pas validée, il faut peut être la supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposals[$prop_key])) {
            // L'événement existe, il faut donc le supprimer
            \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposals[$prop_key]);
          }
        }
      }
    }
    /**
     * Récupération des données de l'agenda Mélanie2 pour l'utilisateur courant
     */
    private static function get_json_events() {
      if (!\Program\Data\User::isset_current_user()) {
        self::$success = false;
        self::$message = "You have no right to access to this resource";
        self::Send();
      } elseif (!\Config\IHM::$ADD_TO_CALENDAR) {
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
      $startDate = new \DateTime('@'.$start);
      $endDate = new \DateTime('@'.$end);

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
        $_e['end'] = $event->end->format('Y-m-d H:i:s');
        if (isset($event->status)) {
          switch($event->status) {
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
              $_e['title'] = '['.l::g('Cancelled', false).'] ' . $_e['title'];
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
      exit;
    }
}