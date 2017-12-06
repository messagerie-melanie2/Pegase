<?php
/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL
 * Cette application est un doodle-like permettant aux utilisateurs
 * d'effectuer des sondages sur des dates ou bien d'autres criteres
 *
 * L'application est écrite en PHP5,HTML et Javascript
 * et utilise une base de données postgresql et un annuaire LDAP pour l'authentification
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
use Program\Data\Poll as p;
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<div id="prevcontent">
    <div id="content">
        <?php t::inc('message') ?>
        <div id="title">
            <h1>
                <?php if (o::get_env("action") == ACT_NEW) { ?>
                    <?= l::g('Create poll page') ?>
                <?php } else {
                ?>
                    <?= l::g('Modification poll page') ?>
                <?php }?>
            </h1>
        </div>
        <?php if (p::isset_current_poll()
                    && o::get_env("action") != ACT_NEW) { ?>
            <div><a class="pure-button pure-button-see-poll" href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><img alt="See" src="skins/<?= o::get_env("skin") ?>/images/1395933052_message-01_white.png" height="40px"/> <?= l::g('See the poll') ?></a></div>
            <div><a class="pure-button pure-button-edit-poll" href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><img alt="List" src="skins/<?= o::get_env("skin") ?>/images/1395932290_list-01_white.png" height="40px"/> <?= l::g('Modify propositions') ?></a></div>
            <div><a class="pure-button pure-button-edit-poll" href="<?= o::url("edit", (p::get_current_poll()->locked === 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><img alt="Lock" src="skins/<?= o::get_env("skin") ?>/images/1395932256_link-01_white.png" height="40px"/> <?= (p::get_current_poll()->locked === 0 ? l::g('Lock') : l::g('Unlock')) ?></a></div>
        <?php }?>        
        <br>
        <div id="edit">
    		<form id="edit_form" action="<?= o::url(p::isset_current_poll() ? "edit_" . p::get_current_poll()->type : "edit_date", o::get_env("action"), array("u" => o::get_env("poll_uid"))) ?>" method="post"  class="pure-form">
    			<fieldset>
    		        <div class="pure-control-group">
    		        	<label for="edit_title"><?= l::g('Edit title') ?> <span style="color: red;">*</span></label>
    		        	<br>
    		        	<input size="60" id="edit_title" style="width: 100%;" type="text" name="edit_title" value="<?= p::isset_current_poll() ? p::get_current_poll()->title : '' ?>"
    		        			placeholder="<?= l::g('Edit title') ?>" required x-moz-errormessage="<?= l::g('You have to put a title for the poll') ?>" />
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_poll_type"><?= l::g('Edit Poll type') ?> <span style="color: red;">*</span></label>
    		        	<br>
    		        	<select name="edit_poll_type" id="edit_poll_type" style="width: 100%;" >
    		        	<?php foreach (\Config\IHM::$POLL_TYPES as $type) { ?>
    		        	    <?php if (p::isset_current_poll() && $type == p::get_current_poll()->type) { ?>
    		        	        <option value="<?= $type ?>"  selected="selected"><?= l::g("poll_type_$type") ?></option>
    		        	    <?php } else { ?>
    		        	        <option value="<?= $type ?>"><?= l::g("poll_type_$type") ?></option>
    		        	    <?php }?>
    		        	<?php } ?>
    		        	</select>
    		        	<div id="warning_change_poll_type"><?= l::g("Warning: If you change poll type, proposals previously add (date or free) will be lost") ?></div>
    		        </div>
    		        <br>		        
    		        <div class="pure-control-group">
    		        	<label for="edit_location"><?= l::g('Edit location') ?></label>
    		        	<br>
    		        	<input size="60" id="edit_location" style="width: 100%;" type="text" name="edit_location" value="<?= p::isset_current_poll() ? p::get_current_poll()->location : '' ?>"
    		        			placeholder="<?= l::g('Edit location') ?>" />
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_description"><?= l::g('Edit description') ?></label>
    		        	<br>
    		        	<textarea rows="4" cols="60" style="width: 100%;" id="edit_description" name="edit_description"><?= p::isset_current_poll() ? p::get_current_poll()->description : '' ?></textarea>
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_only_auth_user" class="customtooltip_right" title="<?= l::g("This poll is only open for auth users", false) ?>">
    		        	    <input id="edit_only_auth_user" name="edit_only_auth_user" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->auth_only ? 'checked' : '' ?>>
    		        	    <?= l::g('Poll for only auth user') ?>
    		        	</label>
    		        </div>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    		        <br>
    		        <div class="pure-controls">
    		        	<button type="submit" class="pure-button pure-button-submit"><?= o::get_env("action") == ACT_NEW ? l::g('Save and choose propositions') : l::g('Save and modify propositions') ?></button>
    		        </div>
    			</fieldset>
    		</form>
    	</div>
    	<?php t::inc('connected') ?>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>