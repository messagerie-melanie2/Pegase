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

/**
 * Classe de gestion de la déconnexion de l'utilisateur pour l'application de sondage
 * 
 * @package    Lib
 * @subpackage Request
 */
class Logout {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }
	
	/**
	 * Execution de la requête d'authentification 
	 * @return multitype:string
	 */
	public static function Process() {
	    // Destruction de la session
		\Program\Lib\Request\Session::destroy();
		// Redirection vers la connexion
		header('Location: ' . (isset(\Config\IHM::$LOGIN_URL) ? \Config\IHM::$LOGIN_URL : '?_p=login'));
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Logout::Process()");
		exit();
	}
}