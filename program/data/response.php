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

/**
 * Définition de la réponse d'un utilisateur pour un sondage
 *
 * @property int $user_id Identifiant de l'utilisateur dans la bd
 * @property int $poll_id Identifiant du sondage dans la bdd
 * @property string $response Données de réponse de l'utilisateur pour le sondage, sérialisées
 * @property string $calendar_id Identifiant du calendrier utilisé si ce n'est pas celui par défaut de l'utilisateur
 * @property string $calendar_name Nom du calendrier utilisé si ce n'est pas celui par défaut de l'utilisateur
 *
 * @package Data
 */
class Response extends Object {
    /******* METHODES *******/
    /**
     * Constructeur par défaut de la classe Response
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
     * Positionne la valeur de paramètre $calendar_id depuis les settings de la réponse
     * @param string $calendar_id
     * @return string
     */
    protected function __set_calendar_id($calendar_id) {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      $settings['calendar_id'] = $calendar_id;
      $this->settings = serialize($settings);
      return true;
    }
    /**
     * Retourne la valeur de paramètre $calendar_id depuis les settings de la réponse
     * @return string
     */
    protected function __get_calendar_id() {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      if (isset($settings['calendar_id']))
        return $settings['calendar_id'];
      else
        // Valeur par défaut
        return null;
    }
    /**
     * Positionne la valeur de paramètre $calendar_name depuis les settings de la réponse
     * @param string $calendar_name
     * @return string
     */
    protected function __set_calendar_name($calendar_name) {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      $settings['calendar_name'] = $calendar_name;
      $this->settings = serialize($settings);
      return true;
    }
    /**
     * Retourne la valeur de paramètre $calendar_name depuis les settings de la réponse
     * @return string
     */
    protected function __get_calendar_name() {
      $settings = unserialize($this->settings);
      if ($settings === false) {
        $settings = array();
      }
      if (isset($settings['calendar_name']))
        return $settings['calendar_name'];
      else
        // Valeur par défaut
        return null;
    }
}