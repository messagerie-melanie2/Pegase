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

if (CONFIGURATION_TYPE_PEGASE == TYPE_EXTERNAL_PEGASE) {
    // Chargement de la configuration de l'application en fonction de l'environnement
    include_once CONFIGURATION_PATH_PEGASE.'/driver.php';
    include_once CONFIGURATION_PATH_PEGASE.'/ldap.php';
    include_once CONFIGURATION_PATH_PEGASE.'/sql.php';
    include_once CONFIGURATION_PATH_PEGASE.'/log.php';
    include_once CONFIGURATION_PATH_PEGASE.'/ihm.php';
}
else if (CONFIGURATION_TYPE_PEGASE == TYPE_INTERNAL_PEGASE) {
    // Chargement de la configuration de l'application en fonction de l'environnement
    include_once 'config/'.ENVIRONNEMENT_PEGASE.'/driver.php';
    include_once 'config/'.ENVIRONNEMENT_PEGASE.'/ldap.php';
    include_once 'config/'.ENVIRONNEMENT_PEGASE.'/sql.php';
    include_once 'config/'.ENVIRONNEMENT_PEGASE.'/log.php';
    include_once 'config/'.ENVIRONNEMENT_PEGASE.'/ihm.php';
}
