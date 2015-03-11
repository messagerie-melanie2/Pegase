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
// ----------------------------------------------------------------------------
// Enregistrement de l'autoloader
// Les classes étant dans des namespaces respectant l'arborescence du projet
// elles sont chargées automatiquement
// ----------------------------------------------------------------------------

/**
 * Méthode de chargement automatique des classes
 * @param string $pClassName
 */
function pollautoload($pClassName) {
	if (strpos($pClassName, 'LibMelanie') === 0 
			|| strpos($pClassName, 'Composer') === 0)
		return;
    // Définition du nom du fichier et du chemin
    $dir_class = $pClassName . '.php';
    // Remplace les \ du namespace par /
    $dir_class = str_replace('\\', '/', $dir_class);
    // Positionne le chemin en minuscule
    $dir_class = strtolower($dir_class);
    // Charge la classe
    include_once $dir_class;
}

// Appel l'autoload register qui va utiliser notre méthode autoload
spl_autoload_register("pollautoload");