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

/* Définition des environnements pour les configurations externes */
/**
 * Configuration externe vers un répertoire
 */
define('TYPE_EXTERNAL_PEGASE', 'external');
/**
 * Configuration dans le répertoire config/ de l'ORM
*/
define('TYPE_INTERNAL_PEGASE', 'internal');


/****** PARTIE CONFIGURATION A MODIFIER SI BESOIN ****/
/**
 * Configuration externe ou interne
 * La configuration TYPE_INTERNAL va lire les données dans le répertoire /config de l'ORM
 * Dans ce cas la configuration chargée sera fonction du ENVIRONNEMENT_LIBM2
 * La configuration TYPE_EXTERNAL va les lire les données dans un répertoire configuré dans CONFIGURATION_PATH_LIBM2
 */
define('CONFIGURATION_TYPE_PEGASE', TYPE_INTERNAL_PEGASE);


/***** CONFIGURATION EXTERNE *******/
/**
 * Chemin vers la configuration externe
 */
define('CONFIGURATION_PATH_PEGASE', '/etc/pegase');


//****** CONFIGURATION INTERNE ******/
/**
 * Choix de l'environnement à configurer, si utilisation de la configuration interne (production, developpement, ...)
 */
define('ENVIRONNEMENT_PEGASE', DEFAULT_CONF);

