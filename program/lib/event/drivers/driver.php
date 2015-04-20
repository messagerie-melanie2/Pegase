<?php
/**
 * Classe abstraite pour le driver de gestion des évènements pour l'application
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
namespace Program\Lib\Event\Drivers;

// Appel les namespaces
use Config;

/**
 * Classe abstraite pour le driver
 * les drivers doivent être implémentée à partir de cette classe
 */
abstract class Driver {
  /**
   * Défini si le driver est configurable
   * @var bool
   */
  const CONFIGURABLE = false;

  /**
   * Instance des drivers
   * @var Driver[]
   */
  private static $drivers = [];
  /**
   * Récupère l'instance du driver à utiliser pour l'utilisateur courant
   * @param string $name [Optionnel] Nom du driver à récupérer
   * @return Driver
   */
  public static function get_driver($name = null) {
    // Récupération la liste des drivers
    self::get_drivers();
    if (!isset($name)
        && count(self::$drivers) == 1) {
      // Le nom n'est pas fourni, il n'y en a qu'un
      foreach(self::$drivers as $driver) {
        return $driver;
      }
    }
    elseif (!isset($name)) {
      // Le nom n'est pas fourni, on retourne le premier driver non configurable
      foreach(self::$drivers as $driver) {
        if (!$driver::CONFIGURABLE) {
          return $driver;
        }
      }
    }
    elseif (isset(self::$drivers[$name])) {
      // Le nom est fourni, on retourne le driver en fonction du nom
      return self::$drivers[$name];
    }
    else {
      // Pas de driver trouvé, retourne null
      return null;
    }
  }
  /**
   * Récupère la liste des drivers pour l'utilisateur courant
   * @return Driver[]
   */
  public static function get_drivers() {
    // Le tableau d'instance doit être initialisé
    if (!isset(self::$drivers)) {
      self::$drivers = [];
    }
    // Parcour les drivers configuré
    $drivers = \Config\Driver::$Drivers_event;
    if (!is_array($drivers)) {
      $drivers = [$drivers];
    }
    // Parcours les drivers pour générer les instances
    foreach($drivers as $driver) {
      $driver_class = strtolower($driver);
      $driver_class = "\\Program\\Lib\\Event\\Drivers\\$driver_class\\$driver_class";
      self::$drivers[$driver] = new $driver_class();
    }
    // Retourne la liste des drivers
    return self::$drivers;
  }

  /**
   * Méthode pour uniformiser la génération de l'uid pour un événement
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @return string $uid
   */
  public function generate_event_uid($date, $poll = null) {
    // Récupération du poll courant
    if (!isset($poll)
        && \Program\Data\Poll::isset_current_poll()) {
      $poll = \Program\Data\Poll::get_current_poll();
    }
    if (isset($poll)) {
      // Retourne l'uid formaté
      return md5($poll->created) . "-" . md5($date) . "-" . $poll->poll_uid . "@" . \Config\IHM::$TITLE;
    }
    else {
      return null;
    }
  }

  /**
   * Permet de formater une date en date de début et date de fin
   * @param string $date Date de l'évènement
   * @return array $start,$end,$allday
   */
  public function date_to_start_end($date) {
    // Génération de la date
    $tmp = explode(' - ', $date);
    $start = new \DateTime($tmp[0]);
    $allday = false;
    if (isset($tmp[1])) {
      $end = new \DateTime($tmp[1]);
      // Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
      if (strlen($tmp[1]) == 10) {
        $end->add(new \DateInterval('P1D'));
        $allday = true;
      }
    } else {
      $end = clone $start;
      // Pour une journée entière on fait +1 jour pour ne pas avoir de décalage
      if (strlen($tmp[0]) == 10) {
        $end->add(new \DateInterval('P1D'));
        $allday = true;
      }
      else {
        $end->add(new \DateInterval('PT1H'));
      }
    }

    return [$start, $end, $allday];
  }

  /****** METHODES ABSTRAITES ******/
  /**
   * Génération de la liste des disponibilités pour l'utilisateur
   * Les disponibilités peuvent se situer entre une date de début et une date de fin
   * Retourne une liste d'objet Event
   * @param \DateTime $start Début des disponibilités
   * @param \DateTime $end Fin des disponibilités
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \Program\Data\Event[] Tableau contenant la liste des évènements
   */
  abstract function get_user_freebusy($start = null, $end = null, $user = null);

  /**
   * Retourne le timezone de l'utilisateur
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return string Timezone de l'utilisateur
   */
  abstract function get_user_timezone($user = null);

  /**
   * Enregistre dans l'agenda de l'utilisateur l'évènement lié au sondage
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return boolean True si la création s'est bien passée, False sinon
   */
  abstract function add_to_calendar($date, $poll = null, $user = null, $status = null);

  /**
   * Permet la génération d'un fichier ICS en fonction du sondage et de la date de la proposition
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return string Contenu ICS
   */
  abstract function generate_ics($date, $poll = null, $user = null);

  /**
   * Permet de tester si la réponse est déjà enregistré dans l'agenda de l'utilisateur
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return boolean True si l'évènement existe, False sinon
   */
  abstract function event_exists($date, $poll = null, $user = null, $status = null);

  /**
   * Supprime l'événement créé en fonction du sondage
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @return boolean True si l'événement a bien été supprimé, False sinon
   */
  abstract function delete_event($date, $poll = null, $user = null);
}