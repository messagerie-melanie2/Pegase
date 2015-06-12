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
namespace Program\Lib\Request;

/**
 * Classe de gestion de la sortie HTML pour l'application de sondage
 *
 * @package Lib
 * @subpackage Request
 */
class Output {
	const JS_OBJECT_NAME = "poll";
	protected static $charset = POLL_CHARSET;
	protected static $js_labels = array ();
	protected static $env = array ();
	protected static $js_env = array ();
	protected static $scripts = "";
	protected static $skin;

	/**
	 * Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() {
	}

	/**
	 * Initialisation des variables de sortie
	 */
	public static function init() {
		// Définition du timezone
		date_default_timezone_set ( \Config\IHM::$DEFAULT_TIMEZONE );
		// Définition de la skin
		self::$skin = \Config\IHM::$SKIN;
		// Test si une skin est passée en GET
		if (isset ( $_GET ['_skin'] ) && file_exists ( __DIR__ . "/../../../skins/" . Request::getInputValue ( "_skin", POLL_INPUT_GET ) )) {
			self::$skin = Request::getInputValue ( "_skin", POLL_INPUT_GET );
			self::set_env ( "mobile", false );
		}		// Test si on passe en skin courrielleur
		elseif (Request::isCourrielleur ()) {
			self::$skin = 'courrielleur';
		}		// Test si on passe en skin mobile
		elseif (Request::isMobile () && file_exists ( __DIR__ . "/../../../skins/" . \Config\IHM::$SKIN . "_mobile" ) && ! Cookie::issetCookie ( "desktop_version" ) || Cookie::issetCookie ( "mobile_version" )) {
			self::$skin = \Config\IHM::$SKIN . "_mobile";
			// Type d'affichage de la page mobile
			self::set_env ( "mobile", true );
		} else {
			// Type d'affichage de la page desktop
			self::set_env ( "mobile", false );
		}
		// Chargement des données en fonction des informations de la page
		self::set_env ( "page", Request::getInputValue ( "_p", POLL_INPUT_GET ) );
		// Chargement de l'uid du sondage
		$poll_uid = Request::getInputValue ( "_u", POLL_INPUT_GET );
		if (empty ( $poll_uid ))
			$poll_uid = Request::getInputValue ( "poll_uid", POLL_INPUT_GPC );
		self::set_env ( "poll_uid", $poll_uid );
		// Chargement de l'action
		self::set_env ( "action", Request::getInputValue ( "_a", POLL_INPUT_GET ) );
		// Chargement de la skin en environnement
		self::set_env ( "skin", self::$skin );
		// Chargement de la localization en environnement
		self::set_env ( "localization", \Config\IHM::$DEFAULT_LOCALIZATION );
		// Mapping des pages d'aides
		if (\Config\IHM::$SHOW_HELP_BUTTON) {
		  self::set_env ( "help_pages_mapping", \Config\IHM::$HELP_PAGES_MAPPING );
		}
		// Chargement des headers
		self::nocacheing_headers ();
		// add common javascripts
		self::add_script ( 'var ' . self::JS_OBJECT_NAME . ' = new poll_app();' );

		// Initialisation de la session
		if (\Config\IHM::$SESSION_TYPE == 'memcache') {
			session_set_save_handler ( new \Program\Lib\Request\Session_Memcache () );
		}
		elseif (\Config\IHM::$SESSION_TYPE == 'db') {
		  session_set_save_handler ( new \Program\Lib\Request\Session_Database() );
		}
		else {
		  session_set_cookie_params(\Config\IHM::$SESSION_LIFETIME * 60);
		}
		// turn on output buffering
		ob_start ();

		// demarrage de la session
		session_start ();
	}
	/**
	 * Envoie de la sortie au client
	 *
	 * @param
	 *        	boolean True if script should terminate (default)
	 */
	public static function send($exit = true) {
		// set output asap
		ob_flush ();
		flush ();

		if ($exit) {
			exit ();
		}
	}

