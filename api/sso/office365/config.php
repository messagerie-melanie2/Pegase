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
namespace Api\SSO\Office365;

/**
 * Configuration de l'ihm pour l'application de sondage
 *
 * @package Config
 */
class Config {
	/**
	 * URL utilisée pour le SSO
	 * @var string
	 */
	public static $SSO_AUTHORITY = "https://login.microsoftonline.com";
	/**
	 * URL relative vers le login pour le SSO
	 * @var string
	 */
	public static $SSO_AUTHORIZE_URL = '/common/oauth2/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code';
	/**
	 * URL relative vers la gestion du token pour le SSO
	 * @var string
	 */
	public static $SSO_TOKEN_URL = "/common/oauth2/token";
	/**
	 * URL relative vers la deconnexion du SSO
	 * @var string
	 */
	public static $SSO_LOGOUT_URL = '/common/oauth2/logout?post_logout_redirect_uri=%1$s';
	/**
	 * Identifiant de l'API pour le SSO
	 * @var string
	 */
	public static $SSO_API_ID = 'https://APITECH.onmicrosoft.com/PegaseDev';
	/**
	 * Identifiant du CLIENT pour l'accés aux API du SSO
	 * @var string
	 */
	public static $SSO_CLIENT_ID = 'f4220f6e-cb7f-42bc-ba17-77bb87b6d598';
	/**
	 * Clé de connexion aux API du SSO
	 * @var string
	 */
	public static $SSO_API_KEY = 'pdMxpET8QFAbNAKjghrKrIUd8MculgoOAprADu9Owzo=';
	/**
	 * URL vers les API Outlook
	 * @var string
	 */
	public static $OUTLOOK_API_URL = "https://outlook.office365.com/api/v1.0";
}