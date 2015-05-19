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
    Program\Lib\Request\Request as Request,
    Program\Lib\Request\Output as o,
    Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion de l'édition du sondage par date
 *
 * @package    Lib
 * @subpackage Request
 */
class Edit_date {
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
	    Edit::EditPoll("date");
	    if (!\Program\Data\Poll::isset_current_poll()) {
	        o::set_env("page", "error");
	        o::set_env("error", "Current poll is not defined");
	        return;
	    }
	    // L'application doit elle enregistrer les événements dans l'agenda
	    o::set_env("add_to_calendar", \Config\IHM::$ADD_TO_CALENDAR);
	    // Définition du nombre de prop par défaut
	    $nb_prop = 1;
	    if (o::get_env("mobile"))
	        $nb_prop = 5;
	    o::set_env("proposals", unserialize(\Program\Data\Poll::get_current_poll()->proposals), false);
	    if (!is_array(o::get_env("proposals")))
	        o::set_env("proposals", array(), false);
        o::set_env("nb_prop", $nb_prop, false);
	    // Défini le nombre en fonction du nombre de propositions
	    if (count(o::get_env("proposals")) >= o::get_env("nb_prop")
	            && \Program\Data\Poll::get_current_poll()->type == "date") {
	        if (o::get_env("mobile")) {
	            o::set_env("nb_prop", count(o::get_env("proposals")) + 1, false);
	        } else {
	            $proposals = o::get_env("proposals");
	            $nb_prop = count($proposals);
	            foreach ($proposals as $key => $value) {
	                $val = intval(str_replace("edit_date", "", $key));
	                if ($val > $nb_prop)
	                    $nb_prop = $val;
	            }
	            o::set_env("nb_prop", $nb_prop, false);
	        }
	    }
	    // Ajout des labels
	    o::add_label(array(
    	    'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',
    	    'Mon','Tue','Wed','Thu','Fri','Sat','Sun',
    	    'January','February','March','April','May','June','July','August','September','October','November','December',
    	    'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec',
    	    'All day', 'Today', 'Month', 'Week', 'Day', 'Start', 'End',
    	    'Edit date (Y-m-d H:i:s)', 'Edit date', 'Delete',
    	    'Are you sure ? Not saved proposals are lost',
    	    'Choose date on the calendar',
    	    'show calendar', 'None', 'Tentative', 'Confirmed', 'Your freebusy',
	    	'Loading your events...',
	    ));
	    // Ajout de l'environnement
	    o::set_env('poll_title', \Program\Data\Poll::get_current_poll()->title);
	    // Définition de la premiere date pour l'affichage javascript
	    $proposals = o::get_env("proposals");
	    // Trie les propositions
	    asort($proposals);
	    foreach ($proposals as $prop_key => $prop_value) {
	        o::set_env('first_prop_date', $prop_value);
	        break;
	    }
	}
	/**
	 * Méthode pour générer les propositions
	 * @return string
	 */
	public static function ShowProps() {
	    $html = "";
	    $proposals = o::get_env("proposals");

	    // Génération des propositions
	    for ($i = 1; $i <= o::get_env("nb_prop"); $i++) {
	        if (!o::get_env("mobile")) {
	            if (isset($proposals['edit_date'.$i])) {
	                $attrib = array(
	                    "id" => "edit_date$i",
	                    "name" => "edit_date$i",
	                    "class" => "edit_date",
	                );
	                $attrib["type"] = "text";
	                $attrib["readonly"] = "readonly";
	                $attrib["placeholder"] = " " . Localization::g('Choose date on the calendar');
	                $attrib["style"] = "margin-left: 25%; width: 50%;";
	                $input = new \Program\Lib\HTML\html_inputfield($attrib);
	                $html .= \Program\Lib\HTML\html::div(array("class" => "pure-control-group"),
	                        $input->show($proposals['edit_date'.$i]) . " " .
	                        \Program\Lib\HTML\html::a(array(
	                            "class" => "pure-button pure-button-delete-date",
	                            "style" => "padding-top: 3px;",
	                            "onclick" => "deleteDate('edit_date$i');",
	                        ),
	                                ""
                            )
                        );
	            }
	        } else {
	            $date_start = null; $time_start = null; $date_end = null; $time_end = null;
	            // Formattage des dates pour les champs inputs
	            if (isset($proposals['edit_date'.$i])) {
	                $val = explode(' - ', $proposals['edit_date'.$i]);
	                if (isset($val[0])) {
	                    $tmp = explode(' ', $val[0]);
	                    if (isset($tmp[0])) {
	                        $date_start = $tmp[0];
	                    }
	                    if (isset($tmp[1])) {
	                        $time_start = $tmp[1];
	                    }
	                }
	                if (isset($val[1])) {
	                    $tmp = explode(' ', $val[1]);
	                    if (isset($tmp[0])) {
	                        $date_end = $tmp[0];
	                    }
	                    if (isset($tmp[1])) {
	                        $time_end = $tmp[1];
	                    }
	                }
	            }
	            // Ajout des champs inputs
	            $attrib_date_start = array(
	                "id" => "edit_date_start$i",
	                "name" => "edit_date_start$i",
	                "class" => "edit_date_start",
	                "type" => "date"
	            );
	            $input_date_start = new \Program\Lib\HTML\html_inputfield($attrib_date_start);
	            $attrib_date_end = array(
	                "id" => "edit_date_end$i",
	                "name" => "edit_date_end$i",
	                "class" => "edit_date_end",
	                "type" => "date"
	            );
	            $input_date_end = new \Program\Lib\HTML\html_inputfield($attrib_date_end);
	            $attrib_time_start = array(
	                "id" => "edit_time_start$i",
	                "name" => "edit_time_start$i",
	                "class" => "edit_time_start",
	                "type" => "time"
	            );
	            $input_time_start = new \Program\Lib\HTML\html_inputfield($attrib_time_start);
	            $attrib_time_end = array(
	                "id" => "edit_time_end$i",
	                "name" => "edit_time_end$i",
	                "class" => "edit_time_end",
	                "type" => "time"
	            );
	            $input_time_end = new \Program\Lib\HTML\html_inputfield($attrib_time_end);


	            $html .= \Program\Lib\HTML\html::div(array("class" => "pure-control-group"),
	                    \Program\Lib\HTML\html::label(array("for" => "edit_date_start$i"), Localization::g('Edit date (Y-m-d H:i:s)')) . " " .
	                    Localization::g("Start") . " " .
	                    $input_date_start->show(isset($date_start) ? $date_start : "") .
	                    $input_time_start->show(isset($time_start) ? $time_start : "") . " " .
	                    Localization::g("End") . " " .
                        $input_date_end->show(isset($date_end) ? $date_end : "") .
                        $input_time_end->show(isset($time_end) ? $time_end : "")
	            );
	            $html .= "<br>";
	        }

	    }
	    return $html;
	}
}