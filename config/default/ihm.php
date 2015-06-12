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
     * Configuration de l'overlay pour la localization
     */
    public static $OVERLAY_LOCALIZATION = null;
    /**
     * Configuration du nom de la skin à utiliser pour l'ihm
     */
    public static $SKIN = 'default';
    /**
     * Configuration du nom de l'application
     */
    public static $TITLE = 'Pegase';

    /**
     * Backend à utiliser pour l'authentification
     * 'ldap'
     */
    public static $AUTHENTICATE_BACKEND = 'ldap';

    /**
     * Permettre la création de l'utilisateur depuis l'interface
     * Le driver doit prendre en compte la création de l'utilisateur avec un mot de passe crypté
     */
    public static $CREATE_USER = false;

    /**
     * Nombre de réponses nécessaires avant l'affichage du pop de verrouillage
     *
     * @var int
     */
    public static $POPUP_NB_RESPONSES = 2;
    /**
     * Temps entre la création du sondage et l'affichage du pop up pour le verrouillage
     * En secondes
     *
     * @var int
     */
    public static $POPUP_TIME_CREATED = 3600;

    /**
     * Page d'accueil
     * Nombre de sondages personnels affichés
     * Au delà ils sont masqués dynamiquement
     */
    public static $MAX_SHOW_OWN_POLLS = 5;
    /**
     * Page d'accueil
     * Nombre de sondages auxquels on a répondu affichés
     * Au delà ils sont masqués dynamiquement
     */
    public static $MAX_SHOW_RESP_POLLS = 5;

    /**
     * Configuration du timezone par defaut pour l'application
     */
    public static $DEFAULT_TIMEZONE = 'Europe/Paris';
    /**
   	 * Type de stockage pour les sessions, 'php', 'memcache' ou 'db'
     */
    public static $SESSION_TYPE = 'db';
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
     * Affichage du bouton d'aide sur l'application dans les pages
     * L'aide se trouve dans le dossier help/<localization>/
     * @var boolean
     */
    public static $SHOW_HELP_BUTTON = false;
    
    /**
     * Mapping entre les pages de l'application et les pages d'aide
     * 'default' => Pour une page par défaut
     * @var array
     */
    public static $HELP_PAGES_MAPPING = [];
    
    /**
     * Utiliser un SSO pour se connecter à Pégase
     * Dans ce cas la page de login est limitée
     *
     * @var boolean
    */
    public static $USE_SSO = false;

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
    public static $MEMCACHE_SERVER = array();

    /**
	 * URL de disponibilité des utilisateurs
	 * Peut être configuré dans le ldap ou ici (la valeur du ldap prend le dessus)
	 *
	 * Paramètres possible: %%username%% / %%email%% / %%start%% / %%end%%
	 *
	 * @var string
	 */
	public static $FREEBUSY_URL = "https://melanie2web.melanie2.i2/kronolith/fb.php?u=%%username%%";
  //public static $FREEBUSY_URL = "";
  /**
   * Défini si les tentatives supprimés par l'organisateur (validation d'une date, suppression du sondage)
   * sont répercutées pour tous les participants
   *
   * @var boolean
   */
  public static $ORGANIZER_DELETE_TENTATIVES_ATTENDEES = true;

  /**
   * Est-ce que l'application doit baser les disponibilités en fonction des réponses aux autres sondages
   * Si un sondage est en cours et que l'utilisateur a répondu, il le verra s'afficher dans ses disponibilités
   * @var boolean
   */
  public static $SHOW_OTHERS_POLLS_FREEBUSY = true;

  /**
   * Mode utilisé pour l'autocomplétion
   * 1: abc*
   * 2: *abc*
   * 3: abc
   */
  public static $AUTOCOMPLETE_MODE = 1;
  /**
   * Champs sur lesquels l'autocomplétion se fait
   * Possible 'username', 'fullname', 'mail'
   */
  public static $AUTOCOMPLETE_FIELDS = ['fullname'];
  /**
   * Nombre de resultats maximum retournés par l'autocomplétion
   */
  public static $AUTOCOMPLETE_SIZE = 5;
  /**
   * Backends utilisés pour l'autocomplétion
   * ldap et/ou driver
   */
  public static $AUTOCOMPLETE_BACKENDS = ['ldap','driver'];

    /**
     * Défini si l'application doit envoyer des mails
     * @var boolean
     */
    public static $SEND_MAIL = false;
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