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
<div id="prevcontent">
	<?php t::inc('toolbar') ?>
    <div id="content">
		<h2 id="aria-label-calendrier" class="voice"><?= l::g('Choose date in the calendar') ?></h2>
        <?php t::inc('message') ?>	
        <div id="calendar" class="customtooltip_top"></div>
        <div id="edit">
    		<form action="<?= o::url("edit_end", o::get_env("action"), array('u' => p::get_current_poll()->poll_uid)) ?>" method="post" onsubmit="setFormSubmitting()" class="pure-form pure-form-aligned">
    			<fieldset>
    			    <div id="props_list">
    			        <h2><?= l::g("Periods") ?></h2>
						<?= e::ShowProps() ?>
    		        </div>
					<div id="props_new">
					</div>
    		        <br>
					<a class="pure-button pure-button-return customtooltip_bottom" title="<?= l::g("Clic to get back to the poll modification", false) ?>" href="<?= o::url("edit", ACT_MODIFY, array('u' => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Return to the edit page of poll') ?></a>
    			    <div class="pure-controls">
    		        	<button type="submit" class="pure-button pure-button-submit customtooltip_bottom" title="<?= l::g('Clic to save the proposals of the poll') ?>"><?= l::g('Continue') ?></button>
    		        </div>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    			</fieldset>
    		</form>
    	</div>
    </div>
<?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>