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

// Utilisation des namespaces
use Program\Lib\Request\Output as o;

/**
 * Définition d'un sondage pour l'application de sondage
 *
 * @property int $poll_id Identifiant du sondage dans la bdd
 * @property string $poll_uid Identifiant unique du sondage
 * @property string $title Titre du sondage
 * @property string $location Emplacement du sondage
 * @property string $description Description du sondage
 * @property int $organizer_id Organisateur du sondage
 * @property date $created Date de création du sondage
 * @property date $modified Date de dernière modification du sondage
 * @property string $proposals Liste des propositions du sondage
 * @property string $settings Liste des paramètres du sondage
 * @property string $type Type de sondage
 * @property int $locked Si le sondage est vérouillé
 * @property int $deleted Si le sondage est supprimé
 * @property int $count_responses Nombre de réponses pour le sondage
 * @property boolean $auth_only Si le sondage est réservé aux utilisateurs authentifiés
 * @property boolean $if_needed Si le sondage propose aux répondeurs un "si besoin"
 * @property boolean $anonymous Si le sondage est en mode anonyme, les utilisateurs ne voient pas les réponses des autres
 * @property array $validate_proposals Liste des propositions validées par l'organisateur du sondage
 *
 * @package Data
 */
class Poll extends Object {
    /***** PRIVATE ****/
    /**
     * Variable static pour le sondage courant
     * @var \Program\Data\Poll
     */
    private static $current_poll;
    /**
     * Savoir si le sondage courant a déjà été chargé depuis la base de données
     * @var bool
     */
    private static $current_poll_loaded = false;

    /******* METHODES *******/
    /**
     * Constructeur par défaut de la classe Poll
     * @param array $data Données à charger dans l'objet
     */
    public function __construct($data = null) {
        if (isset($data)
                && is_array($data)) {
            foreach ($data as $key => $value) {
                $key = strtolower($key);
                $this->$key = $value;
            }
        }
    }
    /**
     * Permet de récupérer le sondage courant
     * @return \Program\Data\Poll
     */
    public static function get_current_poll() {
        self::load_current_poll();
        return self::$current_poll;
    }
    /**
     * Permet de définir le sondage courant
     * @param \Program\Data\Poll $poll
     */
    public static function set_current_poll($poll) {
        self::$current_poll = $poll;
    }
    /**
     * Permet de savoir si le current poll est défini
     * @return bool
     */
    public static function isset_current_poll() {
        self::load_current_poll();
        return isset(self::$current_poll);
    }
    /**
     * Méthode pour générer un identifiant unique pour le sondage
     * @return string
     */
    public static function generation_uid() {
        $count = 0;
        $uid = null;
        do {
            $uid = self::random_string();
            $count++;
            if ($count == 5) break;
        } while(\Program\Drivers\Driver::get_driver()->isPollUidExists($uid));
        return $uid;
    }
    /**
     * Génération d'une chaine de caractères aléatoire
     * @return string
     */
    private static function random_string()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 20; $i++) {
            $randstring .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randstring;
    }
    /**
     * Charge le current poll depuis la base de données si ce n'est pas déjà fait
     */
    private static function load_current_poll() {
        if (!isset(self::$current_poll)
                && !self::$current_poll_loaded) {
            if (o::isset_env("poll_uid")) {
                self::$current_poll = \Program\Drivers\Driver::get_driver()->getPollByUid(o::get_env("poll_uid"));
                if (!isset(self::$current_poll->poll_id))
                        self::$current_poll = null;
            }
            self::$current_poll_loaded = true;
        }
    }
    /**
     * Positionne la valeur de paramètre $auth_only depuis les settings du sondage
     * @param boolean $auth_only
     * @return boolean
     */
    protected function __set_auth_only($auth_only) {
        $settings = unserialize($this->settings);
        if ($settings === false) {
            $settings = array();
        }
        $settings['auth_only'] = $auth_only;
        $this->settings = serialize($settings);
        return true;
    }
    /**
     * Retourne la valeur de paramètre $auth_only depuis les settings du sondage
     * @return boolean
     */
    protected function __get_auth_only() {
        $settings = unserialize($this->settings);
        if ($settings === false) {
            $settings = array();
        }
        if (isset($settings['auth_only']))
            return $settings['auth_only'];
        else
            // Valeur par défaut
            return false;
    }
    /**
     * Positionne la valeur de paramètre $validate_proposals depuis les settings du sondage
     * @param array $validate_proposals Liste des propositions validées
     * @return boolean
     */
    protected function __set_validate_proposals($validate_proposals) {
        $settings = unserialize($this->settings);
        if ($settings === false) {
            $settings = array();
        }
        $settings['validate_proposals'] = $validate_proposals;
        $this->settings = serialize($settings);
        return true;
    }
    /**
     * Retourne la valeur de paramètre $validate_proposals depuis les settings du sondage
     * @return array
     */
    protected function __get_validate_proposals() {
        $settings = unserialize($this->settings);
        if ($settings === false) {
            $settings = array();
        }
        if (isset($settings['validate_proposals']))
            return $settings['validate_proposals'];
        else
            // Valeur par défaut
            return array();
    }
    /**
     * Positionne la valeur de paramètre $if_needed depuis les settings du sondage
     * @param boolean $if_needed
     * @return boolean
     */
    protected function __set_if_needed($if_needed) {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      $settings['if_needed'] = $if_needed;
      $this->settings = serialize($settings);
      return true;
    }
    /**
     * Retourne la valeur de paramètre $if_needed depuis les settings du sondage
     * @return boolean
     */
    protected function __get_if_needed() {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      if (isset($settings['if_needed']))
        return $settings['if_needed'];
      else
        // Valeur par défaut
        return false;
    }
    /**
     * Positionne la valeur de paramètre $anonymous depuis les settings du sondage
     * @param boolean $anonymous
     * @return boolean
     */
    protected function __set_anonymous($anonymous) {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      $settings['anonymous'] = $anonymous;
      $this->settings = serialize($settings);
      return true;
    }
    /**
     * Retourne la valeur de paramètre $anonymous depuis les settings du sondage
     * @return boolean
     */
    protected function __get_anonymous() {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      if (isset($settings['anonymous']))
        return $settings['anonymous'];
      else
        // Valeur par défaut
        return false;
    }
}