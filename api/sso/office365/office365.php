<?php
/**
 * Classe pour la gestion des SSO Office 365 dans l'application
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
namespace Api\SSO\Office365;

// Utilisation des namespaces
use
	Program\Lib\Request\Session as Session,
    Program\Lib\Request\Request as Request,
    Program\Lib\Request\Output as Output;

/**
 * Classe abstraite pour le sso
 * les sso doivent être implémentée à partir de cette classe
 */
class Office365 extends \Api\SSO\SSO {
	const OFFICE365_SSO_URL = 'api/sso/office365/index.php';
	
	/**
	 * Constructeur par défaut du SSO
	 * A surcharger si besoin
	 */
	public function __construct() {
		self::$SSO_URI = \Config\IHM::$HOST . self::OFFICE365_SSO_URL;
	}
	
	/**
     * Appel le traitement spécifique au SSO
     * @return boolean
     */
	public function process() {
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Office365->process()");
		$requestParams = $_SERVER['REQUEST_URI'];
		if (isset($requestParams)) {
			$requestParams = str_replace('_p=login&', '', $requestParams);
			$requestParams = str_replace('_p=login', '', $requestParams);
			$requestParams = str_replace('_poll=', '_u=', $requestParams);
		}
		$requestParams = explode('?', $requestParams, 2);
		$redirectUri = Request::getCurrentURL() . (isset($requestParams[1]) && !empty($requestParams[1]) ? '?'.$requestParams[1] : '');
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Office365->process() Session redirectUri " . $redirectUri);
		// Stock l'url pour une eventuelle redirection
		Session::set('redirectUri', $redirectUri);
		// Redirection vers la connexion
		header('Location: ' . $this->getLoginUrl());
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Office365->process() SSO URL Login : " . $this->getLoginUrl());
		exit();
	}
	
	/**
	 * Builds a login URL based on the client ID and redirect URI
	 * @param string $redirectUri URI de redirection
	 * @return string
	 */
	public function getLoginUrl($redirectUri = null) {
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Office365->getLoginUrl($redirectUri)");
		if (!isset($redirectUri)) {
			$redirectUri = self::$SSO_URI;
		}
		$loginUrl = Config::$SSO_AUTHORITY.sprintf(Config::$SSO_AUTHORIZE_URL, Config::$SSO_CLIENT_ID, urlencode($redirectUri));
		return $loginUrl;
	}
	
	/**
	 * Builds a logout URL based on the redirect URI
	 * @param string $redirectUri URI de redirection
	 * @return string
	 */
	public function getLogoutUrl($redirectUri = null) {
		\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Office365->getLogoutUrl($redirectUri)");
		if (!isset($redirectUri)) {
			$redirectUri = self::$SSO_URI;
		}
		$logoutUrl = Config::$SSO_AUTHORITY.sprintf(Config::$SSO_LOGOUT_URL, urlencode($redirectUri));
		return $logoutUrl;
	}
}