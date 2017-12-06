<?php
/**
 * Programme de sondage du MEDDE/METL
 *
 * Permet de générer des sondages par les utilisateurs via une page web
 * La génération se fait authentifiée
 * N'importe quel utilisateur peut ensuite répondre au sondage
 *
 * Page de statistiques mensuel
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
/**
 * Login pour la page de statistiques
 */
define('LOGIN','PNE');
/**
 * Mot de passe pour la page de statistiques
 */
define('PASSWORD','Melanie2');

$months = [
    '1' => 'Janvier',
    '2' => 'Fevrier',
    '3' => 'Mars',
    '4' => 'Avril',
    '5' => 'Mai',
    '6' => 'Juin',
    '7' => 'Juillet',
    '8' => 'Aout',
    '9' => 'Septembre',
    '10' => 'Octobre',
    '11' => 'Novembre',
    '12' => 'Decembre',
];

// Gestion de l'authentification
if (!isset($_SERVER['PHP_AUTH_USER'])
        || $_SERVER['PHP_AUTH_USER'] != LOGIN
        || $_SERVER['PHP_AUTH_PW'] != PASSWORD) {
    header('WWW-Authenticate: Basic realm="Pegase Statistiques Page"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Vous devez fournir un login/mot de passe pour afficher cette page';
    echo "\r\n\r\n";
    exit;
}

// Inclusion
include 'program/include/includes.php';

// Récupération des paramètres
$month = \Program\Lib\Request\Request::getInputValue('month', POLL_INPUT_GET);
$year = \Program\Lib\Request\Request::getInputValue('year', POLL_INPUT_GET);

if (empty($year)) {
    $year = date('Y');
}
if (empty($month)) {
    $month = date('n');
    if ($month == 1) {
        $month = 12;
        $year--;
    }
    else {
        $month--;
    }
}

if (!isset($months[$month])) {
    echo "Erreur dans le mois";
    echo "\r\n\r\n";
    exit;
}
// Génération des dates de début et de fin
$start = new DateTime("$year-$month-01 00:00:00");
$before_start = clone $start;
// La recherche des utilisateurs est sur deux mois
$before_start->sub(new DateInterval('P1M'));
$last_day = $start->format('t');
$end = new DateTime("$year-$month-$last_day 23:59:59");

// Récupération des utilisateurs connectés depuis deux mois
$count_auth_users = \Program\Drivers\Driver::get_driver()->countAuthUsers($before_start, new DateTime());
// Récupération des utilisateurs non authentifiés créés depuis deux mois
$count_no_auth_users = \Program\Drivers\Driver::get_driver()->countNoauthUsers($before_start, $end);
// Récupération du nombre de sondages créés pour le mois
$count_polls = \Program\Drivers\Driver::get_driver()->countPolls($start, $end);
// Récupération du nombre de réponses créées pour le mois
$count_responses = \Program\Drivers\Driver::get_driver()->countResponses($start, $end);

// Ecriture des résultats
echo "Statistiques pour le mois de " . $months[$month]. " $year";
echo "\r\n\r\n";
echo "Nombre d'utilisateurs authentifies : $count_auth_users";
echo "\r\n\r\n";
echo "Nombre d'utilisateurs non authentifies : $count_no_auth_users";
echo "\r\n\r\n";
echo "Nombre de sondages crees : $count_polls";
echo "\r\n\r\n";
echo "Nombre de reponses aux sondages : $count_responses";
echo "\r\n\r\n";
