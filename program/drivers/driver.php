<?php

/**
 * Classe abstraite pour le driver de l'application
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
namespace Program\Drivers;

// Appel les namespaces
use Config;

/**
 * Classe abstraite pour le driver
 * les drivers doivent être implémentée à partir de cette classe
 */
abstract class Driver {
  /**
   * Instance du driver
   *
   * @var Driver
   */
  private static $driver;
  /**
   * Récupère l'instance du driver à utiliser
   *
   * @return Driver
   */
  public static function get_driver() {
    if (! isset($driver)) {
      $driver_class = strtolower(Config\Driver::$Driver);
      $driver_class = "\\Program\\Drivers\\$driver_class\\$driver_class";
      self::$driver = new $driver_class();
    }
    return self::$driver;
  }
  /**
   * Authentification de l'utilisateur
   * Set le current user
   *
   * @param string $username
   * @param string $password
   * @return bool true si auth ok, false sinon
   */
  abstract function authenticate($username, $password);

  /**
   * Recherche les utilisateurs dans les différents backend
   * Appelé pour l'autocomplétion
   *
   * @param string $search
   * @return \Program\Data\User[] $users Liste d'utilisateur
   */
  abstract function autocomplete($search);

  /**
   * Récupération de la liste des sondage pour l'utilisateur
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @return Program\Data\Poll[] Liste des sondages
   */
  abstract function listUserPolls($user_id);

  /**
   * Récupération de la liste des sondage auquel l'utilisateur à répondu
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @return Program\Data\Poll[] Liste des sondages
   */
  abstract function listUserRespondedPolls($user_id);

  /**
   * Récupération du nombre de réponse pour un sondage
   *
   * @param string $poll_id Identifiant du sondage
   * @return int Nombre de réponses
   */
  abstract function countPollResponses($poll_id);

  /**
   * Récupération des informations du sondage
   *
   * @param int $poll_id Identifiant du sondage
   * @return Program\Data\Poll Sondage à retourner
   */
  abstract function getPoll($poll_id);

  /**
   * Récupération des informations du sondage
   *
   * @param string $poll_uid Identifiant unique du sondage
   * @return Program\Data\Poll Sondage à retourner
   */
  abstract function getPollByUid($poll_uid);

  /**
   * Récupération de la liste des réponses pour un sondage
   *
   * @param string $poll_id Identifiant du sondage
   * @return Program\Data\Response[] Liste des réponses pour le sondage
   */
  abstract function getPollResponses($poll_id);

  /**
   * Récupère l'utilisateur en fonction du username
   * Il s'agit donc forcément d'un utilisateur authentifié
   *
   * @param string $username
   * @return Program\Data\User
   */
  abstract function getAuthUser($username);

  /**
   * Récupère l'utilisateur en fonction du user_id
   *
   * @param int $user_id
   * @return Program\Data\User
   */
  abstract function getUser($user_id);

  /**
   * Création d'un utilisateur
   *
   * @param Program\Data\User $user
   * @return $user_id si OK, null sinon
   */
  abstract function addUser(\Program\Data\User $user);

  /**
   * Modification d'un utilisateur
   *
   * @param Program\Data\User $user
   * @return bool true si ok, false sinon
   */
  abstract function modifyUser(\Program\Data\User $user);

  /**
   * Suppression de l'utilisateur
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @return bool True si ok, false sinon
   */
  abstract function deleteUser($user_id);

  /**
   * Création d'un nouveau sondage
   *
   * @param Program\Data\Poll $poll
   * @return $poll_id si ok, null sinon
   */
  abstract function createPoll(\Program\Data\Poll $poll);

  /**
   * Modification d'un sondage existant
   *
   * @param Program\Data\Poll $poll
   * @return bool true si ok, false sinon
   */
  abstract function modifyPoll(\Program\Data\Poll $poll);

  /**
   * Suppression d'un sondage
   *
   * @param string $poll_id
   * @return bool true si ok, false sinon
   */
  abstract function deletePoll($poll_id);

  /**
   * Test si l'identifiant unique du sondage existe déjà
   *
   * @param string $poll_uid
   * @return bool true si l'uid existe, false sinon
   */
  abstract function isPollUidExists($poll_uid);

  /**
   * Récupère la réponse de l'utilisateur sur un sondage
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @param string $poll_id Identifiant du sondage
   * @return \Program\Data\Response
   */
  abstract function getPollUserResponse($user_id, $poll_id);

