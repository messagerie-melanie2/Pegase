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
 * Définition d'un événement pour l'application de sondage
 *
 * @property string $uid Identifiant unique de l'événement
 * @property string $title Titre de l'événement
 * @property string $location Emplacement de l'événement
 * @property string $description Description de l'événement
 * @property string $status Statut de l'événement
 * @property string $class Classe de l'événement
 * @property \DateTime $start Date de début de l'évènement
 * @property \DateTime $end Date de fin de l'évènement
 * @property boolean $allday Si l'évènement est sur une journée entière
 *
 * @package Data
 */
class Event extends Object {
    // STATUS Fields
    const STATUS_TENTATIVE = 'TENTATIVE';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_NONE = 'NONE';

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
}