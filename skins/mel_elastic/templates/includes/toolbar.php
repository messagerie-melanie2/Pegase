<?php

/**
 * Template pour la gestion de l'utilisateur connecté
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
use Program\Data\Poll as p;
use Program\Data\User as u;
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Session as s;
use Program\Drivers\Driver as d;
?>
<?php if (u::isset_current_user()) { ?>
	<div class="toolbar" role="toolbar">
		<h2 id="aria-label-toolbar" class="voice"><?= l::g('Toolbar') ?></h2>
		<?php if (o::get_env("page") == 'edit' || o::get_env("page") == 'edit_date' || o::get_env("page") == 'edit_prop' || o::get_env("page") == 'edit_rdv' || o::get_env("page") == 'edit_end') { ?>
			<a class="button back customtooltip_bottom" title="<?= l::g("Go back to main page", false) ?>" href="<?= o::url("main") ?>" title="<?= l::g('Back') ?>">
				<span class="material-symbols-outlined">arrow_back</span>
			</a>
		<?php } else { ?>
			<?php if(count(d::get_driver()->listBAL())>1){?>
				<?php if(isset($_GET["username"])){
					d::get_driver()->changeUser($_GET["username"]);
				}
				?>
				<?php $listebal = d::get_driver()->listBAL() ?>
				<select id="balselect">
					<?php $u = u::get_current_user()->username?>
					<?php foreach ($listebal as $bal) { ?>
						<option value=<?= $bal["uid"] ?> <?=$bal["mailboxuid"] == u::get_current_user()->username ? ' selected="selected"' : '';?>><?= $bal["fullname"] ?></option>
					<?php } ?>
				</select>
			<?php } else{?>
			<span class="spacer"></span>
			<?php } ?>
			<a class="button addpoll customtooltip_bottom" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>">
				<span class="material-symbols-outlined icon">
					playlist_add
				</span>
				<span class="inner"><?= l::g('New poll') ?></span>
			</a>
		<?php } ?>
		
		<?php if (
			o::get_env("page") == 'show'
			&& p::isset_current_poll()
			&& !\Program\Data\Poll::get_current_poll()->deleted
		) { ?>
			<?php if (p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
				<div class="toolbar_buttons">
					<a id="button_edit_poll" title="<?= l::g("Clic to edit the poll", false) ?>" class="button" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>" title="<?= l::g('Modify poll') ?>">
						<span class="material-symbols-outlined edit_poll">
							edit
						</span>
						Editer
					</a>
					<a id="button_modify_responses_poll" title="<?= l::g("Clic to change everybody responses", false) ?>" class="button" href="<?= o::url(null, ACT_MODIFY_ALL, array("u" => p::get_current_poll()->poll_uid)) ?>" title="<?= l::g('Modify responses') ?>">
						<span class="material-symbols-outlined edit_response">
							edit_note
						</span>
						Modifier
					</a>
					<a id="button_delete_poll" title="<?= l::g("Clic to delete the poll", false) ?>" class="button deletepoll customtooltip_bottom" href="<?= o::url("main", ACT_DELETE, array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>" title="<?= l::g('Delete poll') ?>">
						<span class="material-symbols-outlined delete_poll">
							delete
						</span>
						Suprimer
					</a>
				</div>
			<?php } ?>
		<?php } elseif (o::get_env("page") == 'edit' || o::get_env("page") == 'edit_date' || o::get_env("page") == 'edit_prop' || o::get_env("page") == 'edit_rdv' || o::get_env("page") == 'edit_end') { ?>
			<span class="edit_nav">
				<span class="edit_desc<?= o::get_env("page") == 'edit' ? ' selected' : '' ?>">
					<?= l::g('Description') ?>
				</span>
				<span class="edit_props<?= o::get_env("page") == 'edit_date' || o::get_env("page") == 'edit_prop' ||  o::get_env("page") == 'edit_rdv' ? ' selected' : '' ?>">
					<?= l::g('Proposals') ?>
				</span>
				<span class="edit_end<?= o::get_env("page") == 'edit_end' ? ' selected' : '' ?>">
					<?= l::g('Invitations') ?>
				</span>
			</span>
		<?php } ?>
	</div>
<?php } ?>