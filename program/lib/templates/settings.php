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
use Program\Lib\Request\Output as o, 
  Program\Lib\Request\Request as Request, 
  Program\Lib\Request\Session as Session;

/**
 * Classe de gestion des paramètres de l'utilisateur
 *
 * @package Lib
 * @subpackage Request
 */
class Settings {
	/**
	 * Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() {
	}

	/**
	 * Execution de la requête
	 */
	public static function Process() {
		if (! \Program\Data\User::isset_current_user ()) {
			o::set_env ( "page", "error" );
			o::set_env ( "error", "You have to be connected" );
			return;
		}
		if (o::get_env('action') == ACT_SAVE_SETTINGS) {
		  $csrf_token = trim(strtolower(Request::getInputValue("_token", POLL_INPUT_POST)));
		  if (Session::validateCSRFToken($csrf_token)) {
		    $user = \Program\Data\User::get_current_user();
		    $user->timezone = Request::getInputValue("_timezone", POLL_INPUT_POST);
		    $user->freebusy_url = Request::getInputValue("_freebusy_url", POLL_INPUT_POST);
		    if (\Program\Drivers\Driver::get_driver()->modifyUser($user)) {
		      \Program\Data\User::set_current_user($user);
		      o::set_env ( "message", 'Settings are updated' );
		    }
		    else {
		      o::set_env ( "error", 'Error while updating the settings' );
		    }
		  } else {
		    o::set_env("error", "Invalid request");
		  }
		}
	}
	/**
	 * Liste les timezones disponibles dans PHP dans un select html
	 * @return string select html
	 */
	public static function GetTimezonesSelect() {
	  $field_id = 'settings_timezone';
	  $select   = new \Program\Lib\HTML\html_select(array('name' => '_timezone', 'id' => $field_id, 'style' => 'width: 400px;'));
	  
	  $zones = array();
	  foreach (\DateTimeZone::listIdentifiers() as $i => $tzs) {
	    try {
	      $tz      = new \DateTimeZone($tzs);
	      $date    = new \DateTime(date('Y') . '-12-21', $tz);
	      $offset  = $date->format('Z') + 45000;
	      $sortkey = sprintf('%06d.%s', $offset, $tzs);
	      $zones[$sortkey] = array($tzs, $date->format('P'));
	    }
	    catch (\Exception $e) {}
	  }
	  
	  ksort($zones);
	  
	  foreach ($zones as $zone) {
	    list($tzs, $offset) = $zone;
	    $select->add('(GMT ' . $offset . ') ' . strtr($tzs, '_', ' '), $tzs);
	  }
	  
	  return $select->show(\Program\Data\User::get_current_user()->timezone);
	}
}