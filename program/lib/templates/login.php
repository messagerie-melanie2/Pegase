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
use Program\Lib\Request\Session as Session, Program\Lib\Request\Request as Request, Program\Lib\Request\Output as Output;

/**
 * Classe de gestion du login utilisateur pour l'application de sondage
 *
 * @package Lib
 * @subpackage Templates
 */
class Login extends Template {

  /**
   * Execution de la requête d'authentification
   *
   * @return multitype:string
   */
  public static function Process() {
    // Utilisation du SSO
    if (isset(\Config\IHM::$USE_SSO) && \Config\IHM::$USE_SSO) {
      return \Api\SSO\SSO::get_sso()->process();
    }
    // Session instanciée
    if (Session::is_setUsername() && Session::is_setPassword()) {
      // Instancie l'objet
      return Session::validateSession();
    }
    else {
      $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
      if (Session::validateCSRFToken($csrf_token)) {
        $username = trim(strtolower(Request::getInputValue("username", POLL_INPUT_POST)));
        $password = Request::getInputValue("password", POLL_INPUT_POST);
        if (isset($username) && isset($password)) {
          if (\Program\Drivers\Driver::get_driver()->authenticate($username, $password)) {
            Session::setUsername($username);
            Session::setPassword($password);
            Session::setToken();
            \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Login::Process() Login for user $username");
            $poll_uid = Request::getInputValue("_poll", POLL_INPUT_GET);
            $params = urldecode(Request::getInputValue("_params", POLL_INPUT_GET));
    		    if (!empty($poll_uid)) {
    		    	header("Location: " . Output::get_poll_url(new \Program\Data\Poll(["poll_uid" => $poll_uid])) . "$params" );
    		    	exit();
    		    }
            return true;
          }
          else {
            \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Login::Process() Bad login for user $username");
            Output::set_env("error", "Auth error, bad login or password");
            return false;
          }
        }
        else {
          return false;
        }
      }
      elseif (isset($_POST['username'])) {
        Output::set_env("error", "Invalid request");
        return false;
      }
      else {
        return false;
      }
    }
  }
}