  /**
   * Ajoute une réponse pour l'utilisateur sur un sondage
   *
   * @param \Program\Data\Response $response
   * @return true si ok, false sinon
   */
  abstract function addPollUserResponse(\Program\Data\Response $response);

  /**
   * Modifie la réponse de l'utilisateur sur un sondage
   *
   * @param \Program\Data\Response $response
   * @return true si ok, false sinon
   */
  abstract function modifyPollUserResponse(\Program\Data\Response $response);

  /**
   * Supprime la réponse de l'utilisateur sur un sondage
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @param string $poll_id Identifiant du sondage
   * @return true si ok, false sinon
   */
  abstract function deletePollUserResponse($user_id, $poll_id);

  /**
   * Récupère la liste des événements de l'utilisateur sur un sondage
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @param int $poll_id Identifiant du sondage
   * @return \Program\Data\EventsList
   */
  abstract function getPollUserEventsList($user_id, $poll_id);

  /**
   * Enregistre les events list pour l'utilisateur sur un sondage
   *
   * @param \Program\Data\EventsList $eventslist
   * @return true si ok, false sinon
   */
  abstract function addPollUserEventsList(\Program\Data\EventsList $eventslist);

  /**
   * Modifie les events list de l'utilisateur sur un sondage
   *
   * @param \Program\Data\EventsList $eventslist
   * @return true si ok, false sinon
   */
  abstract function modifyPollUserEventsList(\Program\Data\EventsList $eventslist);

  /**
   * Supprime les events list de l'utilisateur sur un sondage
   *
   * @param int $user_id Identifiant de l'utilisateur
   * @param string $poll_id Identifiant du sondage
   * @return true si ok, false sinon
   */
  abstract function deletePollUserEventsList($user_id, $poll_id);

  /**
   * Permet de récupérer une liste de réponses dans un lapse de temps
   *
   * @param int $user_id
   * @param \DateTime $start
   * @param \DateTime $end
   * @return \Program\Data\Response[]
   */
  abstract function getResponsesByRange($user_id, $start, $end);

  /**
   * *** SESSION ********
   */
  /**
   * Retourne la session en fonction de son identifiant
   *
   * @param string $session_id Identifiant de session
   * @return \Program\Data\Session
   */
  abstract function getSession($session_id);

  /**
   * Création d'une session
   *
   * @param \Program\Data\Session $session
   * @return $session_id si OK, null sinon
   */
  abstract function createSession(\Program\Data\Session $session);

  /**
   * Modification d'une session
   *
   * @param \Program\Data\Session $session
   * @return bool true si ok, false sinon
   */
  abstract function modifySession(\Program\Data\Session $session);

  /**
   * Suppression de la session
   *
   * @param string $session_id Identifiant de la session à supprimer
   * @return bool true si ok, false sinon
   */
  abstract function deleteSession($session_id);

  /**
   * Supprime toutes les sessions expirées
   *
   * @param number $lifetime Durée de vie d'une session
   * @return bool true si ok, false sinon
   */
  abstract function deleteOldSessions($lifetime = 3600);

  /**
   * *** STATISTIQUES ******
   */
  /**
   * [STATISTIQUES]
   * Récupération du nombre d'utilisateur authentifié qui se sont connecté entre start et end
   *
   * @param DateTime $start Début des recherches pour les statistiques
   * @param DateTime $end Fin des recherches pour les statistiques
   * @return int Nombre d'utilisateurs connectés
   */
  abstract function countAuthUsers($start, $end);

  /**
   * [STATISTIQUES]
   * Récupération du nombre d'utilisateur non authentifié qui ont été créé entre start et end
   *
   * @param DateTime $start Début des recherches pour les statistiques
   * @param DateTime $end Fin des recherches pour les statistiques
   * @return int Nombre d'utilisateurs non authentifié
   */
  abstract function countNoauthUsers($start, $end);

  /**
   * [STATISTIQUES]
   * Récupération du nombre de sondages qui ont été créé entre start et end
   *
   * @param DateTime $start Début des recherches pour les statistiques
   * @param DateTime $end Fin des recherches pour les statistiques
   * @return int Nombre de sondages créés
   */
  abstract function countPolls($start, $end);

  /**
   * [STATISTIQUES]
   * Récupération du nombre de réponses qui ont été faites entre start et end
   *
   * @param DateTime $start Début des recherches pour les statistiques
   * @param DateTime $end Fin des recherches pour les statistiques
   * @return int Nombre de réponses faites
   */
  abstract function countResponses($start, $end);
}