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
use Program\Lib\Request\Session as Session;
use Program\Lib\Request\Request as Request;
use Program\Lib\Request\Output as Output;

/**
 * Classe de creation de l'utilisateur depuis l'interface
 *
 * @package Lib
 * @subpackage Templates
 */
class Register extends Template {

  /**
   * Execution de la requête
   */
  public static function Process() {
    // Session instanciée
    if (Session::is_setUsername() && Session::is_setPassword()) {
      // Instancie l'objet
      return Session::validateSession();
    }
    else {
      $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
      if (Session::validateCSRFToken($csrf_token)) {
        if (! isset($_POST['username'])) {
          // Pas de donnée POST
          return false;
        }
        $username = trim(strtolower(Request::getInputValue("username", POLL_INPUT_POST)));
        $fullname = trim(strtolower(Request::getInputValue("fullname", POLL_INPUT_POST)));
        $email = trim(strtolower(Request::getInputValue("email", POLL_INPUT_POST)));
        $password = Request::getInputValue("password", POLL_INPUT_POST);
        if (isset($username) && isset($password) && isset($fullname) && isset($email)) {
          $user = new \Program\Data\User(["auth" => 1,
                  "fullname" => $fullname,"username" => $username,
                  "email" => $email,"password" => $password,
                  "last_login" => date("Y-m-d H:i:s"),
                  "language" => \Config\IHM::$DEFAULT_LOCALIZATION]);
          $find_user = \Program\Drivers\Driver::get_driver()->getAuthUser($username);
          if (isset($find_user)) {
            Output::set_env("error", "Username already exists");
            return false;
          }
          if (\Program\Drivers\Driver::get_driver()->addUser($user)) {
            Session::setUsername($username);
            Session::setPassword($password);
            Session::setToken();
            \Program\Data\User::set_current_user($user);
            \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Register::Process() Register for user $username");
            Output::set_env("message", "You are now created, you can start use the app");
            return true;
          }
          else {
            \Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Register::Process() Register error for user $username");
            Output::set_env("error", "Error when register the data");
            return false;
          }
        }
        else {
          Output::set_env("error", "Error, you have to put all the information");
          return false;
        }
      }
      else {
        Output::set_env("error", "Invalid request");
        return false;
      }
    }
  }
}