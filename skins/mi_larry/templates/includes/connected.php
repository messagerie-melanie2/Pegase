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
use Program\Lib\Request\Session as s;
use Program\Lib\Templates\Main as m;
?>

<?php if (u::isset_current_user()) { ?>
<nav id="conn">
    <div>
        <h4><?= l::g('Connected as') ?></h4>
        <p><?= u::get_current_user()->fullname ?></p>
    </div>
    <ul>
      <li>
        <a class="pure-button pure-button-home customtooltip_bottom" title="<?= l::g("Go back to the main page", false) ?>" href="<?= o::url("main") ?>">
            <?= l::g('Return to the index') ?>
        </a>
      </li>
      <li>
        <a class="pure-button pure-button-new-poll customtooltip_bottom" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>">
            <?= l::g('New poll') ?>
        </a>
      </li>
      <?php if (u::isset_current_user()) { ?>
      <li>
        <div class="yourpolls folder"><span class="name"><?= l::g('List of your polls') ?></span></div>
        <div class="children">
            <?= m::GetUserPolls(true) ?>
        </div>
      </li>
      <li>
        <div class="yourresponses folder"><span class="name"><?= l::g('List of your responses') ?></span></div>
        <div class="children">
            <?= m::GetUserRespondedPolls(true) ?>
        </div>
      </li>
      <li>
        <div class="yourdeletedpolls folder"><span class="name"><?= l::g('List of your deleted polls') ?></span></div>
        <div class="children">
            <?= m::GetUserDeletedPolls(true) ?>
        </div>
      </li>
      <?php } ?>
      <!--
      <li>
        <a id="help-page-button" class="pure-button pure-button-help customtooltip_bottom" title="<?= l::g("View help of the page", false) ?>" href="#">
          <?= l::g('Help') ?>
        </a>
      </li>
    -->
    <!--
      <li>
        <a class="pure-button pure-button-settings customtooltip_bottom" title="<?= l::g("Go to user settings", false) ?>" href="<?= o::url("settings") ?>">
        <?= l::g('Settings') ?>
        </a>
      </li>
    -->
      <li>&nbsp;</li>
      <li>&nbsp;</li>
      <li>
        <a class="pure-button pure-button-edit-poll" href="<?= (o::get_env("page") == "show" ? o::url(null, ACT_MOBILE, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid)) : o::url(null, ACT_MOBILE)) ?>">Version <?= l::g("Mobile") ?></a>
      </li>
      <li>
        <a class="pure-button pure-button-disconnect customtooltip_bottom" title="<?= l::g("Disconnect from the app", false) ?>" href="<?= o::url("logoutPortail") ?>">
            <?= l::g('Disconnect') ?>
        </a>
      </li>
    </ul>
</nav>
<?php } ?>
