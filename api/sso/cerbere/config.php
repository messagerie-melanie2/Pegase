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
namespace Api\SSO\Cerbere;

/**
 * Configuration du SSO Cerbere
 *
 * @package Config
 */
class Config {
  /**
   * Host du serveur Cerbere
   * @var string
   */
  public static $HOST = "authentification.din.developpement-durable.gouv.fr";
  
  /**
   * Contexte du serveur Cerbere
   * @var string
   */
  public static $CONTEXT = "/cas/public";
  
  /**
   * Port HTTP du serveur Cerbere
   * @var integer
   */
  public static $PORT = 443;
  
  /**
   * Chemin vers le certificat du serveur Cerbere
   * @var string
   */
  public static $CA_CERT = 'certs/AC-RGS-Certigna-Racine-SHA1.pem';
  
  /**
   * Mapping des URLs de Freebusy
   * @var array
   */
  public static $FREEBUSY_URL_MAPPING = array(
//       "gmail.com" => "https://calendar.google.com/calendar/ical/%%UTILISATEUR.MEL%%/public/basic.ics",
  );
}
