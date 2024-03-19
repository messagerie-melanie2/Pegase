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
  Program\Lib\Templates\Settings as sg,
  Program\Data\User as u,
  Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
        <div id="title">
            <h1>
	            <?= l::g('Settings page') ?>
            </h1>
        </div>
	      <br>
        <div id="settings">
        	<form id="settings_form" action="<?= o::url("settings", ACT_SAVE_SETTINGS) ?>" method="post"  class="pure-form">
        		<fieldset>
        			<div class="pure-control-group">
  		        	<label for="_freebusy_url" style="display: inline-block; width: 200px;"><?= l::g('Freebusy URL') ?></label>
  		        	<input style="width: 400px;" id="settings_freebusy_url" type="text" name="_freebusy_url" value="<?= u::get_current_user()->freebusy_url ?>"
  		        			class="customtooltip_right"  title="<?= l::g('User freebusy url', false) ?>"
  		        			placeholder="<?= l::g('Freebusy URL') ?>" />
  		        </div>
  		        <br>
  		        <div class="pure-control-group">
  		        	<label for="_timezone" style="display: inline-block; width: 200px;"><?= l::g('Timezone') ?></label>
  		        	<?= sg::GetTimezonesSelect() ?>
  		        </div>
  		        <br>
  		        <br>
  		        <input type="hidden" name="_token" value="<?= s::getCSRFToken() ?>"/>
  		        <br>
  		        <div class="pure-controls" style="margin-left: 15%;">
  		        	<button type="submit" class="pure-button pure-button-submit customtooltip_top" title="<?= l::g("Clic here to save the settings") ?>" ><?= l::g('Save the settings') ?></button>
  		        </div>
        		</fieldset>
        	</form>
    		</div>
<?php t::inc('foot') ?>
