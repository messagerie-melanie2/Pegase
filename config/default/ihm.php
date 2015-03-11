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
namespace Config;

/**
 * Configuration de l'ihm pour l'application de sondage
 *
 * @package Config
 */
class IHM {
    /**
     * Configuration de la localization par défaut
     */
    public static $DEFAULT_LOCALIZATION = "fr_FR";
	/**
	 * Configuration du nom de la skin à utiliser pour l'ihm
	 */
	public static $SKIN = 'default';
	/**
	 * Configuration du nom de l'application
	 */
	public static $TITLE = 'Pégase';
	/**
	 * Type de stockage pour les sessions, 'php' ou 'memcache'
	 */
	public static $SESSION_TYPE = 'memcache';
	/**
	 * Durée de vie des sessions en minutes
	 */
	public static $SESSION_LIFETIME = 4320;
	/**
	 * Clé de stockage du mot de passe en session
	 */
	public static $SESSION_PASSWORD = 'survpwdencr';
	/**
	 * Clé de stockage du login utilisateur en session
	 */
	public static $SESSION_USERNAME = 'username';
	/**
	 * Clé de stockage du token de validation session
	 */
	public static $SESSION_TOKEN = 'survtoken';
	/**
	 * Clé de stockage du token de validation session en cookie
	 */
	public static $COOKIE_TOKEN = 'survuniqid';
	/**
	 * Clé de stockage du token de validation CSRF en session
	 */
	public static $CSRF_TOKEN = 'survcsrf_token';
	/**
	 * Host du serveur dans le cas ou il n'est pas récupérable facilement
	 * [Optionnel] Peut être mis à null
	 */
	public static $HOST = null;
	/**
	 * URL vers la page de login, peut être changée pour l'annuaire fédérateur
	 * [Optionnel] Peut être mis à null
	 */
	public static $LOGIN_URL = null;
	/**
	 * Défini la valeur du champ get pour lui passer une url en redirection
	 * [Optionnel] Peut être mis à null
	 */
	public static $GET_URL = null;
	/**
	 * Liste des types de sondage possible
	 */
	public static $POLL_TYPES = array("date", "prop");
	/**
	 * Configuration du ou des serveurs memcache
	 */
	public static $MEMCACHE_SERVER = array('localhost:11211');

	/**
	 * Défini si l'application propose aux utilisateurs d'ajouter la réponse à leur calendrier.
	 */
	public static $ADD_TO_CALENDAR = true;
	/**
	 * Valeur de header pour le Access-Control-Allow-Origin
	 * Permet d'effectuer une authentification cross domain depuis Roundcube
	 */
	public static $ROUNDCUBE_CORS = "";

	/**
	 * Défini si l'application doit envoyer des mails
	 * @var boolean
	 */
	public static $SEND_MAIL = true;
	/**
	 * Adresse mail utilisée pour envoyer les email de notification aux utilisateurs
	 */
	public static $FROM_MAIL = "";

	/**
	 * Clé DES utilisée pour l'encodage du mot de passe en session
	 * this key is used to encrypt the users imap password which is stored
     * in the session record (and the client cookie if remember password is enabled).
     * please provide a string of exactly 24 chars.
     * YOUR KEY MUST BE DIFFERENT THAN THE SAMPLE VALUE FOR SECURITY REASONS
	 */
	public static $DES_KEY = 'sKt99YRBZ49UNHCpXdOfGYxk';
}