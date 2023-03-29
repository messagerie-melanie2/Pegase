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
			<div id="edit">
				<form id="edit_form" action="<?= o::url(p::isset_current_poll() ? "edit_" . p::get_current_poll()->type : "edit_date", o::get_env("action"), array("u" => o::get_env("poll_uid"))) ?>" method="post" class="pure-form">
					<fieldset id="edit_fieldset">
						<div class="pure-control-group poll_type">
							<select name="edit_poll_type" id="edit_poll_type" class="customtooltip_right" onchange="poll.command(update_select_poll_type)" title="<?= l::g('Type of the poll', false) ?>">
								<?php foreach (\Config\IHM::$POLL_TYPES as $type) { ?>
									<?php if (p::isset_current_poll() && $type == p::get_current_poll()->type) { ?>
										<option value="<?= $type ?>" selected="selected" class="customtooltip_right" title="<?= l::g('select poll type ' . $type) ?>"><?= l::g("poll_type_$type") ?></option>
									<?php } else { ?>
										<option value="<?= $type ?>" class="customtooltip_right" title="<?= l::g('select poll type ' . $type) ?>"><?= l::g("poll_type_$type") ?></option>
									<?php } ?>
								<?php } ?>
							</select>
							<?php foreach (\Config\IHM::$POLL_TYPES as $type) { ?>
								<?php $selected = p::isset_current_poll() && $type == p::get_current_poll()->type || o::get_env("action") == ACT_NEW && $type == 'date' ? ' selected' : ''; ?>
								<span class="poll_type_<?= $type . $selected ?>" onclick="poll.command(switch_poll_type, {type: '<?= $type ?>'})"><?= l::g("poll_type_$type") ?></span>
							<?php } ?>
							<div id="warning_change_poll_type"><?= l::g("Warning: If you change poll type, proposals previously add (date or free) will be lost") ?></div>
						</div>
						<br>
						<div class="pure-control-group">
						<label for="edit_title" class="voice">Titre du sondage</label>
							<input id="edit_title" type="text" name="edit_title" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->title) : r::getInputValue('title', POLL_INPUT_GET) ?>" class="customtooltip_right" title="<?= l::g('Title of the poll', false) ?>" placeholder="<?= l::g('Edit title') ?>*" required x-moz-errormessage="<?= l::g('You have to put a title for the poll') ?>" />
						</div>
						<br>
						<div id="edit_max_attendees_per_prop">
							<div class="pure-control-group">
								<label for="edit_max_attendees" class="voice">Nombre de participants autorisés par proposition</label>
								<input id="edit_max_attendees" type="number" onkeydown="return event.keyCode !== 69" name="edit_max_attendees_per_prop" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->max_attendees_per_prop) : r::getInputValue('max_attendees_per_prop', POLL_INPUT_GET) ?>" class="customtooltip_right" title="<?= l::g('Number of participants allowed', false) ?>" required x-moz-errormessage="<?= l::g('You have to put a title for the poll')?>"  placeholder="<?= l::g('Edit number of participants') ?>" />
								<span id="warning_max_attendees"></span>
							</div>
							<br>
						</div>
						<div class="pure-control-group">
							<label for="edit_location" class="voice">Lieu</label>
							<input id="edit_location" type="text" name="edit_location" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->location) : r::getInputValue('location', POLL_INPUT_GET) ?>" class="customtooltip_right" title="<?= l::g('Location of the poll', false) ?>" placeholder="<?= l::g('Edit location') ?>" />
						</div>
						<br>
						<div class="pure-control-group">
							<label for="edit_description" class="voice">Description</label>
							<textarea class="customtooltip_right" placeholder="<?= l::g('Description') ?>" title="<?= l::g('Description of the poll', false) ?>" rows="4" id="edit_description" name="edit_description"><?= p::isset_current_poll() ? p::get_current_poll()->description : r::getInputValue('description', POLL_INPUT_GET) ?></textarea>
						</div>
						<br>
						
						<div id="enable_reason">
							<div class="pure-control-group">
								<label for="enable_reason_checkbox" class="voice">Activer l'ajout de motifs de rendez-vous</label>
								<input id="enable_reason_checkbox" type="checkbox" name="enable_reason" value="true" <?= p::isset_current_poll() && p::get_current_poll()->reason ? 'checked' : '' ?> class="customtooltip_right" onchange="displayReasonsManager()"/>	
								<?= l::g('add reasons', false) ?>
								<div class= "reasons-manager" hidden=true>
									<input id="reasons" type="hidden" name="reasons" value="<?= p::isset_current_poll() ? o::tohtml(p::get_current_poll()->reasons) : r::getInputValue('reasons', POLL_INPUT_GET) ?>" title="<?= l::g('reasons',false) ?>"/>
									<ul class="pure-control-group reason-list js-reason-list"></ul>
									<form class="js-form pure-control-group">
										<div class="add-reason-bar">
											<input type="text" aria-label="Enter a new reason" placeholder="intitulé du motif" class="js-reason-input">
											<input type="button" value="Ajouter" class="js-reason-add">
										</div>
									</form>
								</div>
							</div>
						</div>
						<div id="phone">
							<div class="pure-control-group">
								<label for="phone_asked" class="voice">Demander le téléphone de l'utilisateur</label>
								<input id="phone_asked" type="checkbox" name="phone_asked" value="true" <?= p::isset_current_poll() && p::get_current_poll()->phone_asked ? 'checked' : '' ?>>
								Demander le téléphone de l'utilisateur
							</div>
							<div id= "phone_req" class="pure-control-group phone-required required">
								<label for="phone_required" class="voice">Téléphone requis</label>
								<input id="phone_required" type="checkbox" name="phone_required" value="true" <?= p::isset_current_poll() && p::get_current_poll()->phone_required ? 'checked' : '' ?>>
								Champ obligatoire
							</div>
						</div>
						<div id="address">
							<div class="pure-control-group">
								<label for="address_asked" class="voice">Demander l'adresse de l'utilisateur</label>
								<input id="address_asked" type="checkbox" name="address_asked" value="true" <?= p::isset_current_poll() && p::get_current_poll()->address_asked ? 'checked' : '' ?>>
								Demander l'adresse de l'utilisateur
							</div>
							<div id= "addr_req" class="pure-control-group address-required required">
								<label for="address_required" class="voice">Adresse requise</label>
								<input id="address_required" type="checkbox" name="address_required" value="true" <?= p::isset_current_poll() && p::get_current_poll()->address_required ? 'checked' : '' ?>>
								Champ Obligatoire
							</div>
						</div>
						<div class="pure-control-group advanced_options folder">
							<div class="treetoggle expanded">&nbsp;</div>
							<label>
								<?= l::g('Advanced options') ?>
							</label>
						</div>
						<br>
						<div class="pure-control-group advanced_option children">
							<label for="edit_only_auth_user" class="customtooltip_right" title="<?= l::g("This poll is only open for auth users", false) ?>">
								<input id="edit_only_auth_user" name="edit_only_auth_user" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->auth_only ? 'checked' : '' ?>>
								<?= l::g('Poll for only auth user') ?>
							</label>
						</div>
						<div class="pure-control-group advanced_option children">
							<label for="edit_prop_in_agenda" class="customtooltip_right" title="<?= l::g("Check to display proposals in my Agenda", false) ?>">
								<input id="edit_prop_in_agenda" name="edit_prop_in_agenda" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->prop_in_agenda ? 'checked' : '' ?>>
								<?= l::g('Display proposals in my Agenda') ?>
							</label>
						</div>
						<div class="pure-control-group advanced_option children">
							<label for="edit_if_needed" class="customtooltip_right" title="<?= l::g("This poll allows users to use the if needed answer", false) ?>">
								<input id="edit_if_needed" name="edit_if_needed" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->if_needed ? 'checked' : '' ?>>
								<?= l::g('Allow users to use the if needed answer') ?>
							</label>
						</div>
						<div class="pure-control-group advanced_option children">
							<label for="edit_anonymous" class="customtooltip_right" title="<?= l::g("Check this for an anonyme poll, user cannot see others responses until the poll is lock", false) ?>">
								<input id="edit_anonymous" name="edit_anonymous" type="checkbox" value="true" <?= p::isset_current_poll() && p::get_current_poll()->anonymous ? 'checked' : '' ?>>
								<?= l::g('Anonymous poll, user cannot see others responses') ?>
							</label>
						</div>
						<br>
						<div class="pure-control-group cgu">
							<label for="edit_cgu_accepted" class="customtooltip_bottom">
								<input id="edit_cgu_accepted" name="edit_cgu_accepted" type="checkbox" value="true" checked>
								<?= l::g('Continue and accept the CGUs', false) ?>
							</label>
						</div>
						<br>
						<input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>" />
						<br>
						<div class="pure-controls">
							<button type="submit" class="pure-button pure-button-submit customtooltip_top accept_continue" title="<?= l::g("Accept the CGUs, save the poll informations and go to modify proposals") ?>"><?= l::g('Accept and continue') ?></button>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<?php t::inc('copyright') ?>
	</div>
</body>
<?php t::inc('foot') ?>