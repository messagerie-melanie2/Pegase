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

namespace Program\Data;

// utilisation des namespaces
use \Program\Lib\Request\Session as Session;

/**
 * Définition d'un utilisateur pour l'application de sondage
 *
 * @property int $user_id Identifiant de l'utilisateur dans la bdd
 * @property string $username Login de l'utilisateur
 * @property string $password Mot de passe de l'utilisateur
 * @property string $fullname Nom complet de l'utilisateur
 * @property string $firstname Prénom de l'utilisateur
 * @property string $department Département de l'utilisateur
 * @property string $commune Commune de l'utilisateur
 * @property string $email Adresse email de l'utilisateur
 * @property string $phone_number Numéro de téléphone de l'utilisateur
 * @property string $object Objet du rendez-vous de l'utilisateur
 * @property string $siren Numéro SIREN de l'utilisateur
 * @property string $firstname Prénom de l'utilisateur
 * @property date $created Date de création de l'utilisateur
 * @property date $modified Date de modification de l'utilisateur
 * @property date $last_login Date de dernière connexion de l'utilisateur
 * @property string $language Langue utilisé par l'utilisateur
 * @property string $preferences Préférences de l'utilisateur sérialisées
 * @property int $auth Est-ce que l'utilisateur est authentifié ou non
 * @property string $freebusy_url URL de freebusy de l'utilisateur
 * @property string $timezone Timezone de l'utilisateur
 * @property boolean $is_cerbere Est-ce que cet utilisateur a été créé par Cerbère
 *
 * @package Data
 */
class User extends MagicObject implements \JsonSerializable
{
  /***** PRIVATE ****/
  /**
   * Variable static pour l'utilisateur courant (authentifié)
   * @var User
   */
  private static $current_user;

  /******* METHODES *******/
  /**
   * Constructeur de la classe User
   * @param array $data Données à charger dans l'objet
   */
  public function __construct($data = null)
  {
    if (
      isset($data)
      && is_array($data)
    ) {
      foreach ($data as $key => $value) {
        $key = strtolower($key);
        $this->data[$key] = $value;
      }
    }
  }
  /**
   * Permet de récupérer l'utilisateur courant (authentifié)
   * @return \Program\Data\User
   */
  public static function get_current_user()
  {
    if (
      !isset(self::$current_user)
      && Session::is_set("user_id")
    ) {
      self::$current_user = new User(
        array(
          "user_id" => Session::get("user_id"),
          "username" => Session::getUsername(),
          "email" => Session::get("user_email"),
          "fullname" => Session::get("user_fullname"),
        )
      );
    }
    return self::$current_user;
  }
  /**
   * Permet de définir l'utilisateur courant (authentifié)
   * @param \Program\Data\User $user
   */
  public static function set_current_user($user)
  {
    self::$current_user = $user;
    // Mise en session des données
    Session::set('user_id', $user->user_id);
    Session::setUsername($user->username);
    Session::set('user_email', $user->email);
    Session::set('user_fullname', $user->fullname);
  }
  /**
   * Permet de savoir si le current user est défini
   * Si ce n'est pas le cas, la session n'est a priori pas validée
   * @return bool
   */
  public static function isset_current_user()
  {
    return isset(self::$current_user);
  }
  /**
   * Positionne la valeur de paramètre $freebusy_url depuis les preferences de l'utilisateur
   * @param string $freebusy_url
   * @return boolean
   */
  protected function __set_freebusy_url($freebusy_url)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($freebusy_url)) {
      unset($preferences['freebusy_url']);
    } else {
      $preferences['freebusy_url'] = $freebusy_url;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $freebusy_url depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_freebusy_url()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['freebusy_url']))
      return $preferences['freebusy_url'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $firstname depuis les preferences de l'utilisateur
   * @param string $firstname
   * @return boolean
   */
  protected function __set_firstname($firstname)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($firstname)) {
      unset($preferences['firstname']);
    } else {
      $preferences['firstname'] = $firstname;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $firstname depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_firstname()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['firstname']))
      return $preferences['firstname'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $department depuis les preferences de l'utilisateur
   * @param string $department
   * @return boolean
   */
  protected function __set_department($department)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($department)) {
      unset($preferences['department']);
    } else {
      $preferences['department'] = $department;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $department depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_department()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['department']))
      return $preferences['department'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $commune depuis les preferences de l'utilisateur
   * @param string $commune
   * @return boolean
   */
  protected function __set_commune($commune)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($commune)) {
      unset($preferences['commune']);
    } else {
      $preferences['commune'] = $commune;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $commune depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_commune()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['commune']))
      return $preferences['commune'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $phone_number depuis les preferences de l'utilisateur
   * @param string $phone_number
   * @return boolean
   */
  protected function __set_phone_number($phone_number)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($phone_number)) {
      unset($preferences['phone_number']);
    } else {
      $preferences['phone_number'] = $phone_number;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $phone_number depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_phone_number()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['phone_number']))
      return $preferences['phone_number'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $object depuis les preferences de l'utilisateur
   * @param string $object
   * @return boolean
   */
  protected function __set_object($object)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($object)) {
      unset($preferences['object']);
    } else {
      $preferences['object'] = $object;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $object depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_object()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['object']))
      return $preferences['object'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $siren depuis les preferences de l'utilisateur
   * @param string $siren
   * @return boolean
   */
  protected function __set_siren($siren)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (!isset($siren)) {
      unset($preferences['siren']);
    } else {
      $preferences['siren'] = $siren;
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $siren depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_siren()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['siren']))
      return $preferences['siren'];
    else
      // Valeur par défaut
      return null;
  }

  /**
   * Positionne la valeur de paramètre $timezone depuis les preferences de l'utilisateur
   * @param string $timezone
   * @return boolean
   */
  protected function __set_timezone($timezone)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($timezone)) {
      $preferences['timezone'] = $timezone;
    } else {
      unset($preferences['timezone']);
    }
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $timezone depuis les preferences de l'utilisateur
   * @return string
   */
  protected function __get_timezone()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['timezone']))
      return $preferences['timezone'];
    else
      // Valeur par défaut
      return date_default_timezone_get();
  }
  /**
   * Positionne la valeur de paramètre $is_cerbere depuis les preferences de l'utilisateur
   * @param boolean $is_cerbere
   * @return boolean
   */
  protected function __set_is_cerbere($is_cerbere)
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    $preferences['is_cerbere'] = $is_cerbere;
    $this->preferences = serialize($preferences);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $is_cerbere depuis les preferences de l'utilisateur
   * @return boolean
   */
  protected function __get_is_cerbere()
  {
    $preferences = unserialize($this->preferences);
    if ($preferences === false) {
      $preferences = array();
    }
    if (isset($preferences['is_cerbere']))
      return $preferences['is_cerbere'];
    else
      // Valeur par défaut
      return false;
  }

  public function jsonSerialize()
  {
    return [
      'username' => $this->username,
      'email' => $this->email,
      'fullname' => $this->fullname,
      'auth' => $this->auth,
    ];
  }
}
