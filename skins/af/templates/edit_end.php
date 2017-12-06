<?php
/**
 * Template pour la gestion des erreurs
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
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Template as t;
use Program\Lib\Request\Request as r;
use Program\Lib\Templates\Edit_end as e;
use Program\Data\Poll as p;
?>
<?php t::inc('head') ?>
<body>
<?php t::inc('afheader') ?>
<div id="prevcontent">
    <div id="content">
        <?php t::inc('message') ?>
        <?php t::inc('connected') ?>
        <br><br>
        <div><?= l::g('Congratulation, your poll is now created') ?></div>
        <div><?= l::g('You can now share this url with your friend') ?></div>
        <?= e::GetPublicUrl() ?>
        <br><br>
        <div><?= l::g('You can modify the poll by clicking ') ?><a href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('here') ?></a></div>
        <br>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>