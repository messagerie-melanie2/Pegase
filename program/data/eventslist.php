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
 * Définition de la liste des événements lié à un sondage et un utilisateur
 *
 * @property int $user_id Identifiant de l'utilisateur dans la bd
 * @property int $poll_id Identifiant du sondage dans la bdd
 * @property string $events Liste d'uid des événements de l'utilisateur pour le sondage, sérialisées
 * @property string $events_status Status des événements (Provisoire, Confirme ou Libre)
 * @property string $settings Paramètres pour la gestion des événements
 * @property array $events_part_status Liste des part_status associés aux events
 * @property int $modified_time timestamp pour la modification
 *
 * @package Data
 */
class EventsList extends MagicObject {
		/***** PRIVATE ****/
		/**
		 * Variable static pour le EventsList courant
		 * @var \Program\Data\EventsList
		 */
		private static $current_eventslist;
		/**
		 * Savoir si le EventsList courant a déjà été chargé depuis la base de données
		 * @var bool
		 */
		private static $current_eventslist_loaded = false;

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
     * Permet de récupérer le EventsList courant
     * @return \Program\Data\EventsList
     */
    public static function get_current_eventslist() {
    	self::load_current_eventslist();
    	return self::$current_eventslist;
    }
    /**
     * Permet de définir le EventsList courant
     * @param \Program\Data\EventsList $eventslist
     */
    public static function set_current_eventslist($eventslist) {
    	self::$current_eventslist = $eventslist;
    }
    /**
     * Permet de savoir si le current EventsList est défini
     * @return bool
     */
    public static function isset_current_eventslist() {
    	self::load_current_eventslist();
    	return isset(self::$current_eventslist);
    }
    /**
     * Charge le current EventsList depuis la base de données si ce n'est pas déjà fait
     */
    private static function load_current_eventslist() {
    	if (!isset(self::$current_eventslist)
    			&& !self::$current_eventslist_loaded) {
    		if (Poll::isset_current_poll()
    				&& User::isset_current_user()) {
					self::$current_eventslist = \Program\Drivers\Driver::get_driver()->getPollUserEventsList(User::get_current_user()->user_id, Poll::get_current_poll()->poll_id);
					if (!isset(self::$current_eventslist->poll_id))
						self::$current_eventslist = null;
    		}
    		self::$current_eventslist_loaded = true;
    	}
    }
    /**
     * Positionne la valeur de paramètre $events_part_status depuis les settings du eventslist
     * @param array $events_part_status Liste des part_status associés aux events
     * @return boolean
     */
    protected function __set_events_part_status($events_part_status) {
    	$settings = unserialize($this->settings);
    	if ($settings === false) {
    		$settings = array();
    	}
    	$settings['events_part_status'] = $events_part_status;
    	$this->settings = serialize($settings);
    	return true;
    }
    /**
     * Retourne la valeur de paramètre $events_part_status depuis les settings du eventslist
     * @return array
     */
    protected function __get_events_part_status() {
    	$settings = unserialize($this->settings);
    	if ($settings === false) {
    		$settings = array();
    	}
    	if (isset($settings['events_part_status']))
    		return $settings['events_part_status'];
    	else
    		// Valeur par défaut
    		return array();
    }
}