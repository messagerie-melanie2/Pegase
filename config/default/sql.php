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
    public static $READ_SERVER = "sqlite.local";
    /**
     * Configuration du choix de serveur utilisé pour l'écriture dans la base de données
     * @var string
     */
    public static $WRITE_SERVER = "sqlite.local";
  	/**
  	 * Configuration de la connexion SQL
  	 * @var array
  	 */
  	public static $SERVERS = [
        "sqlite.local" => [
      		/**
      		 * Connexion persistante
      		 */
      		'persistent' => 'true',
      		/**
      		 * Chaine de connexion pdo
      		 */
      		'connexion' => 'sqlite:/tmp/data.sqlite',
            /**
             * Utilisateur pour la connexion à la base
             */
            'username' => null,
      		/**
      		 * Mot de passe pour l'utilisateur
      		 */
      		'password' => null,
        ]
    ];
}