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
use Program\Lib\Request\Request as Request;
use Program\Lib\Request\Session as Session;
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion de l'édition du sondage
 * 
 * @package    Lib
 * @subpackage Request
 */
class Edit_end {
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
	    if (!\Program\Data\Poll::isset_current_poll()) {
	        o::set_env("page", "error");
	        o::set_env("error", "Current poll is not defined");
	        return;
	    }
	    $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
	    if (Session::validateCSRFToken($csrf_token)) {
            // récupération des données de post
            if (!o::get_env("mobile") 
                    || \Program\Data\Poll::get_current_poll()->type == "prop") {
                $post = $_POST;
            } else {
                $post = array();
                foreach($_POST as $key => $value) {
                    if (strpos($key, "edit_date_start") === 0
                            && $value != "") {
                        $id = str_replace("edit_date_start", "", $key);
                        $post["edit_date$id"] = Request::getInputValue($key, POLL_INPUT_POST) 
                                . (isset($_POST["edit_time_start$id"]) && $_POST["edit_time_start$id"] != "" ? " " . Request::getInputValue("edit_time_start$id", POLL_INPUT_POST) : "") 
                                . (isset($_POST["edit_date_end$id"]) && $_POST["edit_date_end$id"] != "" ? " - " . Request::getInputValue("edit_date_end$id", POLL_INPUT_POST) : "") 
                                . (isset($_POST["edit_time_end$id"]) && $_POST["edit_time_end$id"] != "" && isset($_POST["edit_date_end$id"]) && $_POST["edit_date_end$id"] != "" ? " " . Request::getInputValue("edit_time_end$id", POLL_INPUT_POST) : "");
                    }
                }
            }
    	    // Génération des propositions du sondage
    	    $proposals = array();
    	    foreach($post as $key => $value) {
    	        if (strpos($key, "edit_date") === 0 
    	                || strpos($key, "edit_prop") === 0 ) {
    	            $val = Request::getInputValue($key, POLL_INPUT_POST);
    	            if (empty($val)
    	                    && o::get_env("mobile"))
    	                $val = $value;
    	            if (!empty($val)
    	                    && !in_array($val, $proposals))
    	                $proposals[strtolower($key)] = $val;
    	        }
    	    }
    	    // Enregistrement des propositions
            \Program\Data\Poll::get_current_poll()->proposals = serialize($proposals);
            if (!\Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll())
                    && o::get_env("action") == ACT_NEW) {
                o::set_env("page", "edit_" . \Program\Data\Poll::get_current_poll()->type);
                o::set_env("error", "Error saving proposals");
            }
	    } else {
            o::set_env("page", "edit_" . \Program\Data\Poll::get_current_poll()->type);
            o::set_env("error", "Invalid request");
        }
    }
	/**
	 * Génére l'url public vers le sondage courant
	 * @param boolean $newtab Ouvrir l'url dans un nouvel onglet ?
	 * @return string
	 */
	public static function GetPublicUrl($newtab = false) {
	    $url = o::get_poll_url();
	    $params = array(
	            "title" => Localization::g("Copy this url to share your poll", false), 
	            "class" => "public_url_link customtooltip_bottom", 
	            "href" => $url);
	    if ($newtab) $params['target'] = "_blank";
	    return \Program\Lib\HTML\html::a($params, $url);
	}
}