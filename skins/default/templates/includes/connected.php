<?php
/**
 * Template pour la gestion de l'utilisateur connecté
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
use Program\Data\User as u;
use Program\Lib\Request\Localization as l;
?>

<?php if (u::isset_current_user()) { ?>
<div id="conn">
    <span style="font-size: 80%;">
        <?= l::g('Connected as') ?> <b title="<?= u::get_current_user()->fullname ?>"><?= u::get_current_user()->username ?></b>
    </span>
    <a class="pure-button pure-button-new-poll customtooltip_bottom" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>">
        <img alt="Add" src="skins/<?= o::get_env("skin") ?>/images/1395836978_add_new_poll.png" height="10px"/>
        <?= l::g('New poll') ?>
    </a>
    <a class="pure-button pure-button-home customtooltip_bottom" title="<?= l::g("Go back to the main page", false) ?>" href="<?= o::url("main") ?>">
        <?= l::g('Return to the index') ?>
    </a>
    <?php if (\Config\IHM::$SHOW_HELP_BUTTON) { ?>
      <a id="help-page-button" class="pure-button pure-button-help customtooltip_bottom" title="<?= l::g("View help of the page", false) ?>" href="#">
          <?= l::g('Help') ?>
      </a>
    <?php } ?>
    <a class="pure-button pure-button-disconnect customtooltip_bottom" title="<?= l::g("Disconnect from the app", false) ?>" href="<?= o::url("logout") ?>">
        <?= l::g('Disconnect') ?>
    </a>
</div>
<br>
<?php } ?>
