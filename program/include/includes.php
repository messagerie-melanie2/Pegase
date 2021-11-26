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

// Configuration du nom de l'application pour l'ORM
if (! defined('CONFIGURATION_APP_LIBM2')) {
  define('CONFIGURATION_APP_LIBM2', 'roundcube');
}

require_once __DIR__."/../lib/defs/defs.php";
require_once __DIR__."/../../config/env.php";

// Chargement de la configuration en fonction de l'environnement
require_once __DIR__."/../include/include_conf.php";

// Utilisation d'un autoloader
// http://fr.php.net/manual/fr/function.spl-autoload-register.php
require_once __DIR__."/../include/autoload.php";

// Inclusion de la localisation
require_once __DIR__."/../include/include_localization.php";

// Charge les vendor
require_once __DIR__."/../../vendor/autoload.php";

