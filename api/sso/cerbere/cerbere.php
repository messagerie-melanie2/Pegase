<?php
/**
 * Classe pour la gestion du SSO Cerbere dans l'application
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
namespace Api\SSO\Cerbere;

// Utilisation des namespaces
use
  Program\Lib\Request\Session as Session,
  Program\Lib\Request\Request as Request,
  Program\Lib\Request\Output as Output;

/**
 * Classe abstraite pour le sso
 * les sso doivent être implémentée à partir de cette classe
 */
class Cerbere extends \Api\SSO\SSO {
  const SSO_URL = 'api/sso/cerbere/';
  
  /**
   * Constructeur par défaut du SSO
   * A surcharger si besoin
   */
  public function __construct() {
    self::$SSO_URI = \Config\IHM::$HOST . self::SSO_URL;
  }
  
  /**
   * Appel le traitement spécifique au SSO
   * @return boolean
   */
  public function process() {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Cerbere->process()");
    // Redirection vers la connexion
    header('Location: ' . $this->getLoginUrl());
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Cerbere->process() SSO URL Login : " . $this->getLoginUrl());
    exit();
  }
  
  /**
   * Builds a login URL based on the client ID and redirect URI
   * @param string $redirectUri URI de redirection
   * @return string
   */
  public function getLoginUrl($redirectUri = null, $poll = null) {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Cerbere->getLoginUrl($redirectUri)");    
    $loginUrl = self::$SSO_URI;
    
    if (isset($redirectUri)) {
      $loginUrl .= "?uri=" . urlencode(\Config\IHM::$HOST . $redirectUri);
    }
    elseif (isset($poll)) {
      $loginUrl .= "?poll=" . $poll;
    }
    
    
    return $loginUrl;
  }
  
  /**
   * Builds a logout URL based on the redirect URI
   * @param string $redirectUri URI de redirection
   * @return string
   */
  public function getLogoutUrl($redirectUri = null) {
    \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "SSO\Cerbere->getLogoutUrl($redirectUri)");
    $logoutUrl = self::$SSO_URI.'?logout';
    
    if (isset($redirectUri)) {
      $logoutUrl.= "&uri=" . urlencode(\Config\IHM::$HOST . $redirectUri);
    }
    
    return $logoutUrl;
  }
}