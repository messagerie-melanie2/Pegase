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
<div id="prevcontent">
    <div id="content">
        <?php t::inc('message') ?>
        <?php t::inc('connected') ?>
        <br><br>
        <div><?= l::g('Congratulation, your poll is now created') ?></div>
        <div><?= l::g('You can now share this url with your friend') ?></div>
        <?= e::GetPublicUrl() ?>
        <br><br>
        <div><?= l::g('You can see the poll by clicking ') ?><a href="<?= o::url(null, null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('here') ?></a></div>
        <div><?= l::g('You can modify the poll by clicking ') ?><a href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('here') ?></a></div>
        <br>
        <div id="notify_attendees">
          <h3><?= l::g('Invit attendees') ?></h3>
          <div><?= l::g('You can now invit attendees to answer the poll') ?></div>
          <div id="attendees-list">
    				<div class="attendees-list-searchbox">
    					<form class="pure-form autocomplete" action="#" autocomplete="off">
    						<input id="attendees-list-searchbox-input" type="text"
    							name="attendees-list-searchbox-input" value=""
    							class="customtooltip_right autocomplete"
    							title="<?= l::g('Add attendees tooltip') ?>"
    							placeholder="<?= l::g('Add attendees') ?>" /> <button class="button-validate-autocomplete pure-button pure-button-modify-poll customtooltip_bottom"><?= l::g('OK') ?></button>
    						<div class="autocomplete-results"></div>
    					</form>
    				</div>
    				<div class="attendees-list-items">
    					<ul>
    					</ul>
    				</div>
    				<button class="button-send-invitation pure-button pure-button-save customtooltip_bottom"><?= l::g('Send invitation') ?></button>
			    </div>
          <br>
        </div>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>