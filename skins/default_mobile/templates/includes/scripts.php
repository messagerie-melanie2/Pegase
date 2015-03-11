<?php
/**
 * Scripts css/javascript à charger pour l'application de sondage
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
// Utilisation des namespaces
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Request as r;
?>
<link href='skins/<?= o::get_env('skin') ?>/css/lib/pure-min.css' rel='stylesheet' />
<link href='skins/<?= o::get_env('skin') ?>/css/src/style.css' rel='stylesheet' />
<link href='skins/<?= o::get_env('skin') ?>/css/src/custom.css' rel='stylesheet' />
<script src='javascript/lib/jquery-1.11.0.min.js'></script>
<script src='javascript/src/app.js'></script>
<?php if (o::get_env("page") == 'edit_date') { ?>
    <link href='javascript/lib/fullcalendar/fullcalendar.css' rel='stylesheet' />
    <link href='javascript/lib/fullcalendar/fullcalendar.print.css' rel='stylesheet' media='print' />
    <script src='javascript/lib/jquery-ui.custom.min.js'></script>
    <script src='javascript/lib/fullcalendar/fullcalendar.min.js'></script>
    <script src='javascript/src/calendar.js'></script>
<?php } elseif (o::get_env("page") == 'edit_prop') { ?>
    <script src='javascript/src/proposals.js'></script>
<?php } elseif (o::get_env("page") == 'edit') { ?>
    <script src='javascript/src/edit.js'></script> 
<?php } elseif (o::get_env("page") == 'show') { ?>
    <script src='javascript/src/show.js'></script>
<?php } elseif (o::get_env("page") == 'main') { ?>
    <script src='javascript/src/main.js'></script>
<?php } ?>