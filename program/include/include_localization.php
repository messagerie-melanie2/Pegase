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
// TODO: Possibilité de rendre cette option configuration par l'utilisateur
if (Program\Lib\Request\Output::isset_env("localization")
		&& file_exists('localization/'.Program\Lib\Request\Output::get_env("localization").'.php')) {
	include_once 'localization/'.Program\Lib\Request\Output::get_env("localization").'.php';
	$localization = Program\Lib\Request\Output::get_env("localization");
} elseif (file_exists('localization/'.Config\IHM::$DEFAULT_LOCALIZATION.'.php')) {
	include_once 'localization/'.Config\IHM::$DEFAULT_LOCALIZATION.'.php';
	$localization = Config\IHM::$DEFAULT_LOCALIZATION;
} else {
	// Pas de fichier trouvé, on crée un tableau vide pour l'overlay
	$labels = array();
	$localization = "";
}
// Chargement de l'overlay
if (isset(Config\IHM::$OVERLAY_LOCALIZATION)
		&& file_exists('localization/'.$localization.Config\IHM::$OVERLAY_LOCALIZATION.'.php')) {
	include_once 'localization/'.$localization.Config\IHM::$OVERLAY_LOCALIZATION.'.php';
}