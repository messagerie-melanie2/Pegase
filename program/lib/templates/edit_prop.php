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
	    if (\Program\Data\Poll::get_current_poll()->organizer_id != \Program\Data\User::get_current_user()->user_id) {
	        o::set_env("page", "error");
	        o::set_env("error", "You are not organizer of the poll");
	        return;
	    }
	    o::set_env("proposals", unserialize(\Program\Data\Poll::get_current_poll()->proposals), false);
	    if (!is_array(o::get_env("proposals")))
	        o::set_env("proposals", array(), false);
      o::set_env("nb_prop", 5, true);
	    // Défini le nombre en fonction du nombre de propositions
// 	    if (count(o::get_env("proposals")) >= o::get_env("nb_prop")
// 	            && \Program\Data\Poll::get_current_poll()->type == "prop")
// 	        o::set_env("nb_prop", count(o::get_env("proposals")) + 1, false);

	    if (count(o::get_env("proposals")) > 0) {
	      $nb_prop = 0;
        foreach (o::get_env("proposals") as $key => $proposals) {
          $nb = intval(str_replace('edit_prop', '', $key));
          if ($nb > $nb_prop) {
            $nb_prop = $nb;
          }
        }
        o::set_env("nb_prop", $nb_prop + 1, true);
 	    }
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
	      if (count($proposals) > 0
	          && $i < o::get_env("nb_prop")
	          && !isset($proposals['edit_prop'.$i])) {
          continue;
	      }
	      $params = array(
            "id" => "edit_prop$i",
            "type" => "text",
            "name" => "edit_prop$i",
            "class" => "edit_prop",
            "placeholder" => "" . Localization::g('Edit proposition'),
        );
        $input = new \Program\Lib\HTML\html_inputfield($params);
        $html .= \Program\Lib\HTML\html::div(array("class" => "pure-control-group"),
                \Program\Lib\HTML\html::label(array("style" => "width: 35%;", "for" => "edit_prop$i"), Localization::g('Edit proposition')) . " " .
                $input->show(isset($proposals['edit_prop'.$i]) ? $proposals['edit_prop'.$i] : "") . " " .
                \Program\Lib\HTML\html::a(array(
                      "class" => "pure-button pure-button-delete-date",
                      "style" => "padding-top: 3px;",
                      "onclick" => "deletePropDiv('edit_prop$i');",
                  ),
                  ""
                ) .
            \Program\Lib\HTML\html::tag('br')
        );
	    }
	    return $html;
	}
}