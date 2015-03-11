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
namespace Program\Lib\Backend\Ldap;

// Utilisation des namespaces
use \Program\Lib\Log\Log as Log;

/**
 * Gestion de la connexion LDAP
 * 
 * @package Program
 * @subpackage Backend
 */
class Ldap {
    /**
     * Instances LDAP
     * @var Ldap
     */
    private static $instances = array();
	/**
	 * Connexion vers le serveur LDAP
	 * @var resource
	 */
	private $connection = null;
	/**
	 * Configuration de connexion
	 * @var array
	 */
	private $config = array();
	/**
	 * Utilisateur connecté
	 * @var string
	 */
	private $username = null;
	/**
	 * Stockage des données retournées en cache
	 * @var array
	 */
	private $cache = array();
	/**
	 * Permet de savoir si on est en connexion anonyme
	 * @var bool
	 */
	private $isAnonymous = false;
	
	/************** SINGLETON ***/
	/**
	 * Récupèration de l'instance lié au serveur
	 * @param string $server Nom du serveur, l'instance sera liée à ce nom qui correspond à la configuration du serveur
	 * @return Ldap
	 */
	public static function GetInstance($server) {
	    Log::l(Log::DEBUG, "Ldap::GetInstance($server)");
	    if (!isset(self::$instances[$server])) {
	        if (!isset(\Config\Ldap::$SERVERS[$server])) {
	            Log::l(Log::ERROR, "Ldap->GetInstance() Erreur la configuration du serveur '$server' n'existe pas");
	            return false;
	        }
            self::$instances[$server] = new self(\Config\Ldap::$SERVERS[$server]);
	    }
	    return self::$instances[$server];
	}
	
	/*** Constructeurs **/
	/**
	 * Constructeur par défaut
	 * @param string $config
	 */
	public function __construct($config) {
		// Assigner la configuration
		$this->config = $config;
		// Lancer la connexion au LDAP
		if (is_null($this->connection)) $this->connect();
	}
	
	/**
	 * Destructeur par défaut : appel à disconnect
	 */
	function __destruct() {
		$this->disconnect();
	}
	
	/****************** Authentification ****/
	/**
	 * Authentification sur le serveur LDAP
	 *
	 * @param string $dn
	 * @param string $password
	 * @return boolean
	 */
	public function authenticate($dn, $password) {
	    Log::l(Log::DEBUG, "Ldap->authentification($dn)");
	    if (is_null($this->connection)) $this->connect();
	
	    // Authentification sur le seveur LDAP
	    if (isset($this->config['tls'])
	            && $this->config['tls']) ldap_start_tls($this->connection);
	    $this->isAnonymous = false;
	    return @ldap_bind($this->connection, $dn, $password);
	}
	
	/**
	 * Se connecte en faisant un bind anonyme sur la connexion LDAP
	 *
	 * @return boolean
	 */
	public function anonymous() {
	    Log::l(Log::DEBUG, "Ldap->anonymous()");
	    if (is_null($this->connection)) $this->connect();
	    if ($this->isAnonymous) return $this->isAnonymous;
	
	    // Authentification sur le seveur LDAP
	    if (isset($this->config['tls'])
	            && $this->config['tls']) ldap_start_tls($this->connection);
	    $this->isAnonymous = @ldap_bind($this->connection);
	    return $this->isAnonymous;
	}
	
