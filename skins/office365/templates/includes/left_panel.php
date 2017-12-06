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
use Program\Lib\Templates\Main as m;
use Program\Data\User as u;
use Program\Data\Poll as p;
?>
<div id="left-panel">
	<?php if (u::isset_current_user()) { ?>
		<div class="left-panel-new-poll">
			<a class="customtooltip_bottom" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW, null, true) ?>">
	        	<img alt="Add" src="skins/<?= o::get_env("skin") ?>/images/1430581158_1_1-128.png" height="16px"/>
	        	<?= l::g('New poll') ?>
	       	</a>
		</div>
		<div class="left-panel-poll-list">
			<div id="listyourpolls">
	            <h3><?= l::g('List of your polls') ?></h3>
	            <?= m::GetUserPolls() ?>
	        </div>
		</div>
	<?php } else { ?>
		<div class="left-panel-login">
			<?php if (p::isset_current_poll()) { ?>
				<a class="pure-button pure-button-connect-with-account customtooltip_bottom" title="<?= l::g("Clic to connect and answer with your Office 365 account") ?>" href="<?= o::url("login", null, array("poll" => p::get_current_poll()->poll_uid)) ?>"><img alt="Connect" src="skins/<?= o::get_env("skin") ?>/images/1395933029_user-01_white.png" height="20px"/> <?= l::g('Login to your Office 365 account to answer') ?></a>
			<?php } else { ?>
				<a class="pure-button pure-button-connect-with-account customtooltip_bottom" title="<?= l::g("Clic to connect with your Office 365 account") ?>" href="<?= o::url("login") ?>"><img alt="Connect" src="skins/<?= o::get_env("skin") ?>/images/1395933029_user-01_white.png" height="20px"/> <?= l::g('Login to your Office 365 account') ?></a>
			<?php } ?>
		</div>
	<?php } ?>
	<br>
</div>
