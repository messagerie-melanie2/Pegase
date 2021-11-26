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
use Program\Lib\Utils\Utils as u;

/**
 * Définition d'un sondage pour l'application de sondage
 *
 * @property int $poll_id Identifiant du sondage dans la bdd
 * @property string $poll_uid Identifiant unique du sondage
 * @property string $title Titre du sondage
 * @property string $max_attendees_per_prop Nombre de participants par proposition du sondage
 * @property boolean $prop_in_agenda Définit si les propositions sont ajoutés à l'Agenda de l'organisateur
 * @property string $location Emplacement du sondage
 * @property string $description Description du sondage
 * @property int $organizer_id Organisateur du sondage
 * @property string $organizer_username Username de l'organisateur du sondage
 * @property string $organizer_email Email de l'organisateur du sondage
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
 * @property boolean $cgu_accepted Si le créateur du sondage a accepté de cocher les cgu
 * @property array $validate_proposals Liste des propositions validées par l'organisateur du sondage
 * @property date $date_start Première date de proposition pour le sondage (sondage de dates)
 * @property date $date_end Dernière date de proposition pour le sondage (sondage de dates)
 * @property date $deadline Date butoir avant la fermeture des votes du sondage
 * @property string $timezone Timezone du sondage
 * @property \Program\Data\Response[] $response Réponses du sondage
 *
 * @package Data
 */
