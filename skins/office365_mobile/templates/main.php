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
use Program\Data\User as u;
?>
<?php t::inc('head') ?>
<body>
<div data-role="page">
    <div data-role="header" data-position="fixed" data-tap-toggle="false">
      <h6> </h6>
      <?php if (u::isset_current_user()) { ?>
    		<div data-role="controlgroup" data-type="horizontal" class="ui-mini ui-btn-right">
    		  <?php if (o::get_env("page") != "main") { ?>
            <a class="pure-button-home ui-btn ui-btn-icon-right ui-icon-home ui-btn-icon-notext" data-role="button" title="<?= l::g("Go back to the main page", false) ?>" href="<?= o::url("main") ?>"><?= l::g('Return to the index') ?></a>
          <?php } ?>
          <a class="pure-button-new-poll ui-btn ui-btn-icon-right ui-icon-plus ui-btn-icon-notext" data-role="button" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>"><?= l::g('New poll') ?></a>
          <a class="pure-button-disconnect ui-btn ui-btn-icon-right ui-icon-power ui-btn-icon-notext" data-role="button" title="<?= l::g("Disconnect from the app", false) ?>" href="<?= \Api\SSO\SSO::get_sso()->getLogoutUrl() ?>"><?= l::g('Disconnect') ?></a>
        </div>
      <?php } ?>
  	</div>
    <div data-role="panel" data-position="right" data-position-fixed="true" data-display="overlay" data-theme="b" id="connected-right-panel">
      <?php t::inc('connected') ?>
    </div>
    <div role="main" class="ui-content">
        <div id="title">
            <h3><?= l::g('Welcome to doodle of the MEDDE') ?></h3>
        </div>
        <?php t::inc('message') ?>
        <div id="listyourpolls">
            <h4><?= l::g('List of your polls') ?></h4>
            <?= m::GetUserPolls() ?>
        </div>
        <div id="listpollsresponded">
            <h4><?= l::g('List of polls you have responded') ?></h4>
            <?= m::GetUserRespondedPolls() ?>
        </div>
        <br>
    </div>
<?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>