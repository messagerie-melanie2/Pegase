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
<!-- <link href='skins/<?= o::get_env('skin') ?>/css/lib/pure-min.css' rel='stylesheet' /> -->
<link href='skins/<?= o::get_env('skin') ?>/css/lib/jquery.mobile-1.4.5.min.css' rel='stylesheet' />
<link href='skins/<?= o::get_env('skin') ?>/css/src/style.css?s=<?= BUILD ?>' rel='stylesheet' />
<!-- <link href='skins/<?= o::get_env('skin') ?>/css/src/custom.css?s=<?= BUILD ?>' rel='stylesheet' /> -->
<script src='js/lib/jquery-1.11.0.min.js'></script>
<script src='js/lib/jquery.mobile-1.4.5.min.js'></script>
<script src='js/src/app.js?s=<?= BUILD ?>'></script>
<script src='js/src/mobile.js?s=<?= BUILD ?>'></script>
<?php if (o::get_env("page") == 'edit_date') { ?>
    <script src='js/src/calendar_mobile.js?s=<?= BUILD ?>'></script>
<?php } elseif (o::get_env("page") == 'edit_prop') { ?>
    <script src='js/src/proposals.js?s=<?= BUILD ?>'></script>
<?php } elseif (o::get_env("page") == 'edit') { ?>
    <script src='js/src/edit.js?s=<?= BUILD ?>'></script>
<?php } elseif (o::get_env("page") == 'show') { ?>
    <script src='js/src/show.js?s=<?= BUILD ?>'></script>
<?php } elseif (o::get_env("page") == 'main') { ?>
    <script src='js/src/main.js?s=<?= BUILD ?>'></script>
<?php } ?>