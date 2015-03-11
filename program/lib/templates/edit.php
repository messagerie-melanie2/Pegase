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
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Request as Request;
use Program\Lib\Request\Session as Session;

/**
 * Classe de gestion de l'édition du sondage pour les informations de base
 * 
 * @package    Lib
 * @subpackage Request
 */
class Edit {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }
	
	/**
	 * Execution de la requête
	 */
	public static function Process() {
	    if (!\Program\Data\User::isset_current_user()) {
	        o::set_env("page", "error");
	        o::set_env("error", "You have to be connected");
	        return;
	    }
	    // Test si le sondage courant existe
	    if ((o::get_env("action") == ACT_MODIFY || o::get_env("action") == ACT_LOCK)
	            && !\Program\Data\Poll::isset_current_poll()) {
	        o::set_env("page", "error");
	        o::set_env("error", "Current poll is not defined");
	        return;
	    }
	    if (\Program\Data\Poll::isset_current_poll()) {
	        o::set_env("poll_type", \Program\Data\Poll::get_current_poll()->type);
	    }
	    self::LockPoll();
	}
	/**
	 * Vérouillage dévérouillage du sondage
	 */
	public static function LockPoll() {
	    // Vérouillage/dévérouillage du sondage
	    if (o::get_env("action") == ACT_LOCK
	            && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id
	            && \Program\Data\Poll::get_current_poll()->locked === 0) {
	        $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
	        if (Session::validateCSRFToken($csrf_token)) {
                \Program\Data\Poll::get_current_poll()->locked = 1;
                $message = 'Poll is now locked' ;
    	        if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
    	            o::set_env("message", $message);
    	        }
	        } else {
	            o::set_env("error", "Invalid request");
	        }
	    } elseif (o::get_env("action") == ACT_UNLOCK
	            && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id
	            && \Program\Data\Poll::get_current_poll()->locked === 1) {
	        $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
	        if (Session::validateCSRFToken($csrf_token)) {
    	        \Program\Data\Poll::get_current_poll()->locked = 0;
    	        $message = 'Poll is now unlocked' ;
    	        if (\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())) {
    	            o::set_env("message", $message);
    	        }
	        } else {
	            o::set_env("error", "Invalid request");
	        }
	    }
	}
	/**
	 * Edition d'un sondage
	 * @param string $type
	 */
	public static function EditPoll($type = "date") {
	    // Récupération des données de post
	    $edit_title = Request::getInputValue("edit_title", POLL_INPUT_POST);
	    $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
	    if (isset($edit_title)
	            && $edit_title != "") {
	        if (Session::validateCSRFToken($csrf_token)) {
    	        $edit_location = Request::getInputValue("edit_location", POLL_INPUT_POST);
    	        $edit_description = Request::getInputValue("edit_description", POLL_INPUT_POST);
    	        $edit_only_auth_user = Request::getInputValue("edit_only_auth_user", POLL_INPUT_POST);
    	        if (o::get_env("action") == ACT_NEW) {
    	            $poll = new \Program\Data\Poll(array(
    	                "title" => $edit_title,
    	                "location" => $edit_location,
    	                "description" => $edit_description,
    	                "organizer_id" => \Program\Data\User::get_current_user()->user_id,
    	                "type" => $type,
    	            ));
    	            $poll->auth_only = $edit_only_auth_user == "true";
    	            // Création du sondage
    	            $poll->poll_uid = \Program\Data\Poll::generation_uid();
    	            if (!is_null($poll->poll_uid)) {
    	                $poll_id = \Program\Drivers\Driver::get_driver()->createPoll($poll);
    	                if (isset($poll_id)) {
    	                    \Program\Data\Poll::set_current_poll(\Program\Drivers\Driver::get_driver()->getPoll($poll_id));
    	                    if (isset(\Config\IHM::$SEND_MAIL) && \Config\IHM::$SEND_MAIL) {
    	                        \Program\Lib\Mail\Mail::SendCreatePollMail(\Program\Data\Poll::get_current_poll(), \Program\Data\User::get_current_user());
    	                    }    	                    
    	                } else {
    	                    o::set_env("page", "edit");
    	                    o::set_env("error", "Error creating the poll");
    	                }
    	            } else {
    	                o::set_env("page", "edit");
    	                o::set_env("error", "Error when generating the uid");
    	            }
    	        } elseif (o::get_env("action") == ACT_MODIFY) {
    	            // Modification du sondage courant
    	            $poll = \Program\Data\Poll::get_current_poll();
    	            $poll->title = $edit_title;
    	            $poll->location = $edit_location;
    	            $poll->description = $edit_description;
    	            $poll->type = $type;
    	            $poll->auth_only = $edit_only_auth_user == "true";
    	            if (\Program\Drivers\Driver::get_driver()->modifyPoll($poll)) {
    	                o::set_env("message", 'The poll is modified');
    	            }
    	        }
	        } else {
	            o::set_env("page", "edit");
	            o::set_env("error", "Invalid request");
	        }
	    }
	}
}