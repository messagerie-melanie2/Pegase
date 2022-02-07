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
class IHM
{
  /**
   * Configuration de la localization par défaut
   */
  public static $DEFAULT_LOCALIZATION = "fr_FR";
  /**
   * Configuration de l'overlay pour la localization
   */
  public static $OVERLAY_LOCALIZATION = null;

  /**
   * Doit-on afficher la page de maintenance ?
   */
  public static $MAINTENANCE = false;

  /**
   * Configuration du nom de la skin à utiliser pour l'ihm
   */
  public static $SKIN = 'default';
  /**
   * Configuration du nom de l'application
   */
  public static $TITLE = 'Pegase';
  /**
   * Permettre la création de l'utilisateur depuis l'interface
   * Le driver doit prendre en compte la création de l'utilisateur avec un mot de passe crypté
   */
  public static $CREATE_USER = false;

  /**
   * Page d'accueil
   * Nombre de sondages personnels affichés
   * Au delà ils sont masqués dynamiquement
   */
  public static $MAX_SHOW_OWN_POLLS = 3;
  /**
   * Page d'accueil
   * Nombre de sondages auxquels on a répondu affichés
   * Au delà ils sont masqués dynamiquement
   */
  public static $MAX_SHOW_RESP_POLLS = 5;
  /**
   * Page d'accueil
   * Nombre de sondages supprimés affichés
   * Au delà ils sont masqués dynamiquement
   */
  public static $MAX_SHOW_DELETED_POLLS = 1;

  /**
   * Nombre de réponses nécessaires avant l'affichage du pop de verrouillage
   * @var int
   */
  public static $POPUP_NB_RESPONSES = 2;
  /**
   * Temps entre la création du sondage et l'affichage du pop up pour le verrouillage
   * En secondes
   * @var int
   */
  public static $POPUP_TIME_CREATED = 60;
  /**
   * Configuration du timezone par defaut pour l'application
   */
  public static $DEFAULT_TIMEZONE = 'Europe/Paris';
  /**
   * Type de stockage pour les sessions, 'php' ou 'memcache'
   */
  public static $SESSION_TYPE = 'php';
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
   * Utiliser un SSO pour se connecter à Pégase
   * Dans ce cas la page de login est limitée
   * @var boolean
   */
  public static $USE_SSO = false;
  /**
   * Proposer une connexion via Cerbere dans la base de login
   * @var string
   */
  public static $USE_CERBERE = false;
  /**
   * Défini la valeur du champ get pour lui passer une url en redirection
   * [Optionnel] Peut être mis à null
   */
  public static $GET_URL = null;
  /**
   * Liste des types de sondage possible
   */
  public static $POLL_TYPES = array(
    "date",
    "rdv",
    "prop"
  );
  /**
   * Liste des champs pour les sondages
   */
  public static $ALL_FIELDS = array(
    "edit_only_auth_user",
    "edit_if_needed",
    "edit_prop_in_agenda",
    "edit_anonymous",
    "edit_max_attendees_per_prop"
  );
  /**
   * Liste des champs à afficher par types de sondages
   */
  public static $SHOW_FIELDS = array(
    "date" => array(
      "edit_only_auth_user",
      "edit_if_needed",
      "edit_anonymous"
    ),
    "rdv" => array(
      "edit_only_auth_user",
      "edit_prop_in_agenda",
      "edit_anonymous",
      "edit_max_attendees_per_prop"
    ),
    "prop" => array(
      "edit_only_auth_user",
      "edit_if_needed",
      "edit_anonymous"
    ),
  );
  /**
   * Liste des champs à cocher par types de sondages
   */
  public static $CHECK_FIELDS = array(
    "date" => array(),
    "rdv" => array(
      "edit_prop_in_agenda",
    ),
    "prop" => array(),
  );
  /**
   * Liste des champs required par types de sondages
   */
  public static $REQUIRED_FIELDS = array(
    "rdv" => array(
      "edit_max_attendees_per_prop",
    )
  );
  /**
   * Liste des champs non required par types de sondages
   */
  public static $NOT_REQUIRED_FIELDS = array(
    "date" => array("edit_max_attendees_per_prop"),
    "prop" => array("edit_max_attendees_per_prop")
  );
  /**
   * Configuration du ou des serveurs memcache
   */
  public static $MEMCACHE_SERVER = array();

  /**
   * URL de disponibilité des utilisateurs
   * Peut être configuré dans le ldap ou ici (la valeur du ldap prend le dessus)
   *
   * Paramètres possible: %%username%% / %%email%%
   *
   * @var string
   */
  public static $FREEBUSY_URL = "https://melanie2web.melanie2.i2/kronolith/fb.php?u=%%username%%";


  /**
   * Défini si les tentatives supprimés par l'organisateur (validation d'une date, suppression du sondage)
   * sont répercutées pour tous les participants
   * @var boolean
   */
  public static $ORGANIZER_DELETE_TENTATIVES_ATTENDEES = true;

  /**
   * Défini si l'application propose aux utilisateurs d'ajouter la réponse à leur calendrier.
   */
  public static $ADD_TO_CALENDAR = false;
  /**
   * Valeur de header pour le Access-Control-Allow-Origin
   * Permet d'effectuer une authentification cross domain depuis Roundcube
   */
  public static $ROUNDCUBE_CORS = "";

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
