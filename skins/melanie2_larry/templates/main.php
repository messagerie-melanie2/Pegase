<?php
/**
 * Template pour la page principale de l'application de sondage
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
use Program\Lib\Templates\Main as m;
?>
<?php t::inc('head') ?>
<body>
<div id="prevcontent">
	<?php t::inc('toolbar') ?>
    <div id="content">
        <?php t::inc('message') ?>
        <div id="listyourpolls">
            <h3><?= l::g('List of your polls') ?></h3>
            <?= m::GetUserPolls() ?>
        </div>
        <div id="listpollsresponded">
            <h3><?= l::g('List of polls you have responded') ?></h3>
            <?= m::GetUserRespondedPolls() ?>
        </div>
        <br>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>