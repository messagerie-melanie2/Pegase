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
use Program\Lib\Templates\Edit_date as e;
use Program\Data\Poll as p;
use Program\Data\User as u;
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<div data-role="page" id="edit_date_page">
    <div data-role="header" data-position="fixed">
          <h6> </h6>
        <?php if (p::isset_current_poll()
                      && u::isset_current_user()
                      && p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
    		  <a href="#manage-poll-left-panel" data-icon="gear" class="ui-mini ui-btn-left">Menu</a>
    		<?php } ?>
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
  	<div data-role="panel" data-position-fixed="true" data-display="push" data-theme="b" id="manage-poll-left-panel">
        <h2>Menu</h2>
        <ul data-role="listview">
          <li><a class="pure-button pure-button-edit-poll" href="<?= o::url("edit", ACT_MODIFY, array('u' => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Return to the edit page of poll') ?></a></li>
          <?php if (p::isset_current_poll()
                      && o::get_env("action") != ACT_NEW) { ?>
                  <li><a class="pure-button pure-button-see-poll" href="<?= o::url(null, null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a></li>
          <?php }?>
        </ul>
    </div>
    <div role="main" class="ui-content">
        <?php t::inc('message') ?>
        <div id="title">
            <h3><?php if (o::get_env("action") == ACT_NEW) { ?>
                <?= l::g('Create poll page, modify the dates') ?>
            <?php } else {
            ?>
                <?= l::g('Modification poll page, change the dates') ?>
            <?php }?></h3>
        </div>
        <div class="pure-control-group">
        	<label><i><?= l::g('Poll name') ?> : </i></label>
        	<span id="poll_title"><?= o::tohtml(p::get_current_poll()->title) ?></span>
        </div>
        <div class="pure-control-group">
        	<label><?= l::g('Last modification time') ?>  <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?> </label>
        </div>
        <br>
        <div id="edit">
    		<form action="<?= o::url("edit_end", o::get_env("action"), array('u' => p::get_current_poll()->poll_uid)) ?>" method="post" class="pure-form pure-form-stacked">
    			<fieldset>
    			    <div class="pure-controls" style="margin-left: 0;">
    		        	<button type="submit" class="pure-button pure-button-submit"><?= l::g('Save the poll') ?></button>
    		        </div>
    		        <br>
    			    <div id="props_list">
    		            <?= e::ShowProps() ?>
    		        </div>
    		        <a href="" id="add_new_date"><?= l::g('Add') ?></a>
    		        <br><br>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    		        <div class="pure-controls" style="margin-left: 0;">
    		        	<button type="submit" class="pure-button pure-button-submit"><?= l::g('Save the poll') ?></button>
    		        </div>
    			</fieldset>
    		</form>
    	</div>
    </div>
<?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>