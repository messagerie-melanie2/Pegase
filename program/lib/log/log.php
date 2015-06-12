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
namespace Program\Lib\Log;

/**
 * Classe de log
 * Singleton
 * @package Lib
 * @subpackage Log
 */
class Log {
	/**
	 * Désativation des logs
	 * @var int (binaire)
	 */
	const OFF = 1;
	/**
	 * Log seulement les erreurs fatales
	 * @var int (binaire)
	 */
	const FATAL = 2;
	/**
	 * Log seulement les erreurs
	 * @var int (binaire)
	 */
	const ERROR = 4;
	/**
	 * Log seulement les warnings
	 * @var int (binaire)
	 */
	const WARN = 8;
	/**
	 * Log les informations sur l'execution
	 * @var int (binaire)
	 */
	const INFO = 16;
	/**
	 * Log les informations de debuggage
	 * @var int (binaire)
	 */
	const DEBUG = 32;
	/**
	 * Log tout
	 * @var int (binaire)
	 */
	const ALL = 62;

	/**
	 * Static log class
	 * @var Logging $log
	 */
	private static $log = null;
	/**
	 * Permet de calculer le niveau de configuration du niveau de log
	 * Le calcul est fait ici car impossible de passer des opérateurs dans les définitions de variable de classe
	 * conflevel sert donc à conserver le niveau configurer pour ne pas avoir à recalculer à chaque fois
	 * @var int (binaire)
	 */
	private static $conflevel = 0;
	/**
	 * Fichier de log pour les erreurs
	 */
	private static $errorlog_file = "";
	/**
	 * Fichier de log
	 */
	private static $log_file = "";

	/**
	 * Fonction de log
	 *
	 * @param Log::<LEVEL> $level
	 * @param string $message message to show
	 */
	public static function l($level, $message) {
		if (!isset(self::$log)) self::$log = new Logging();
		// Récupération du niveau de log configuré
		if (self::$conflevel === 0) {
			$globalLevel = explode('|', \Config\Log::$Level);
			// Utilise la reflection pour un accès dynamique à la valeur de la constante de classe
			$r = new \ReflectionClass('\Program\Lib\Log\Log');
			foreach ($globalLevel as $l) self::$conflevel |= $r->getConstant($l);
		}
		// Les logs sont désactivé
		if ((self::$conflevel & self::OFF) === self::OFF) return;
		// Ce niveau de log n'est pas pris en charge
		if ((self::$conflevel & $level) !== $level && (self::$conflevel & self::ALL) !== self::ALL) return;
		$date = @date(\Config\Log::$date_format);
		// Définition du fichier de log (ajout de la date du jour si besoin)
		if (self::$log_file === "") {
			self::$log_file = str_replace("{date}", $date, \Config\Log::$file_log);
		}
		// Définition du fichier de log pour les erreurs (ajout de la date du jour si besoin)
		if (self::$errorlog_file === "") {
			self::$errorlog_file = str_replace("{date}", $date, \Config\Log::$file_errors_log);
		}
		// Récupèration de l'adresse IP
		$addrip = \Program\Lib\Request\Request::get_ip_address();
		// Récupération du process ID
		$procid = getmypid();
		// Ecriture des logs dans le/les fichier(s) en fonction du niveau de log configuré
		if ($level === self::ERROR) {
			// Erreur dans le fichier d'erreur
			self::$log->lfile(self::$errorlog_file);
			self::$log->lwrite("$addrip [$procid] [ERROR] $message");
			self::$log->lclose();
			// Si le fichier log et error_log sont différents, on écrit dans les deux
			if (self::$log_file !== self::$errorlog_file) {
				self::$log->lfile(self::$log_file);
				self::$log->lwrite("$addrip [$procid] [ERROR] $message");
				self::$log->lclose();
			}
		} elseif ($level === self::FATAL) {
			// Fatal dans le fichier d'erreur
			self::$log->lfile(self::$errorlog_file);
			self::$log->lwrite("$addrip [$procid] [FATAL] $message");
			self::$log->lclose();
			// Si le fichier log et error_log sont différents, on écrit dans les deux
			if (self::$log_file !== self::$errorlog_file) {
				self::$log->lfile(self::$log_file);
				self::$log->lwrite("$addrip [$procid] [FATAL] $message");
				self::$log->lclose();
			}
		} else {
			// Pour tous les autres niveaux de logs on écrit dans le fichier log
			self::$log->lfile(self::$log_file);
			if ($level === self::DEBUG) self::$log->lwrite("$addrip [$procid] [DEBUG] $message");
			elseif ($level === self::INFO) self::$log->lwrite("$addrip [$procid] [INFO] $message");
			elseif ($level === self::WARN) self::$log->lwrite("$addrip [$procid] [WARN] $message");
			self::$log->lclose();
		}
	}
}