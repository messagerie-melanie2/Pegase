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
namespace Program\Lib\Backend\DB;

// Utilisation des namespaces
use \Program\Lib\Log\Log as Log;

/**
 * Gestion de la connexion Sql
 * @package Program
 * @subpackage Backend
 */
class DB {
    /**
     * Instances SQL
     * @var DB
     */
    private static $instances = array();
	/**
	 * Connexion PDO en cours
	 * @var \PDO
	 */
	private $connection = null;
	/**
	 * String de connexion
	 * @var string
	 */
	private $cnxstring;
	/**
	 * Utilisateur SQL
	 * @var string
	 */
	private $username;
	/**
	 * Mot de passe SQL
	 * @var string
	 */
	private $password;
	/**
	 * Connexion persistante
	 * @var bool
	 */
	private $persistent;

	/************** SINGLETON ***/
	/**
	 * Récupèration de l'instance lié au serveur
	 * @param string $server Nom du serveur, l'instance sera liée à ce nom qui correspond à la configuration du serveur
	 * @return DB
	 */
	public static function GetInstance($server) {
	    Log::l(Log::DEBUG, "DB::GetInstance($server)");
	    if (!isset(self::$instances[$server])) {
	        if (!isset(\Config\Sql::$SERVERS[$server])) {
	            Log::l(Log::ERROR, "DB::GetInstance() Erreur la configuration du serveur '$server' n'existe pas");
	            return false;
	        }
	        self::$instances[$server] = new self(\Config\Sql::$SERVERS[$server]);
	    }
	    return self::$instances[$server];
	}

	/**
	 * Constructor SQL
	 *
	 * @param array $db configuration vers la base de données
     * @access public
	 */
	public function __construct($db) {
		Log::l(Log::DEBUG, "DB->__construct()");
		if (isset($db["dsn"])) {
		  $this->cnxstring = $db["dsn"];
		}
		else {
		  $this->cnxstring = "$db[phptype]:dbname=$db[database];host=$db[hostspec];port=$db[port]";
		}
		$this->username = $db['username'];
		$this->password = $db['password'];
		$this->persistent = $db['persistent'];
		$this->getConnection();
	}

	/**
	 * Destructor SQL
	 *
	 * @access public
	 */
	public function __destruct() {
		Log::l(Log::DEBUG, "DB->__destruct()");
		$this->disconnect();
	}

	/**
	 * Connect to sql database
	 *
	 * @access private
	 * @return boolean true if connect OK
	 */
	private function connect() {
		Log::l(Log::DEBUG, "DB->connect()");
		// Connexion persistante ?
		$driver = array(\PDO::ATTR_PERSISTENT => ($this->persistent == 'true'),
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
		try {
			$this->connection = new \PDO($this->cnxstring, $this->username, $this->password, $driver);
		} catch (\PDOException $e) {
			// Erreur de connexion
			Log::l(Log::ERROR, "DB->connect(): Erreur de connexion à la base de données\n" . $e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * Disconnect from SQL database
	 *
	 * @access public
	 */
	public function disconnect() {
		Log::l(Log::DEBUG, "DB->disconnect()");
		// Deconnexion de la bdd
		if (!is_null($this->connection)) {
			$this->connection = NULL;
		}
	}

	/**
	 * Get the active connection to the sql database
	 *
	 * @access private
	 */
	public function getConnection() {
		// Si la connexion n'existe pas, on se connecte
		if (is_null($this->connection)) $this->connect();
	}

	/**
	 * Execute a sql query to the active database connection in PDO
	 *
	 * If query start by SELECT
	 * return an array of array of data
	 *
	 * @param string $query
	 * @param array $params
	 * @param string $class
	 * @return mixed
	 *
	 * @access public
	 */
	public function executeQuery($query, $params = null, $class = null) {
		Log::l(Log::DEBUG, "DB->executeQuery($query, $class)");
		// Si la requête demarre par SELECT on retourne les resultats
		// Sinon on retourne true (UPDATE/DELETE pas de resultat)
		try {
			$sth = $this->connection->prepare($query);
			if (isset($class)) $sth->setFetchMode(\PDO::FETCH_CLASS, $class);
			if (isset($params)) $sth->execute($params);
			else $sth->execute();
		}
		catch (\PDOException $ex) {
			// Retourne false, erreur
			Log::l(Log::ERROR, "DB->executeQuery(): Exception $ex");
			return false;
		}

		// Tableau de stockage des données sql
		$arrayData = Array();

		// Si la requête demarre par SELECT on retourne les resultats
		// Sinon on retourne true (UPDATE/DELETE pas de resultat)
		if (strpos($query, "SELECT") === 0) {
			while ($object = $sth->fetch()) {
				if (isset($class) && method_exists($object, "__initialize_haschanged")) {
					$object->__initialize_haschanged();
				}
				$arrayData[] = $object;
			}
			return $arrayData;
		} else {
			return true;
		}

		// Retourne false, pas de resultat
		return false;
	}

	/**
	 * Execute a sql query to the active database connection in PDO
	 *
	 * If query start by SELECT
	 * @return boolean
	 *
	 * @param string $query
	 * @param array $params
	 * @param mixed $object
	 *
	 * @access public
	 */
	public function executeQueryToObject($query, $params = null, $object = null) {
		Log::l(Log::DEBUG, "DB->executeQueryToObject($query, ".get_class($object).")");
		// Si la requête demarre par SELECT on retourne les resultats
		// Sinon on retourne null (UPDATE/DELETE pas de resultat)
		try {
			$sth = $this->connection->prepare($query);
			$sth->setFetchMode(\PDO::FETCH_INTO, $object);
			if (isset($params)) $sth->execute($params);
			else $sth->execute();
		} catch (\PDOException $ex) {
			// Retourne false, erreur
			Log::l(Log::DEBUG, "DB->executeQueryToObject(): Exception $ex");
			return false;
		}

		// Si la requête demarre par SELECT on retourne les resultats
		// Sinon on retourne null (UPDATE/DELETE pas de resultat)
		if (strpos($query, "SELECT") == 0) {
	        if ($sth->fetch(\PDO::FETCH_INTO)) {
	            return true;
	        } else {
	            return false;
	        }
		} else {
			return true;
		}
		// Retourne false, pas de resultat
		return false;
	}

	/**
	 * Begin a PDO transaction
	 */
	public function beginTransaction() {
		Log::l(Log::DEBUG, "DB->beginTransaction()");
		return $this->connection->beginTransaction();
	}

	/**
	 * Commit a PDO transaction
	 */
	public function commit() {
		Log::l(Log::DEBUG, "DB->commit()");
		return $this->connection->commit();
	}

	/**
	 * Rollback a PDO transaction
	 */
	public function rollBack() {
		Log::l(Log::DEBUG, "DB->rollBack()");
		return $this->connection->rollBack();
	}

	/**
	 * Returns the ID of the last inserted row or sequence value
	 *
	 * @param $name Name of the sequence object from which the ID should be returned.
	 */
	public function lastInsertId($name = null) {
	    Log::l(Log::DEBUG, "DB->lastInsertId($name)");
	    return $this->connection->lastInsertId($name);
	}
}
?>