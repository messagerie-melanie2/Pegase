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
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<?php t::inc('header') ?>
<div id="prevcontent">
	<?php t::inc('left_panel') ?>
    <div id="content">
        <?php t::inc('message') ?>	
        <div id="title">
            <?php if (o::get_env("action") == ACT_NEW) { ?>
                <h1><?= l::g('Create poll page, modify the dates') ?></h1>
            <?php } else {
            ?>
                <h1><?= l::g('Modification poll page, change the dates') ?></h1>
            <?php }?>
        </div>
        <div class="pure-control-group">
        	<label style="width: 35%;"><i><?= l::g('Poll name') ?> : </i></label>
        	<span id="poll_title"><?= o::tohtml(p::get_current_poll()->title) ?></span>
        </div>
        <div class="pure-control-group">
        	<label style="width: 35%;"><?= l::g('Last modification time') ?> </label> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?>
        </div>
        <br>
        <div class="pure-controls" style="margin-left: 40%;">
    		<button type="submit" form="calendar_form" class="pure-button pure-button-submit customtooltip_bottom" title="<?= l::g('Clic to save the proposals of the poll') ?>"><?= l::g('Save the poll') ?></button>
    	</div>
    	<br>
        <div id="calendar" title="<?= l::g('Select dates by clicking in the calendar') ?>" style="width: 90%; margin-left: 5%;" class="customtooltip_top"></div>
        <div id="edit">
    		<form id="calendar_form" action="<?= o::url("edit_end", o::get_env("action"), array('u' => p::get_current_poll()->poll_uid)) ?>" method="post" class="pure-form pure-form-aligned">
    			<fieldset>
    			    <div class="pure-controls" style="margin-left: 40%;">
    		        	<button type="submit" class="pure-button pure-button-submit customtooltip_bottom" title="<?= l::g('Clic to save the proposals of the poll') ?>"><?= l::g('Save the poll') ?></button>
    		        </div>
    		        <br>
    			    <div id="props_list">
    			        <label style="margin-left: 35%;"><?= l::g("Selected date list") ?></label>
    		            <?= e::ShowProps() ?>
    		        </div>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    			</fieldset>
    		</form>
    	</div>
    	<br>
    </div>
</div>
</body>
<?php t::inc('foot') ?>