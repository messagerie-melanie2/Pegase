<?php
/**
 * Template pour la page de login de l'application de sondage
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
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
<body>
<div id="prevcontent">
    <div id="content">
        <?php t::inc('message') ?>
        <div id="title">
            <h1><?= l::g('Welcome to doodle of the MEDDE') ?></h1>
        </div>
        <div id="login">
    		<form action="<?= o::url("login") ?>" method="post" class="pure-form pure-form-aligned" autocomplete="off">
    			<fieldset>
    			    <legend><?= l::g('Log-in to create new poll and list all your polls') ?></legend>
    		        <div class="pure-control-group">
    		        	<label style="width: 35%;" for="username"><?= l::g('Username') ?><span style="color: red;">*</span></label>
    		        	<input style="width: 30%;" id="username" type="text" name="username"
    		        	        value="<?= r::getInputValue('username', POLL_INPUT_POST) ?>"
    		        			placeholder="<?= l::g('Username') ?>" required x-moz-errormessage="<?= l::g('You have to put your username') ?>" autofocus/>
    		        </div>				        
    		        <div class="pure-control-group">
    		        	<label style="width: 35%;" for="password"><?= l::g('Password') ?><span style="color: red;">*</span></label>
    		        	<input style="width: 30%;" id="password" type="password" name="password" 
    		        			placeholder="<?= l::g('Password') ?>" x-moz-errormessage="<?= l::g('You have to put your password') ?>" required />
    		        </div>
    		        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
    		        <div class="pure-controls" style="margin-left: 40%;">
    		        	<button type="submit" class="pure-button pure-button-submit"><?= l::g('Connect') ?></button>
    		        </div>
    			</fieldset>
    		</form>
    	</div>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>