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
use Program\Lib\Request\Localization as l;
use Program\Lib\Templates\Main as m;
use Program\Data\User as u;
use Program\Drivers\Driver as d;
?>
<div id="left-panel">
<h2 id="aria-label-left-panel" class="voice"><?=l::g('Left panel')?></h2>
	<?php if (u::isset_current_user()) { ?>
		<h3></h3>
		<div class="left-panel-poll-list">
			<div id="listyourpolls">
				<div class="yourpolls folder">
					<div class="treetoggle expanded">&nbsp;</div>
					<span class="material-symbols-outlined left-panel-poll-list-icon">patient_list</span>
					<span class="name"><?= l::g('List of your polls') ?></span>
				</div>
				<div class="children"><?= m::GetUserPolls(true) ?></div>
      		</div>
			<div id="listyourresponses">
					<div class="yourresponses folder">
						<div class="treetoggle expanded">&nbsp;</div>
						<span class="material-symbols-outlined left-panel-poll-list-icon">playlist_add_check</span>
						<span class="name"><?= l::g('List of your responses') ?></span>
					</div>
					<div class="children"><?= m::GetUserRespondedPolls(true) ?></div>
			</div>
			<div id="listyourdeletedpolls">
				<div class="yourdeletedpolls folder">
					<div class="treetoggle expanded">&nbsp;</div>
					<span class="material-symbols-outlined left-panel-poll-list-icon">playlist_remove</span>
					<span class="name"><?= l::g('List of your deleted polls') ?></span>
				</div>
				<div class="children"><?= m::GetUserDeletedPolls(true) ?></div>
			</div>
		</div>
	<?php } ?>
</div>