class Poll extends MagicObject implements \JsonSerializable
{
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
  public function __construct($data = null)
  {
    if (
      isset($data)
      && is_array($data)
    ) {
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
  public static function get_current_poll()
  {
    self::load_current_poll();
    return self::$current_poll;
  }
  /**
   * Permet de définir le sondage courant
   * @param \Program\Data\Poll $poll
   */
  public static function set_current_poll($poll)
  {
    self::$current_poll = $poll;
  }
  /**
   * Permet de savoir si le current poll est défini
   * @return bool
   */
  public static function isset_current_poll()
  {
    self::load_current_poll();
    return isset(self::$current_poll);
  }
  /**
   * Méthode pour générer un identifiant unique pour le sondage
   * @return string
   */
  public static function generation_uid()
  {
    $count = 0;
    $uid = null;
    do {
      $uid = u::random_string();
      $count++;
      if ($count == 5) break;
    } while (\Program\Drivers\Driver::get_driver()->isPollUidExists($uid));
    return $uid;
  }

  /**
   * Charge le current poll depuis la base de données si ce n'est pas déjà fait
   */
  private static function load_current_poll()
  {
    if (
      !isset(self::$current_poll)
      && !self::$current_poll_loaded
    ) {
      if (o::isset_env("poll_uid")) {
        self::$current_poll = \Program\Drivers\Driver::get_driver()->getPollByUid(o::get_env("poll_uid"));
        if (!isset(self::$current_poll->poll_id))
          self::$current_poll = null;
      }
      self::$current_poll_loaded = true;
    }
  }
  /**
   * Positionne la valeur de paramètre $max_attendees_per_prop depuis les settings du sondage
   * @param int $max_attendees_per_prop
   * @return boolean
   */
  protected function __set_max_attendees_per_prop($max_attendees_per_prop)
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    //Valeur minimum à 1
    $settings['max_attendees_per_prop'] = ($max_attendees_per_prop > 0) ? $max_attendees_per_prop : 1;
    $this->settings = serialize($settings);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $max_attendees_per_prop depuis les settings du sondage
   * @return int
   */
  protected function __get_max_attendees_per_prop()
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    if (isset($settings['max_attendees_per_prop']))
      return $settings['max_attendees_per_prop'];
    else
      // Valeur par défaut
      return 1;
  }
  /**
   * Retourne le nombre de participant par prop d'un sondage
   * @param Poll $poll
   * @return array
   */
  public static function get_nb_attendees_per_prop($poll)
  {
    $nb_attendees_per_prop = [];
    $responses = [];
    $proposals = unserialize($poll->proposals);

    foreach (\Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id) as $key => $response) {
			$response = unserialize($response->response);
			foreach ($response as $key => $serialize_response) {
				$proposal_key = array_search($key, $proposals);
				if ($proposal_key !== false) {
					array_push($nb_attendees_per_prop, $proposal_key);
				}
			}
		}
    $responses = array_count_values($nb_attendees_per_prop);

    return $responses;
  }
  /**
   * Positionne la valeur de paramètre $prop_in_agenda depuis les settings du sondage
   * @param int $prop_in_agenda
   * @return boolean
   */
  protected function __set_prop_in_agenda($prop_in_agenda)
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    $settings['prop_in_agenda'] = $prop_in_agenda;
    $this->settings = serialize($settings);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $prop_in_agenda depuis les settings du sondage
   * @return int
   */
  protected function __get_prop_in_agenda()
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    if (isset($settings['prop_in_agenda']))
      return $settings['prop_in_agenda'];
    else
      // Valeur par défaut
      return 1;
  }
  /**
   * Positionne la valeur de paramètre $auth_only depuis les settings du sondage
   * @param boolean $auth_only
   * @return boolean
   */
  protected function __set_auth_only($auth_only)
  {
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
  protected function __get_auth_only()
  {
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
  protected function __set_validate_proposals($validate_proposals)
  {
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
  protected function __get_validate_proposals()
  {
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
  protected function __set_if_needed($if_needed)
  {
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
  protected function __get_if_needed()
  {
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
   * Positionne la valeur de paramètre $anonymous dans les settings du sondage
   * @param boolean $anonymous
   * @return boolean
   */
  protected function __set_anonymous($anonymous)
  {
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
  protected function __get_anonymous()
  {
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
  /**
   * Positionne la valeur de paramètre $cgu_accepted dans les settings du sondage
   * @param boolean $anonymous
   * @return boolean
   */
  protected function __set_cgu_accepted($cgu_accepted)
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    $settings['cgu_accepted'] = $cgu_accepted;
    $this->settings = serialize($settings);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $cgu_accepted depuis les settings du sondage
   * @return boolean
   */
  protected function __get_cgu_accepted()
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    if (isset($settings['cgu_accepted']))
      return $settings['cgu_accepted'];
    else
      // Valeur par défaut
      return false;
  }
  /**
   * Positionne la valeur de paramètre $timezone dans les settings du sondage
   * @param string $timezone
   * @return boolean
   */
  protected function __set_timezone($timezone)
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    if (isset($timezone)) {
      $settings['timezone'] = $timezone;
    } else {
      unset($settings['timezone']);
    }
    $this->settings = serialize($settings);
    return true;
  }
  /**
   * Retourne la valeur de paramètre $timezone depuis les settings du sondage
   * @return string
   */
  protected function __get_timezone()
  {
    $settings = unserialize($this->settings);
    if ($settings === false) {
      $settings = array();
    }
    if (isset($settings['timezone']))
      return $settings['timezone'];
    else
      // Valeur par défaut
      return date_default_timezone_get();
  }

  /**
   * Retourne la valeur du champ date_start
   * Si le date start n'existe pas dans le sondage, il est calculé en fonction des propositions
   * @return date
   */
  protected function __get_date_start()
  {
    if ($this->type != "date" &&  $this->type != "rdv") {
      return null;
    }
    if (
      !isset($this->date_start)
      && isset($this->proposals)
      && !empty($this->proposals)
    ) {
      $proposals = unserialize($this->proposals);
      if (
        $proposals !== false
        && is_array($proposals)
        && count($proposals) > 0
      ) {
        // Parcourir les proposition pour trouver le start et end
        foreach ($proposals as $prop_key => $prop_value) {
          if (strpos($prop_value, ' - ')) {
            $prop = explode(' - ', $prop_value, 2);
            $prop_start = new \DateTime($prop[0]);
            $prop_end = new \DateTime($prop[1]);
          } else {
            $prop_start = new \DateTime($prop_value);
            $prop_end = clone $prop_start;
          }
          $prop_end->add(new \DateInterval('P1D'));
          if (
            !isset($start)
            && !isset($end)
          ) {
            // Positionnement de la date de début et de fin
            $start = $prop_start;
            $end = $prop_end;
          } else {
            if ($prop_end > $end) {
              // Si tmp est supérieur, on conserve comme fin
              $end = $prop_end;
            }
            if ($prop_start < $start) {
              // Si tmp est inférieur, on conserve comme début
              $start = $prop_start;
            }
          }
        }
        $this->date_start = $start->format('Y-m-d H:i:s');
        $this->date_end = $end->format('Y-m-d H:i:s');
      }
    }
    return $this->data['date_start'];
  }
  /**
   * Retourne la valeur du champ date_end
   * Si le date end n'existe pas dans le sondage, il est calculé en fonction des propositions
   * @return date
   */
  protected function __get_date_end()
  {
    if ($this->type != "date" &&  $this->type != "rdv") {
      return null;
    }
    if (
      !isset($this->date_end)
      && isset($this->proposals)
      && !empty($this->proposals)
    ) {
      $proposals = unserialize($this->proposals);
      if (
        $proposals !== false
        && is_array($proposals)
        && count($proposals) > 0
      ) {
        // Parcourir les proposition pour trouver le start et end
        foreach ($proposals as $prop_key => $prop_value) {
          if (strpos($prop_value, ' - ')) {
            $prop = explode(' - ', $prop_value, 2);
            $prop_start = new \DateTime($prop[0]);
            $prop_end = new \DateTime($prop[1]);
          } else {
            $prop_start = new \DateTime($prop_value);
            $prop_end = clone $prop_start;
          }
          $prop_end->add(new \DateInterval('P1D'));
          if (
            !isset($start)
            && !isset($end)
          ) {
            // Positionnement de la date de début et de fin
            $start = $prop_start;
            $end = $prop_end;
          } else {
            if ($prop_end > $end) {
              // Si tmp est supérieur, on conserve comme fin
              $end = $prop_end;
            }
            if ($prop_start < $start) {
              // Si tmp est inférieur, on conserve comme début
              $start = $prop_start;
            }
          }
        }
        $this->date_start = $start->format('Y-m-d H:i:s');
        $this->date_end = $end->format('Y-m-d H:i:s');
      }
    }
    return $this->data['date_end'];
  }

  public function jsonSerialize()
  {
    if (isset($this->response)) {
      return [
        'poll_uid' => $this->poll_uid,
        'title' => $this->title,
        'location' => $this->location,
        'description' => $this->description,
        'created' => $this->created,
        'modified' => $this->modified,
        'organizer_id' => $this->organizer_id,
        'organizer_username' => $this->organizer_username,
        'organizer_email' => $this->organizer_email,
        'response' => $this->response,
      ];
    } elseif (isset($this->organizer_username)) {
      return [
        'poll_uid' => $this->poll_uid,
        'title' => $this->title,
        'location' => $this->location,
        'description' => $this->description,
        'created' => $this->created,
        'modified' => $this->modified,
        'organizer_id' => $this->organizer_id,
        'organizer_username' => $this->organizer_username,
        'organizer_email' => $this->organizer_email,
      ];
    } else {
      return [
        'poll_uid' => $this->poll_uid,
        'title' => $this->title,
        'location' => $this->location,
        'description' => $this->description,
        'created' => $this->created,
        'modified' => $this->modified,
        'organizer_id' => $this->organizer_id,
      ];
    }
  }
}
