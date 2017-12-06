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
use Program\Lib\Templates\Edit_prop as e;
use Program\Data\Poll as p;
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<?php t::inc('afheader') ?>
<div id="prevcontent">
    <div id="content">
        <?php t::inc('message') ?>	
        <?php t::inc('connected') ?>
        <br>
        <div id="title">
            <?php if (o::get_env("action") == ACT_NEW) { ?>
                <h1><?= l::g('Create poll page, modify your own propositions') ?></h1>
            <?php } else {
            ?>
                <h1><?= l::g('Modification poll page, change your own dates') ?></h1>
            <?php }?>
        </div>
        <a class="pure-button pure-button-edit-poll customtooltip_bottom" title="<?= l::g("Clic to get back to the poll modification", false) ?>" style="width: 25%;" href="<?= o::url("edit", ACT_MODIFY, array('u' => p::get_current_poll()->poll_uid)) ?>"><img alt="Modify" src="skins/<?= o::get_env("skin") ?>/images/1395932254_gear-01_white.png" height="12px"/> <?= l::g('Return to the edit page of poll') ?></a>
        <?php if (p::isset_current_poll()
                    && o::get_env("action") != ACT_NEW) { ?>
                <a class="pure-button pure-button-see-poll customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>" style="width: 25%;" href="<?= o::url(null, null, array("u" => p::get_current_poll()->poll_uid)) ?>"><img alt="See" src="skins/<?= o::get_env("skin") ?>/images/1395933052_message-01_white.png" height="12px"/> <?= l::g('See the poll') ?></a>
        <?php }?>
        <br><br>
        <div class="pure-control-group">
        	<label style="width: 35%;"><i><?= l::g('Poll name') ?> : </i></label>
        	<span id="poll_title"><?= o::tohtml(p::get_current_poll()->title) ?></span>
        </div>
        <div class="pure-control-group">
        	<label style="width: 35%;"><?= l::g('Last modification time') ?> </label> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?>
        </div>
        <br>
        <div id="edit">
    		<form action="<?= o::url("edit_end", o::get_env("action"), array('u' => p::get_current_poll()->poll_uid)) ?>" method="post" class="pure-form pure-form-aligned">
    			<fieldset>
    			    <div id="props_list">
    		            <?= e::ShowProps() ?>
    		        </div>
    		        <a style="margin-left: 50%;" href="" class="customtooltip_bottom" title="<?= l::g('Clic to add a new prop', false) ?>" id="add_new_prop"><?= l::g('Add') ?></a>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    		        <div class="pure-controls" style="margin-left: 40%;">
    		        	<button type="submit" class="pure-button pure-button-submit customtooltip_bottom" title="<?= l::g('Clic to save the proposals of the poll') ?>"><?= l::g('Save the poll') ?></button>
    		        </div>
    			</fieldset>
    		</form>
    	</div>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>