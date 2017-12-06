<?php
/**
 * Template pour la barre en haut de la page
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 * @author Apitech
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
use Config\IHM as i;
use Program\Lib\Request\Localization as l;
use Program\Data\User as u;
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Session as s;
use Program\Data\Poll as p;
?>
<div id="header-bar">
	<div class="header-bar-left">
		<div class="header-bar-office-home">
			<a class="customtooltip_bottom" title="<?= l::g("Go to Office 365 Apps", false) ?>" href="<?= i::$OFFICE365_URL ?>">
		        <?= l::g('Office 365') ?>
		    </a>
		</div>
		<div class="header-bar-separator"></div>
		<div class="header-bar-app-name">
			<a class="customtooltip_bottom" title="<?= l::g("Go back to the main page", false) ?>" href="<?= o::url("main") ?>">
		        <?= i::$TITLE ?>
		    </a>
		</div>
	</div>
	<div class="header-bar-center"></div>
	<div class="header-bar-right">
		<div class="header-bar-separator"></div>
		<div class="header-bar-mobile-button">
			<a title="<?= l::g("Switch to mobile skin", false) ?>" class="customtooltip_bottom" href="<?= (o::get_env("page") == "show" ? o::url(null, ACT_MOBILE, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid)) : o::url(null, ACT_MOBILE)) ?>">&nbsp;</a>
		</div>
		<?php if (u::isset_current_user()) { ?>
			<div class="header-bar-separator"></div>
			<div class="header-bar-poll-settings">
				<a onclick="poll.show_popup(event, 'poll-settings-show-popup')" class="button-poll-settings-show-popup customtooltip_bottom" title="<?= l::g("Show poll settings menu", false) ?>" href="#">
			        &nbsp;
			    </a>
			</div>
		<?php } ?>
		<div class="header-bar-separator"></div>
		<div class="header-bar-help-button">
			<a onclick="poll.show_popup(event, 'help-show-popup')" class="button-help-show-popup customtooltip_bottom" title="<?= l::g("Show help menu", false) ?>" href="#">
		        ?
		    </a>
		</div>
		<?php if (u::isset_current_user()) { ?>
			<div class="header-bar-separator"></div>
			<div class="header-bar-user-login">
				<a onclick="poll.show_popup(event, 'user-show-popup')" class="button-user-show-popup customtooltip_bottom" title="<?= l::g("Show user menu", false) ?>" href="#">
			        &nbsp;
			    </a>
			</div>
		<?php } ?>
	</div>
</div>

<?php if (u::isset_current_user()) { ?>
	<div id="user-show-popup" class="popup">
		<div class="user-show-user-infos">
			<div class="user-show-picture">&nbsp;</div>
			<div class="user-show-fullname">
				<?= u::get_current_user()->fullname ?>
			</div>
			<div class="user-show-email">
				<?= u::get_current_user()->email ?>
			</div>
		</div>
		<div class="user-show-menu">
			<div class="user-show-menu-element">
				<a class="customtooltip_bottom" title="<?= l::g("Change your Office 365 password", false) ?>" href="<?= s::get('pwdUrl') ?>">
			        <?= l::g('Change your password') ?>
			    </a>
			</div>
			<div class="user-show-menu-element">
				<a class="customtooltip_bottom" title="<?= l::g("Disconnect from the app", false) ?>" href="<?= \Api\SSO\SSO::get_sso()->getLogoutUrl() ?>">
			        <?= l::g('Disconnect') ?>
			    </a>
			</div>
		</div>
	</div>
<?php } ?>

<div id="help-show-popup" class="popup">
	<div class="help-show-list">
		<div class="copyright">
			<a href="http://apitech.fr/">
		        <?= l::g('copyright') ?>
		    </a>
		</div>
	</div>
</div>

<?php if (u::isset_current_user()) { ?>
	<div id="poll-settings-show-popup" class="popup">
		<div class="poll-settings-list">
			<div class="poll-settings-element">
				<a class="customtooltip_bottom" title="<?= l::g("Clic here to open Office 365 settings", false) ?>" href="<?= \Config\IHM::$OFFICE365_SETTINGS_URL ?>">
			        <?= l::g('Office365 settings') ?>
			    </a>
			</div>
			<?php if (p::isset_current_poll()
					&& u::isset_current_user()
					&& p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
				<div class="separator"></div>
				<div class="title">
					<?= l::g('Poll settings') ?>
				</div>
				<?php if (o::get_env("page") == 'show') { ?>
					<div class="poll-settings-element">
						<a id="button_edit_poll" title="<?= l::g("Clic to edit the poll", false) ?>" class="customtooltip_bottom" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify poll') ?></a>
					</div>
					<div class="poll-settings-element">
						<a id="button_edit_prop_poll" title="<?= l::g("Clic to change poll proposals", false) ?>" class="customtooltip_bottom" href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify propositions') ?></a>
					</div>
					<div class="poll-settings-element">
						<a id="button_modify_responses_poll" title="<?= l::g("Clic to change everybody responses", false) ?>" class="customtooltip_bottom" href="<?= o::url(null, ACT_MODIFY_ALL, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify responses') ?></a>
					</div>
					<div class="poll-settings-element">
						<a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="customtooltip_bottom" href="<?= o::url(null, (p::get_current_poll()->locked == 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= (p::get_current_poll()->locked == 0 ? l::g('Lock') : l::g('Unlock')) ?></a>
					</div>
					<div class="poll-settings-element">
						<a id="button_delete_poll" title="<?= l::g("Clic to delete the poll", false) ?>" class="customtooltip_bottom" href="<?= o::url("main", ACT_DELETE, array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= l::g('Delete poll') ?></a>
					</div>
				<?php } elseif (o::get_env("page") == 'edit'
						&& o::get_env("action") != ACT_NEW) { ?>
					<div class="poll-settings-element">
						<a class="customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>"  href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
					</div>
					<div class="poll-settings-element">
						<a class="customtooltip_bottom" title="<?= l::g("Clic to change poll proposals", false) ?>"  href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify propositions') ?></a>
					</div>
					<div class="poll-settings-element">
						<a class="customtooltip_bottom" title="<?= l::g("Clic to lock the poll", false) ?>"  href="<?= o::url("edit", (!p::get_current_poll()->locked ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= (!p::get_current_poll()->locked ? l::g('Lock') : l::g('Unlock')) ?></a>
					</div>
				<?php } elseif (o::get_env("page") == 'edit_date' || o::get_env("page") == 'edit_prop') { ?>
					<div class="poll-settings-element">
						<a title="<?= l::g("Clic to edit the poll", false) ?>" class="customtooltip_bottom" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify poll') ?></a>
					</div>
					<?php if (o::get_env("action") != ACT_NEW) { ?>
						<div class="poll-settings-element">
							<a class="customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>"  href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
						</div>
					<?php } ?>
				<?php } else { ?>
					<div class="poll-settings-element">
						<a class="customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>"  href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
<?php } ?>
