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
namespace Program\Lib\Request;

/**
 * Classe de gestion des cookies pour l'application de sondage
 * 
 * @package    Lib
 * @subpackage Request
 */
class Cookie {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }
	
	/**
	 * Expiration du cookie : calcul pour 2 ans (60*60*24*365*2)
	 */
	private static $expire = 63072000;
	/**
	 * Chemin de stockage des cookies (lié à l'url / pour tout)
	 */
	private static $path = '/';
	/**
	 * Set le cookie avec les valeurs par défaut de configuration
	 * @param string $key
	 * @param string $value
	 * @param bool $expireatsession
	 */
	public static function setCookie($key, $value, $expireatsession = false) {
		setcookie(strtolower($key), $value, $expireatsession ? 0 : self::$expire + time(), self::$path);
	}
	/**
	 * Recupère le cookie avec les valeurs par défaut de configuration
	 * @param string $key
	 */
	public static function getCookie($key) {
		if(isset($_COOKIE[strtolower($key)]))
		return $_COOKIE[strtolower($key)];
		return null;
	}
	/**
	 * Supprimer le cookie avec les valeurs par défaut de configuration
	 * @param string $key
	 */
	public static function deleteCookie($key) {
		setcookie(strtolower($key), '', time() - 3600, self::$path);
	}
	/**
	 * Est-ce le cookie existe
	 * @param string $key
	 */
	public static function issetCookie($key) {
		return isset($_COOKIE[strtolower($key)]);
	}
}