	/**
	 * Send HTTP headers to prevent caching a page
	 */
	public static function nocacheing_headers() {
		if (headers_sent ()) {
			return;
		}
		header ( "Expires: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );

		// Request browser to disable DNS prefetching (CVE-2010-0464)
		header ( "X-DNS-Prefetch-Control: off" );
		header ( "Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0" );
		header ( "Pragma: no-cache" );
		// Gestion de l'authentification cross domain depuis Roundcube
		if (isset ( \Config\IHM::$ROUNDCUBE_CORS )) {
			if (is_array ( \Config\IHM::$ROUNDCUBE_CORS )) {
				if (isset ( $_SERVER ['HTTP_ORIGIN'] ) && in_array ( $_SERVER ['HTTP_ORIGIN'], \Config\IHM::$ROUNDCUBE_CORS )) {
					header ( "Access-Control-Allow-Origin: " . $_SERVER ['HTTP_ORIGIN'] );
					header ( "Access-Control-Allow-Credentials: true" );
					header ( "Access-Control-Allow-Headers: X-Roundcube-Request" );
				}
			} else {
				header ( "Access-Control-Allow-Origin: " . \Config\IHM::$ROUNDCUBE_CORS );
				header ( "Access-Control-Allow-Credentials: true" );
				header ( "Access-Control-Allow-Headers: X-Roundcube-Request" );
			}
		}
	}

	/**
	 * Génération du head du script
	 *
	 * @return string
	 */
	public static function head() {
		// Parcourir les variables d'environnement
		if (count ( self::$js_env ) > 0)
			self::add_script ( self::JS_OBJECT_NAME . '.set_env(' . self::json_serialize ( self::$js_env ) . ");" );
			// Parcourir les variables d'environnement
		if (count ( self::$js_labels ) > 0)
			self::add_script ( self::JS_OBJECT_NAME . '.add_label(' . self::json_serialize ( self::$js_labels ) . ");" );
			// Parcourir les scripts js
		return "<script type=\"text/javascript\">" . self::$scripts . "</script>";
	}

	/**
	 * Add inline javascript code
	 *
	 * @param
	 *        	string JS code snippet
	 */
	public static function add_script($script) {
		if (self::$scripts != "")
			self::$scripts .= "\n";
		self::$scripts .= rtrim ( $script );
	}

	/**
	 * Retourne le message dans le bon encodage HTML
	 *
	 * @param string $message
	 * @return string
	 */
	public static function tohtml($message) {
		return nl2br ( htmlentities ( $message, ENT_QUOTES, "UTF-8" ) );
	}
	/**
	 * Retourne la date formaté suivante la localization pour du texte
	 *
	 * @param string $time
	 * @return string
	 */
	public static function date_format($time) {
		$label = Localization::g ( "date_label" );
		$month = Localization::g ( date ( "F", $time ) );
		$day = Localization::g ( date ( "l", $time ) );
		$d = date ( "d", $time );
		$year = date ( "Y", $time );
		$hour = date ( "H", $time );
		$minute = date ( "i", $time );
		$label = str_replace ( '%%l', $day, $label );
		$label = str_replace ( '%%F', $month, $label );
		$label = str_replace ( '%%d', $d, $label );
		$label = str_replace ( '%%Y', $year, $label );
		$label = str_replace ( '%%H', $hour, $label );
		$label = str_replace ( '%%i', $minute, $label );
		return $label;
	}

	/**
	 * Récupération du titre de la page courante
	 *
	 * @return string
	 */
	public static function get_title() {
		$page = self::getInputValue ( "_p", POLL_INPUT_GET );
		if (isset ( $page )) {
			$title = Localization::g ( "title $page" );
		} else {
			$title = Localization::g ( "title main" );
		}
		return \Config\IHM::$TITLE . ' :: ' . $title;
	}

	/**
	 * Génération de l'url vers la page données avec une action possible
	 *
	 * @param string $page
	 *        	[optionnal]
	 * @param string $action
	 *        	[optionnal]
	 * @param array $options
	 *        	[optionnal]
	 * @param boolean $escape_amp
	 *        	[optionnal]
	 */
	public static function url($page = null, $action = null, $options = array(), $escape_amp = true) {
		$url = "";
		// Définition de l'amp
		$amp = '&';
		if ($escape_amp)
			$amp = \Program\Lib\HTML\HTML::quote ( $amp );
			// Gestion du login
		if ($page == "login" && isset ( \Config\IHM::$LOGIN_URL ) && isset ( $options ['url'] )) {
			if (strpos ( \Config\IHM::$LOGIN_URL, "?" ) !== false)
				$url = \Config\IHM::$LOGIN_URL . "$amp" . \Config\IHM::$GET_URL . "=" . $options ['url'];
			else
				$url = \Config\IHM::$LOGIN_URL . "?" . \Config\IHM::$GET_URL . "=" . $options ['url'];
		} else {
			// Gestion de la page
			if (isset ( $page )) {
				$page = strtolower ( $page );
				$url = "?_p=$page";
			}
			// Gestion de l'action
			if (isset ( $action )) {
				if ($url == "")
					$url = "?";
				else
					$url .= "$amp";
				$action = strtolower ( $action );
				$url .= "_a=$action";
			}
			// Gestion des options GET
			if (isset ( $options ) && is_array ( $options )) {
				foreach ( $options as $key => $value ) {
					if (isset ( $value )) {
						if ($url == "")
							$url = "?";
						else
							$url .= "$amp";
						$key = strtolower ( $key );
						$url .= "_$key=$value";
					}
				}
			}
			// Si l'url est passé on la conserve
			$_url = Request::getInputValue ( "_url", POLL_INPUT_GET );
			if (! empty ( $_url ) && ! isset ( $options ['url'] )) {
				if ($url == "")
					$url = "?";
				else
					$url .= "$amp";
				$url .= "_url=$_url";
			}
			// Si le poll est passé on la conserve
			$_poll = Request::getInputValue ( "_poll", POLL_INPUT_GET );
			if (! empty ( $_poll )) {
				if ($url == "")
					$url = "?";
				else
					$url .= "$amp";
				$url .= "_poll=$_poll";
			}
			// Si les params sont passés on les conserve
			$_params = Request::getInputValue ( "_params", POLL_INPUT_GET );
			if (! empty ( $_params )) {
			  if ($url == "")
			    $url = "?";
			  else
			    $url .= "$amp";
			  $url .= "_params=".urlencode($_params);
			}
			// Si l'url est passé on la conserve
			$_skin = Request::getInputValue ( "_skin", POLL_INPUT_GET );
			if (! empty ( $_skin )) {
				if ($url == "")
					$url = "?";
				else
					$url .= "$amp";
				$url .= "_skin=$_skin";
			}
		}
		// Retourne l'url générée
		return $url;
	}
	/**
	 * Retourne la commande javascript lié aux paramètres passés
	 *
	 * @param string $command
	 *        	Commande à appeler
	 * @param string $url
	 *        	URL vers la requête à appeler
	 * @param string $action
	 *        	action de la commande
	 * @param array $params
	 *        	paramètres de la commande
	 * @return string
	 */
	public static function command($command, $url = "", $action = "", $params = array()) {
		return "poll.command($command, {url: '$url', action: '$action', params: " . json_encode ( $params ) . "});";
	}

	/**
	 * Add a localized label to the client environment
	 */
	public static function add_label() {
		$args = func_get_args ();
		if (count ( $args ) == 1 && is_array ( $args [0] ))
			$args = $args [0];

		foreach ( $args as $name ) {
			self::$js_labels [$name] = Localization::g ( $name, false );
		}
	}

	/**
	 * Set environment variable
	 *
	 * @param
	 *        	string Property name
	 * @param
	 *        	mixed Property value
	 * @param
	 *        	boolean True if this property should be added to client environment
	 */
	public static function set_env($name, $value, $addtojs = true) {
		self::$env [$name] = $value;
		if ($addtojs || isset ( self::$js_env [$name] )) {
			self::$js_env [$name] = $value;
		}
	}
	/**
	 * Environment variable getter.
	 *
	 * @param string $name
	 *        	Property name
	 *
	 * @return mixed Property value
	 */
	public static function get_env($name) {
		if (! isset ( self::$env [$name] ))
			return null;
		return self::$env [$name];
	}
	/**
	 * Environment variable isset.
	 *
	 * @param string $name
	 *        	Property name
	 *
	 * @return bool
	 */
	public static function isset_env($name) {
		return isset ( self::$env [$name] );
	}
	/**
	 * Retourne l'url absolue vers le poll courant
	 *
	 * @return string
	 */
	public static function get_poll_url(\Program\Data\Poll $poll = null) {
		$url = "";
		if (isset ( $poll )) {
			$url = explode ( '?', Request::getCurrentURL () );
			$url = $url [0] . "?_u=" . $poll->poll_uid;
		} else if (\Program\Data\Poll::isset_current_poll ()) {
			$url = explode ( '?', Request::getCurrentURL () );
			$url = $url [0] . "?_u=" . \Program\Data\Poll::get_current_poll ()->poll_uid;
		}
		return $url;
	}

	/**
	 * Retourne l'url absolue vers l'application
	 *
	 *
	 * @return string
	 */
	public static function get_main_url() {
		$url = "";
		if (isset ( $poll )) {
			$url = explode ( '?', Request::getCurrentURL () );
			$url = $url [0];
		} else if (\Program\Data\Poll::isset_current_poll ()) {
			$url = explode ( '?', Request::getCurrentURL () );
			$url = $url [0];
		}
		return $url;
	}

	/**
	 * Retourne l'url vers la suppression des événements provisoires pour un sondage
	 * @param \Program\Data\Poll $poll
	 * @return string
	 */
	public static function get_delete_tentatives_poll_url(\Program\Data\Poll $poll = null) {
		$url = self::get_poll_url($poll);
		$url .= "&_a=".ACT_DELETE_TENTATIVES;
		return $url;
	}

	/**
	 * Retourne l'url pour l'ajout de l'événement à l'agenda
	 * @param \Program\Data\Poll $poll
	 * @param string $prop_key
	 * @param string $part_status
	 * @return string
	 */
	public static function get_add_calendar_url(\Program\Data\Poll $poll = null, $prop_key = null, $part_status = null) {
		$url = self::get_poll_url($poll);
		$url .= "&_a=".ACT_ADD_CALENDAR;
		if (isset($prop_key)) {
			$url .= "&_prop_key=$prop_key";
		}
		if (isset($part_status)) {
			$url .= "&_part_status=$part_status";
		}
		$url .= "&_s=mail";
		return $url;
	}

	/**
	 * Retourne l'url pour le téléchargement du fichier iCalendar
	 * @param \Program\Data\Poll $poll
	 * @param string $prop_key
	 * @return string
	 */
	public static function get_download_ics_url(\Program\Data\Poll $poll = null, $prop_key = null) {
	  $url = self::get_poll_url($poll);
	  $url .= "&_a=".ACT_DOWNLOAD_ICS;
	  if (isset($prop_key)) {
	    $url .= "&_prop=$prop_key";
	  }
	  $url .= "&_s=mail";
	  return $url;
	}

	/**
	 * Format la proposition de date en quelque de plus lisible
	 * @param \Program\Data\Poll $poll sondage lié à la proposition
	 * @param string $prop_value proposition à formatter
	 * @return string
	 */
	public static function format_prop_poll(\Program\Data\Poll $poll, $prop_value) {
		if ($poll->type == "date") {
			$values = explode ( ' - ', $prop_value );
			$time = strtotime ( $values [0] );
			$month = Localization::g ( date ( "F", $time ) );
			$day = Localization::g ( date ( "l", $time ) );
			$d = date ( "d", $time );
			$year = date ( "Y", $time );
			$hour = date ( "H", $time );
			$minute = date ( "i", $time );
			$prop = "$day $d $month $year";
			if (strlen ( $values [0] ) != 10)
				$prop .= " - " . $hour . "h" . $minute;

			if (isset($values [1])) {
				$time = strtotime ( $values [1] );
				$hour = date ( "H", $time );
				$minute = date ( "i", $time );
				$prop .= "-" . $hour . "h" . $minute;
			}
		}
		else {
			$prop = $prop_value;
		}

		return $prop;
	}

	/**
	 * Convert a variable into a javascript object notation
	 *
	 * @param
	 *        	mixed Input value
	 *
	 * @return string Serialized JSON string
	 */
	public static function json_serialize($input) {
		$input = rcube_charset::clean ( $input );

		// sometimes even using rcube_charset::clean() the input contains invalid UTF-8 sequences
		// that's why we have @ here
		return @json_encode ( $input );
	}
}