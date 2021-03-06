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
 * Configuration de la connexion SQL vers la base de données Mélanie2
 *
 * @package Config
 */
class Sql {
    /**
     * Configuration du choix de serveur utilisé pour la lecture dans la base de données
     * @var string
     */
    public static $READ_SERVER = "sgbd.server";
    /**
     * Configuration du choix de serveur utilisé pour l'écriture dans la base de données
     * @var string
     */
    public static $WRITE_SERVER = "sgbd.server";
  	/**
  	 * Configuration des serveurs SQL
  	 * Chaque clé indique le nom du serveur ldap et sa configuration de connexion
  	 * @var array
  	 */
  	public static $SERVERS = array(
        "sgbd.server" => array(
          		/**
          		 * Connexion persistante
          		 */
          		'persistent' => 'true',
              /**
               * Le Data Source Name (DSN) de PDO
               * Voir http://php.net/manual/en/ref.pdo-mysql.connection.php
               * Ex: mysql:host=localhost;dbname=testdb
               */
              'dsn' => "sqlite:/home/user/data/database.sqlite",
              /**
               * Utilisateur pour la connexion à la base
               */
              'username' => '',
          		/**
          		 * Mot de passe pour l'utilisateur
          		 */
          		'password' => '',
        )
	  );
}