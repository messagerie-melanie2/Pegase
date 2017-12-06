<?php

/**
 * Classe pour le driver de gestion des évènements pour l'application
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
namespace Program\Lib\Event\Drivers\Pegase;

/**
 * Classe pour le driver de gestion des évènements
 * les drivers doivent être implémentée à partir de cette classe
 */
class Pegase extends \Program\Lib\Event\Drivers\Driver {
	/**
	 * Est-ce que le driver permet l'affichage des freebusy
	 * @var boolean
	 */
	public $CAN_GET_FREEBUSY = false;

	/**
	 * Est-ce que le driver permet d'écrire dans le calendrier
	 * @var boolean
	 */
	public $CAN_WRITE_CALENDAR = false;

	/**
	 * Est-ce que le driver permet de générer un fichier ICS
	 * @var boolean
	 */
	public $CAN_GENERATE_ICS = true;

  /**
   * Enregistre dans l'agenda de l'utilisateur l'évènement lié au sondage
   *
   * @param string $date Date de l'évènement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status \Program\Data\Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param string $part_status \Program\Data\Event::PARTSTAT_* [Optionnel] Statut de participant pour la génération de la réunion
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @param boolean $selected_date Date retenue par l'organisateur ?
   * @return string UID de l'événement créé si OK, null sinon
   */
  public function add_to_calendar($date, $poll = null, $user = null, $status = null, $part_status = null, $calendar = null, $selected_date = false) {
    return false;
  }

  /**
   * Liste les calendriers accessibles pour l'utilisateur (en lecture/écriture)
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @return \Program\Data\Calendar[] Tableau contenant la liste des calendriers
   */
  public function list_user_calendars($user = null) {
    // Récupération de l'utilisateur
    if (! isset($user)) {
      // Si il n'y a pas d'utilisateur connecté
      if (\Program\Data\User::isset_current_user()) {
        $user = \Program\Data\User::get_current_user();
      }
      else {
        return [];
      }
    }
    // Génération du calendrier basé sur l'utilisateur
    return [new \Program\Data\Calendar([
            'id' => $user->username,
            'owner' => $user->username,
            'name' => $user->fullname,
    ])];
  }

  /**
   * Permet de tester si la réponse est déjà enregistré dans l'agenda de l'utilisateur
   *
   * @param string $date Date de l'évènement
   * @param string $event_uid [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param boolean $request S'agit il d'une request, permet de définir METHOD:REQUEST
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'évènement existe, False sinon
   */
  public function event_exists($date, $event_uid = null, $poll = null, $user = null, $status = null, $calendar = null) {
    return false;
  }

  /**
   * Supprime l'événement créé en fonction du sondage
   *
   * @param string $date Date de l'évènement
   * @param string $event_uid [Optionnel] UID de l'événement
   * @param \Program\Data\Poll $poll [Optionnel] Sondage à utiliser, si ce n'est pas le courant
   * @param \Program\Data\User $user [Optionnel] Utilisateur si ce n'est pas le courant
   * @param string $status Event::STATUS_* [Optionnel] Statut lié à l'évènement
   * @param \Program\Data\Calendar $calendar [Optionnel] Calendrier si ce n'est pas le courant
   * @return boolean True si l'événement a bien été supprimé, False sinon
   */
  public function delete_event($date, $event_uid = null, $poll = null, $user = null, $calendar = null) {
    return false;
  }
}