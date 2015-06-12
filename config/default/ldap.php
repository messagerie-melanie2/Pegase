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
 * Classe de configuration des serveurs LDAP
 *
 * @package Config
 */
class Ldap {
	/**
	 * Configuration du choix de serveur utilisé pour l'authentification
	 * @var string
	 */
	public static $AUTH_LDAP = "ldap.server";
	/**
	 * Configuration du choix de serveur utilisé pour la recherche dans l'annuaire
	 * @var string
	 */
	public static $SEARCH_LDAP = "ldap.server";
	/**
	 * Configuration du choix de serveur utilisé pour l'autocomplétion
	 * @var string
	 */
	public static $AUTOCOMPLETE_LDAP = "ldap.server";

	/**
	 * Configuration des serveurs LDAP
	 * Chaque clé indique le nom du serveur ldap et sa configuration de connexion
	 * hostname, port, dn
	 * informations
	 */
	public static $SERVERS = array(
			/* Serveur LDAP IDA de test */
			"ldap.server" => array(
					/* Host vers le serveur d'annuaire, précédé par ldaps:// si connexion SSL */
					"hostname" => "ldaps://ldap.server",
					/* Port de connexion au LDAP */
					"port" => 636,
					/* Base DN de recherche */
					"base_dn" => "dc=example,dc=com",
					/* Version du protocole LDAP */
					"version" => 3,
					/* Connexion TLS */
					"tls" => false,
					/* Champ LDAP utilisé pour l'authentification, peut être dans mapping ou non */
					"authenticate_field" => "dn",
					/* Filtre complémentaire pour la recherche d'utilisateur */
					"user_filter" => "(mineqTypeentree=BALI)",
					/**
					 * Mapping des champs utilisateurs
					 * Champs à mapper : username, fullname, mail
					 */
					"mapping" => [
					    'username' => 'uid',
					    'fullname' => 'cn',
					    'email' => 'mail',
							'freebusy_url' => 'fburl',
					],
			),
	);
}