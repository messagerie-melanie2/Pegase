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
use Program\Lib\Request\Output as o,
  Program\Lib\Request\Localization as l,
  Program\Lib\Request\Template as t,
  Program\Lib\Request\Request as r,
  Program\Data\Poll as p,
  Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<div id="prevcontent">
	<?php t::inc('toolbar') ?>
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
        <div id="edit">
    		<form id="edit_form" action="<?= o::url(p::isset_current_poll() ? "edit_" . p::get_current_poll()->type : "edit_date", o::get_env("action"), array("u" => o::get_env("poll_uid"))) ?>" method="post"  class="pure-form">
    			<fieldset>
    		        <div class="pure-control-group">
    		        	<label for="edit_title"><?= l::g('Edit title') ?> <span style="color: red;">*</span></label>
    		        	<br>
    		        	<input style="width: 60%;" id="edit_title" type="text" name="edit_title" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->title) : r::getInputValue('title', POLL_INPUT_GET) ?>"
    		        			class="customtooltip_right" title="<?= l::g('Title of the poll', false) ?>"
    		        			placeholder="<?= l::g('Edit title') ?>" required x-moz-errormessage="<?= l::g('You have to put a title for the poll') ?>" />
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_poll_type"><?= l::g('Edit Poll type') ?> <span style="color: red;">*</span></label>
    		        	<br>
    		        	<select name="edit_poll_type" id="edit_poll_type" style="width: 60%;"
    		        	    class="customtooltip_right"  title="<?= l::g('Type of the poll', false) ?>"
    		        	>
    		        	<?php foreach (\Config\IHM::$POLL_TYPES as $type) { ?>
    		        	    <?php if (p::isset_current_poll() && $type == p::get_current_poll()->type) { ?>
    		        	        <option value="<?= $type ?>"  selected="selected"
    		        	            class="customtooltip_right" title="<?= l::g('select poll type ' . $type) ?>"
    		        	        ><?= l::g("poll_type_$type") ?></option>
    		        	    <?php } else { ?>
    		        	        <option value="<?= $type ?>"
    		        	            class="customtooltip_right" title="<?= l::g('select poll type ' . $type) ?>"
    		        	        ><?= l::g("poll_type_$type") ?></option>
    		        	    <?php }?>
    		        	<?php } ?>
    		        	</select>
    		        	<div id="warning_change_poll_type"><?= l::g("Warning: If you change poll type, proposals previously add (date or free) will be lost") ?></div>
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_location"><?= l::g('Edit location') ?></label>
    		        	<br>
    		        	<input style="width: 60%;" id="edit_location" type="text" name="edit_location" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->location) : r::getInputValue('location', POLL_INPUT_GET) ?>"
    		        			class="customtooltip_right"  title="<?= l::g('Location of the poll', false) ?>"
    		        			placeholder="<?= l::g('Edit location') ?>" />
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label for="edit_description"><?= l::g('Edit description') ?></label>
    		        	<br>
    		        	<textarea class="customtooltip_right"  title="<?= l::g('Description of the poll', false) ?>"
    		        	    rows="4" style="width: 60%;" id="edit_description" name="edit_description"><?= p::isset_current_poll() ? p::get_current_poll()->description : r::getInputValue('description', POLL_INPUT_GET) ?></textarea>
    		        </div>
    		        <br>
    		        <div class="pure-control-group">
    		        	<label style="width: 60%;">
    		        	    <?= l::g('Advanced options') ?>
    		        	</label>
    		        </div>
    		        <div class="pure-control-group">
    		        	<label style="width: 60%;" for="edit_only_auth_user" class="customtooltip_right" title="<?= l::g("This poll is only open for auth users", false) ?>">
    		        	    <input id="edit_only_auth_user" name="edit_only_auth_user" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->auth_only ? 'checked' : '' ?>>
    		        	    <?= l::g('Poll for only auth user') ?>
    		        	</label>
    		        </div>
    		        <div class="pure-control-group">
    		        	<label style="width: 60%;" for="edit_if_needed" class="customtooltip_right" title="<?= l::g("This poll allows users to use the if needed answer", false) ?>">
    		        	    <input id="edit_if_needed" name="edit_if_needed" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->if_needed ? 'checked' : '' ?>>
    		        	    <?= l::g('Allow users to use the if needed answer') ?>
    		        	</label>
    		        </div>
    		        <div class="pure-control-group">
    		        	<label style="width: 60%;" for="edit_anonymous" class="customtooltip_right" title="<?= l::g("Check this for an anonyme poll, user cannot see others responses until the poll is lock", false) ?>">
    		        	    <input id="edit_anonymous" name="edit_anonymous" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->anonymous ? 'checked' : '' ?>>
    		        	    <?= l::g('Anonymous poll, user cannot see others responses') ?>
    		        	</label>
    		        </div>
    		        <br>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    		        <br>
    		        <div class="pure-controls" style="margin-left: 15%;">
    		        	<button type="submit" class="pure-button pure-button-submit customtooltip_top" title="<?= l::g("Save the poll informations and modify proposals") ?>" ><?= o::get_env("action") == ACT_NEW ? l::g('Save and choose propositions') : l::g('Save and modify propositions') ?></button>
    		        </div>
    			</fieldset>
    		</form>
    	</div>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>