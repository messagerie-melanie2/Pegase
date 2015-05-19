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
 * Classe de gestion des sessions pour l'application de sondage
 * 
 * @package    Lib
 * @subpackage Request
 */
class Session {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }
	
	/**
	 * @desc Valide le token de session et le renouvelle
	 * @access public
	 */
	public static function validateSession() {
		// Gestion de la session
		if (!self::is_setUsername()) return false;
		
		if (self::genToken(Cookie::getCookie(\Config\IHM::$COOKIE_TOKEN)) == self::get(\Config\IHM::$SESSION_TOKEN)
		        && \Program\Drivers\Driver::get_driver()->authenticate(self::getUsername(), self::getPassword())) {
			// On renouvelle le token
			$uniqID = self::genUniqID();
			self::set(\Config\IHM::$SESSION_TOKEN, self::genToken($uniqID));
			Cookie::setCookie(\Config\IHM::$COOKIE_TOKEN, $uniqID, true);
			session_regenerate_id(true);
			// Test si l'utilisateur est connecté pour nettoyer la session
			if (\Program\Data\User::isset_current_user()) {
			    Session::set("user_noauth_id", null);
			    Session::set("user_noauth_name", null);
			    Session::set("user_noauth_poll_id", null);
			}
			\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Session::validateSession() Valid session : " . self::get(\Config\IHM::$SESSION_TOKEN));
			return true;
		} else {		    
			\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::DEBUG, "Session::validateSession() Invalid session, relogin : Cookie : " . self::genToken(Cookie::getCookie(\Config\IHM::$COOKIE_TOKEN)) . " / Session : " . self::get(\Config\IHM::$SESSION_TOKEN));			
			\Program\Lib\Templates\Logout::Process();
			Cookie::deleteCookie(\Config\IHM::$COOKIE_TOKEN);
			return false;
		}
	}
	/**
	 * Défini le session token une premiere fois (ensuite il faut le valider à chaque page)
	 */
	public static function setToken() {
		// On renouvelle le token
		$uniqID = self::genUniqID();
		self::set(\Config\IHM::$SESSION_TOKEN, self::genToken($uniqID));
		Cookie::setCookie(\Config\IHM::$COOKIE_TOKEN, $uniqID, true);
		session_regenerate_id(true);
	}
	
	/**
	 * Récupère la valeur de session
	 * @param string $name
	 * @return string
	 */
	public static function get($name) {
	    if (isset($_SESSION[$name]))
	        return $_SESSION[$name];
	    else
	        return null;
	}
	/**
	 * Positionne la valeur de session
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value) {
	    $_SESSION[$name] = $value;
	}
	/**
	 * Supprime la valeur de session
	 * @param string $name
	 */
	public static function un_set($name) {
	    unset($_SESSION[$name]);
	}
	/**
	 * Est-ce que la valeur existe en session
	 * @param string $name
	 */
	public static function is_set($name) {
	    return isset($_SESSION[$name]);
	}
	/**
	 * Récupère le mot de passe depuis la session
	 * @return string|NULL
	 */
	public static function getPassword() {
	    if (self::is_setPassword())
	        return \Program\Lib\Request\Crypt::decrypt(self::get(\Config\IHM::$SESSION_PASSWORD));
	    else
	        return null;
	}
	/**
	 * Positionne la valeur du mot de passe
	 * @param string $value
	 */
	public static function setPassword($value) {
	    self::set(\Config\IHM::$SESSION_PASSWORD, \Program\Lib\Request\Crypt::encrypt($value));
	}
	/**
	 * Est-ce que le mot de passe est en session
	 */
	public static function is_setPassword() {
	    return self::is_set(\Config\IHM::$SESSION_PASSWORD);
	}
	/**
	 * Récupère le login utilisateur depuis la session
	 * @return string|NULL
	 */
	public static function getUsername() {
	    if (self::is_setPassword())
	        return self::get(\Config\IHM::$SESSION_USERNAME);
	    else
	        return null;
	}
	/**
	 * Positionne la valeur du login utilisateur
	 * @param string $value
	 */
	public static function setUsername($value) {
	    self::set(\Config\IHM::$SESSION_USERNAME, $value);
	}
	/**
	 * Est-ce que le login utilisateur est en session
	 */
	public static function is_setUsername() {
	    return self::is_set(\Config\IHM::$SESSION_USERNAME);
	}
	/**
	 * Destruction de la session
	 */
	public static function destroy() {
	    // Gestion de la deconnexion
	    session_destroy();
	    // Détruit toutes les variables de session
	    unset($_SESSION);
	    $_SESSION = array();
	}
	
	/**
	 * Génération du token CSRF
	 */
	public static function setCSRFToken() {
	    // Génération du token CSRF pour protéger les formulaires
	    self::set(\Config\IHM::$CSRF_TOKEN, self::genToken(self::genUniqID()));
	}
	
	/**
	 * Retourne le token CSRF à ajouter dans chaque formulaire
	 * @return string
	 */
	public static function getCSRFToken() {
	    // Génération du token CSRF pour protéger les formulaires
	    if (!self::is_set(\Config\IHM::$CSRF_TOKEN)) {
	       self::setCSRFToken();
	    }
	    return self::get(\Config\IHM::$CSRF_TOKEN);
	}
	
	/**
	 * Validation du token csrf en fonction de la valeur en session
	 * @param string $csrf_token
	 * @return boolean
	 */
	public static function validateCSRFToken($csrf_token) {
	    return $csrf_token == self::get(\Config\IHM::$CSRF_TOKEN);
	}
	
	/*********** PRIVATE FUNCTIONS ********/
	/**
	 * Génération du token de session
	 * @param string $uniqID
	 * @return string
	 * @access private
	 */
	private static function genToken($uniqID) {
		return md5(base64_encode($uniqID.':'.self::getUserAgent()));
	}
	
	/**
	 * Génération d'un ID unique
	 * @return string
	 * @access private
	 */
	private static function genUniqID() {
		return uniqid(strval(mt_rand(1, 999999)));
	}
	/**
	 * @desc Récupére l'adresse IP du client
	 * @return string
	 * @access private
	 */
	private static function getIP() {
		if(getenv('HTTP_X_FORWARDED_FOR')) {
			return getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('HTTP_CLIENT_IP')) {
			return getenv('HTTP_CLIENT_IP');
		} else {
			return getenv('REMOTE_ADDR');
		}
	}
	/**
	 * @desc Retourne le user agent du client
	 * @return string
	 * @access private
	 */
	private static function getUserAgent() {
		return $_SERVER['HTTP_USER_AGENT'];
	}
}