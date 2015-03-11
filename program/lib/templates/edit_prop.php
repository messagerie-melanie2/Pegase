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
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion de l'édition du sondage par date
 * 
 * @package    Lib
 * @subpackage Request
 */
class Edit_prop {
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
	    Edit::EditPoll("prop");
	    if (!\Program\Data\Poll::isset_current_poll()) {
	        o::set_env("page", "error");
	        o::set_env("error", "Current poll is not defined");
	        return;
	    }
	    o::set_env("proposals", unserialize(\Program\Data\Poll::get_current_poll()->proposals), false);
	    if (!is_array(o::get_env("proposals"))) 
	        o::set_env("proposals", array(), false);
        o::set_env("nb_prop", 5, false);;
	    // Défini le nombre en fonction du nombre de propositions
	    if (count(o::get_env("proposals")) >= o::get_env("nb_prop")
	            && \Program\Data\Poll::get_current_poll()->type == "prop")
	        o::set_env("nb_prop", count(o::get_env("proposals")) + 1, false);
	    // Ajout des labels
	    o::add_label(array(
    	    'Edit proposition',
    	    'Are you sure ? Not saved proposals are lost',
	    ));
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
	        $input = new \Program\Lib\HTML\html_inputfield(array(
	            "id" => "edit_prop$i",
	            "type" => "text",
	            "name" => "edit_prop$i",
	            "class" => "edit_prop",
	            "placeholder" => " " . Localization::g('Edit proposition'),
	        ));
	        $html .= \Program\Lib\HTML\html::div(array("class" => "pure-control-group"),
	                \Program\Lib\HTML\html::label(array("style" => "width: 35%;", "for" => "edit_prop$i"), Localization::g('Edit proposition')) . " " .
	                $input->show(isset($proposals['edit_prop'.$i]) ? $proposals['edit_prop'.$i] : "")
	        );
	        $html .= "<br>";
	    }
	    return $html;
	}
}