	/*************** Statics methods ***/
	/**
	 * Authentification sur le serveur LDAP associé
	 * @param string $username
	 * @param string $password
	 */
	public static function Authentification($username, $password) {
	    Log::l(Log::DEBUG, "Ldap::Authentification($username)");
	    // Récupération de l'instance LDAP en fonction du serveur
	    $ldap = self::GetInstance(\Config\Ldap::$AUTH_LDAP);
	    // Définition de la clé de cache
	    $keycache = "Authentification:$username";
	    // Récupération des données en cache
	    $infos = $ldap->getCache($keycache);
	    if (isset($infos)) {
	        $dn = $infos['dn'];
	    } else {
	        // Connexion anonymous pour lire les données
	        $ldap->anonymous();
	        if (!isset($filter)) {
	            // Génération du filtre
	            $filter = $ldap->getConfig("authentification_filter");
	            if (isset($filter)) {
	                $filter = str_replace('%%username%%', $username, $filter);
	            } else {
	                $filter = "(uid=$username)";
	            }
	            // Lancement de la recherche
	            $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, array('dn'), 0, 1);
	            if ($sr && $ldap->count_entries($sr) == 1) {
	                $infos = $ldap->get_entries($sr);
	                $dn = $infos[0]['dn'];
	            } else {
	                return false;
	            }
	        }
	    }
	    // Authentification
	    return $ldap->authenticate($dn, $password);
	}
	/**
	 * Retourne les données sur l'utilisateur lues depuis le Ldap
	 * @param string $username
	 * @return array
	 */
	public static function GetUserInfos($username, $filter = null) {
	    Log::l(Log::DEBUG, "Ldap::GetUserInfos($username)");
	    // Récupération de l'instance LDAP en fonction du serveur
	    $ldap = self::GetInstance(\Config\Ldap::$SEARCH_LDAP);
	    // Définition de la clé de cache
	    $keycache = "GetUserInfos:$username";
	    // Récupération des données en cache
	    $infos = $ldap->getCache($keycache);
	    if (!isset($infos)) {
	        // Connexion anonymous pour lire les données
	        $ldap->anonymous();
	        if (!isset($filter)) {
	            // Génération du filtre
	            $filter = $ldap->getConfig("get_user_infos_filter");
	            if (isset($filter)) {
	                $filter = str_replace('%%username%%', $username, $filter);
	            } else {
	                $filter = "(uid=$username)";
	            }
	        }
	        // Lancement de la recherche
	        $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, $ldap->getConfig("get_user_infos_attributes"), 0, 1);
	        if ($sr && $ldap->count_entries($sr) == 1) {
	            $infos = $ldap->get_entries($sr);
	            $infos = $infos[0];
	            $ldap->setCache($keycache, $infos);
	        } else {
	            $ldap->deleteCache($keycache);
	        }
	    }
	    // Retourne les données, null si vide
	    return $infos;
	}
	
	/**
	 * Return les boites partagées accessible pour un utilisateur depuis le LDAP
	 * @param string $username
	 * @return mixed cn mineqmelmailemission uid
	 */
	public static function GetUserBalPartagees($username) {
	    Log::l(Log::DEBUG, "Ldap::GetUserBalPartagees($username)");
	    // Récupération de l'instance LDAP en fonction du serveur
	    $ldap = self::GetInstance(\Config\Ldap::$SEARCH_LDAP);
	    // Définition de la clé de cache
	    $keycache = "GetUserBalPartagees:$username";
	    // Récupération des données en cache
	    $infos = $ldap->getCache($keycache);
	    if (!isset($infos)) {
	        // Connexion anonymous pour lire les données
	        $ldap->anonymous();
	        if (!isset($filter)) {
	            // Génération du filtre
	            $filter = $ldap->getConfig("get_user_bal_partagees_filter");
	            if (isset($filter)) {
	                $filter = str_replace('%%username%%', $username, $filter);
	            } else {
	                $filter = "(uid=$username.-.*)";
	            }
	        }
	        // Lancement de la recherche
	        $sr = $ldap->search($ldap->getConfig("shared_base_dn"), $filter, $ldap->getConfig("get_user_bal_partagees_attributes"));
	        if ($sr && $ldap->count_entries($sr) > 0) {
	            $infos = $ldap->get_entries($sr);
	            $ldap->setCache($keycache, $infos);
	        } else {
	            $ldap->deleteCache($keycache);
	        }
	    }
	    // Retourne les données, null si vide
	    return $infos;
	}
	
	/**
	 * Return les boites partagées accessible pour un utilisateur depuis le LDAP
	 * @param string $username
	 * @return mixed cn mineqmelmailemission uid
	 */
	public static function GetUserBalEmission($username) {
	    Log::l(Log::DEBUG, "Ldap::GetUserBalEmission($username)");
	    // Récupération de l'instance LDAP en fonction du serveur
	    $ldap = self::GetInstance(\Config\Ldap::$SEARCH_LDAP);
	    // Définition de la clé de cache
	    $keycache = "GetUserBalEmission:$username";
	    // Récupération des données en cache
	    $infos = $ldap->getCache($keycache);
	    if (!isset($infos)) {
	        // Connexion anonymous pour lire les données
	        $ldap->anonymous();
	        if (!isset($filter)) {
	            // Génération du filtre
	            $filter = $ldap->getConfig("get_user_bal_emission_filter");
	            if (isset($filter)) {
	                $filter = str_replace('%%username%%', $username, $filter);
	            } else {
	                $filter = "(|(mineqmelpartages=$username:C)(mineqmelpartages=$username:G))";
	            }
	        }
	        // Lancement de la recherche
	        $sr = $ldap->search($ldap->getConfig("shared_base_dn"), $filter, $ldap->getConfig("get_user_bal_emission_attributes"));
	        if ($sr && $ldap->count_entries($sr) > 0) {
	            $infos = $ldap->get_entries($sr);
	            $ldap->setCache($keycache, $infos);
	        } else {
	            $ldap->deleteCache($keycache);
	        }
	    }
	    // Retourne les données, null si vide
	    return $infos;
	}
	
	/**
	 * Return les informations sur un utilisateur depuis son adresse email depuis le LDAP
	 * @param string $email
	 * @return mixed dn cn uid
	 */
	public static function GetUserInfosFromEmail($email) {
	    Log::l(Log::DEBUG, "Ldap::GetUserInfosFromEmail($email)");
	    // Récupération de l'instance LDAP en fonction du serveur
	    $ldap = self::GetInstance(\Config\Ldap::$SEARCH_LDAP);
	    // Définition de la clé de cache
	    $keycache = "GetUserInfosFromEmail:$email";
	    // Récupération des données en cache
	    $infos = $ldap->getCache($keycache);
	    if (!isset($infos)) {
	        // Connexion anonymous pour lire les données
	        $ldap->anonymous();
	        if (!isset($filter)) {
	            // Génération du filtre
	            $filter = $ldap->getConfig("get_user_infos_from_email_filter");
	            if (isset($filter)) {
	                $filter = str_replace('%%email%%', $email, $filter);
	            } else {
	                $filter = "(mineqmelmailemission=$email)";
	            }
	        }
	        // Lancement de la recherche
	        $sr = $ldap->search($ldap->getConfig("base_dn"), $filter, $ldap->getConfig("get_user_infos_from_email_attributes"), 0, 1);
	        if ($sr && $ldap->count_entries($sr) == 1) {
	            $infos = $ldap->get_entries($sr);
	            $infos = $infos[0];
	            $ldap->setCache($keycache, $infos);
	        } else {
	            $ldap->deleteCache($keycache);
	        }
	    }
	    // Retourne les données, null si vide
	    return $infos;
	}
	
	/**************** Cache store ******/
	/**
	 * Mise en cache des données
	 * @param string $key
	 * @param \multitype $value
	 */
	public function setCache($key, $value) {
	    // Création du stockage en cache
	    if (!is_array($this->cache)) $this->cache = array();
	    // Stockage en cache de la donnée
	    $this->cache[$key] = $value; 
	}
	/**
	 * Récupération des données depuis le cache
	 * @param string $key
	 * @return \multitype:
	 */
	public function getCache($key) {
	    // test si les données existes
	    if (!isset($this->cache[$key])) return null;
	    // Retourne les données du cache
	    return $this->cache[$key];
	}
	/**
	 * Suppression de la donnée en cache
	 * @param string $key
	 */
	public function deleteCache($key) {
	    // Delete les données du cache
	    unset($this->cache[$key]);
	}
	
	/****************** Generic LDAP Methods ****/
	/**
	 * Connection au serveur LDAP
	 */
	public function connect() {
	    Log::l(Log::DEBUG, "Ldap->connect()");
	    $this->connection = @ldap_connect($this->config['hostname'], isset($this->config['port']) ? $this->config['port'] : '389');
	    ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
	    if (isset($this->config['version'])) @ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->config['version']);
	    $this->isAnonymous = false;
	}
	/**
	 * Deconnection du serveur LDAP
	 * @return boolean
	 */
	public function disconnect() {
	   Log::l(Log::DEBUG, "Ldap->disconnect()");
	    $ret = @ldap_unbind($this->connection);
	    $this->connection = null;
	    $this->isAnonymous = false;
	    return $ret;
	}
	/**
	 * Recherche dans le LDAP
	 * @param string $base_dn Base DN de recherche
	 * @param string $filter Filtre de recherche
	 * @param array $attributes Attributs à rechercher
	 * @param int $attrsonly Doit être défini à 1 si seuls les types des attributs sont demandés. S'il est défini à 0, les types et les valeurs des attributs sont récupérés, ce qui correspond au comportement par défaut.
	 * @param int $sizelimit Vous permet de limiter le nombre d'entrées à récupérer. Le fait de définir ce paramètre à 0 signifie qu'il n'y aura aucune limite.
	 * @return resource a search result identifier or false on error.
	 */
	public function search($base_dn, $filter, $attributes = null, $attrsonly = 0, $sizelimit = 0) {
	    Log::l(Log::DEBUG, "Ldap->search($base_dn, $filter)");
	    return @ldap_search($this->connection, $base_dn, $filter, $attributes, $attrsonly, $sizelimit);
	}
	/**
	 * Retourne les entrées trouvées via le Ldap search
	 * @param resource $search Resource retournée par le search
	 * @return array a complete result information in a multi-dimensional array on success and false on error.
	 */
	public function get_entries($search) {
	    return @ldap_get_entries($this->connection, $search);
	}
	/**
	 * Retourne le nombre d'entrées trouvé via le Ldap search
	 * @param resource $search Resource retournée par le search
	 * @return int number of entries in the result or false on error.
	 */
	public function count_entries($search) {
	    return @ldap_count_entries($this->connection, $search);
	}
	/**
	 * Retourne la premiere entrée trouvée
	 * @param resource $search Resource retournée par le search
	 * @return resource the result entry identifier for the first entry on success and false on error.
	 */
	public function first_entry($search) {
	    if (is_null($this->connection)) $this->connect();
	    return @ldap_first_entry($this->connection, $search);
	}
	/**
	 * Retourne les entrées suivantes de la recherche
	 * @param resource $search Resource retournée par le search
	 * @return resource entry identifier for the next entry in the result whose entries are being read starting with ldap_first_entry. If there are no more entries in the result then it returns false.
	 */
	public function next_entry($search) {
	    if (is_null($this->connection)) $this->connect();
	    return @ldap_next_entry($this->connection, $search);
	}
	/**
	 * Retourne le dn associé à une entrée de l'annuaire
	 * @param resource $entry l'entrée dans laquelle on récupère les infos
	 * @return string the DN of the result entry and false on error.
	 */
	public function get_dn($entry) {
	    if (is_null($this->connection)) $this->connect();
	    return @ldap_get_dn($this->connection, $entry);
	}
	/**
	 * Ajoute l'attribut entry à l'entrée dn.
	 * Elle effectue la modification au niveau attribut, par opposition au niveau objet.
	 * Les additions au niveau objet sont réalisées par ldap_add().
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param array $entry Entrée à remplacer dans l'annuaire
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function mod_add($dn , $entry) {
	    Log::l(Log::DEBUG, "Ldap->mod_add($dn)");
	    return @ldap_mod_add($this->connection, $dn, $entry);
	}
	/**
	 * Remplace l'attribut entry de l'entrée dn.
	 * Elle effectue le remplacement au niveau attribut, par opposition au niveau objet.
	 * Les additions au niveau objet sont réalisées par ldap_modify().
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param array $entry Entrée à remplacer dans l'annuaire
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function mod_replace($dn , $entry) {
	    Log::l(Log::DEBUG, "Ldap->mod_replace($dn)");
	    return @ldap_mod_replace($this->connection, $dn, $entry);
	}
	/**
	 * Efface l'attribut entry de l'entrée dn.
	 * Elle effectue la modification au niveau attribut, par opposition au niveau objet.
	 * Les additions au niveau objet sont réalisées par ldap_delete().
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param array $entry Entrée à remplacer dans l'annuaire
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function mod_del($dn , $entry) {
	    Log::l(Log::DEBUG, "Ldap->mod_del($dn)");
	    return @ldap_mod_del($this->connection, $dn, $entry);
	}
	/**
	 * Ajoute une entrée dans un dossier LDAP.
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param array $entry Entrée à remplacer dans l'annuaire
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function add($dn, $entry) {
	    Log::l(Log::DEBUG, "Ldap->add($dn)");
	    return @ldap_add($this->connection, $dn, $entry);
	}
	/**
	 * Modifie l'entrée identifiée par dn, avec les valeurs fournies dans entry.
	 * La structure de entry est la même que détaillée dans ldap_add().
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param array $entry Entrée à remplacer dans l'annuaire
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function modify($dn, $entry) {
	    Log::l(Log::DEBUG, "Ldap->modify($dn)");
	    return @ldap_modify($this->connection, $dn, $entry);
	}
	/**
	 * Efface une entrée spécifique d'un dossier LDAP.
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function delete($dn) {
	    Log::l(Log::DEBUG, "Ldap->delete($dn)");
	    return @ldap_delete($this->connection, $dn);
	}
	/**
	 * Renomme une entrée pour déplacer l'objet dans l'annuaire
	 * @param string $dn Le nom DN de l'entrée LDAP.
	 * @param string $newrdn The new RDN.
	 * @param string $newparent The new parent/superior entry.
	 * @param bool $deleteoldrdn If TRUE the old RDN value(s) is removed, else the old RDN value(s) is retained as non-distinguished values of the entry.
	 * @return bool Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient.
	 */
	public function rename($dn , $newrdn , $newparent , $deleteoldrdn) {
	    Log::l(Log::DEBUG, "Ldap->rename($dn)");
	    return @ldap_rename($this->connection, $dn, $newrdn, $newparent, $deleteoldrdn);
	}
	/**
	 * Retourne la précédente erreur pour la commande LDAP
	 * @return string Errno: Errmsg
	 */
	public function getError() {
	    $errno = ldap_errno($this->connection);
	    return "$errno: ".ldap_err2str($errno);
	}
	
	/****************** CONFIGURATION ****/
	/**
	 * Retourne la configuration associée
	 * @param string $name Nom de la propriété à retourner
	 * @return string|array Retourne la valeur
	 */
	public function getConfig($name) {
	    if (!isset($this->config[$name])) return null;
	    return $this->config[$name];
	}
	/**
	 * Modifie ou ajoute la configuration associée
	 * @param string $name Nom de la propriété à modifier
	 * @param string|array $value Valeur de la proriété à définir
	 */
	public function setConfig($name, $value) {
	    $this->config[$name] = $value;
	}
	/**
	 * Retourne si la configuration associée existe
	 * @param string $name Nom de la propriété à retourner
	 * @return bool True si la valeur existe, false sinon
	 */
	public function issetConfig($name) {
	    return isset($this->config[$name]);
	}
}
?>