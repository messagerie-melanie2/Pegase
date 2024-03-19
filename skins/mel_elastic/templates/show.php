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
use Program\Data\Poll as p;
use Program\Data\User as u;
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Session as Session;
use Program\Lib\Request\Template as t;
use Program\Lib\Templates\Edit_end as e;
use Program\Lib\Templates\Show as s;
use Api\SSO\SSO as sso;
?>
<?php t::inc('head') ?>

<body>
	<div id="prevcontent">
		<?php t::inc('toolbar') ?>
    <!-- Affichage pour utilisateur connecté -->
    <?php if (u::isset_current_user()) { ?>
		<?php t::inc('left_panel') ?>
		<div id="content" class="left-panel">
        <!-- Affichage pour utilisateur non connecté -->
      <?php } else { ?>
        <div id="content" class="no-authenticate">
          <h2 id="aria-label-toolbar" class="voice"><?= l::g('Toolbar') ?></h2>
          <a class="button connect connect-with-account customtooltip_bottom" title="<?= l::g("Clic to connect and respond with your account", false) ?>" href="<?= o::url("login", null, array("poll" => p::get_current_poll()->poll_uid)) ?>">
            <?= l::g('Login, to respond with your account') ?>
          </a>
          <?php if (\Config\IHM::$USE_CERBERE) { ?>
            <a class="button connect connect-with-cerbere customtooltip_bottom" title="<?= l::g("Clic to connect and respond with your Cerbere account", false) ?>" href="<?= sso::get_sso('Cerbere')->getLoginUrl(null, p::get_current_poll()->poll_uid) ?>">
              <?= l::g('Login with cerbere to respond') ?>
            </a>
          <?php } ?>
        <?php } ?>
			<?php t::inc('message') ?>
			<?php if (p::isset_current_poll() && u::isset_current_user()) { ?>
				<div id="head" class="<?= o::tohtml(p::get_current_poll()->type) ?>">
					<?php if (p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
						<?php if (p::get_current_poll()->deleted) { ?>
							<div class="poll_erase">
								<span class="erased_label"><?= l::g('This poll is deleted') ?></span>
								<a id="button_restore_poll" title="<?= l::g('Clic to restore the poll', false) ?>" class="button pure-button restorepoll customtooltip_bottom" href="<?= o::url("main", ACT_RESTORE, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><?= l::g('Restore poll') ?></a>
								<a id="button_erase_poll" title="<?= l::g('Clic to erase the poll', false) ?>" class="button pure-button erasepoll customtooltip_bottom" href="<?= o::url("main", ACT_ERASE, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><?= l::g('Erase poll') ?></a>
							</div>
						<?php } else { ?>
							<div class="poll_lock">
								<?php if (p::get_current_poll()->type != 'rdv') {
									if (p::get_current_poll()->locked === 0) { ?>
										<a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="button pure-button lockpoll customtooltip_bottom" href="<?= o::url(null, ACT_LOCK, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><?= l::g('Lock') ?></a>
									<?php } else { ?>
										<span class="lock_label"><?= l::g('The poll is lock') ?></span>
										<a id="button_unlock_poll" title="<?= l::g("Clic to unlock the poll", false) ?>" class="button pure-button unlockpoll customtooltip_bottom" href="<?= o::url(null, ACT_UNLOCK, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><?= l::g('Unlock') ?></a>
								<?php }
								} ?>
							</div>
						<?php } ?>
					<?php } else if (p::get_current_poll()->deleted) { ?>
						<div class="poll_erase">
							<span class="erased_label"><?= l::g('This poll is deleted') ?></span>
						</div>
					<?php } ?>
					<div class="poll_title" title="<?= l::g('Created by') . ' ' . o::tohtml(o::get_env("poll_organizer")->fullname) . ' ' . o::date_format(strtotime(p::get_current_poll()->created)) . '. ' . l::g('Last modification time') . ' ' . o::date_format(strtotime(p::get_current_poll()->modified)) ?>">
						<?= o::tohtml(p::get_current_poll()->title) ?></div>
            <div class="poll_organizer"><?= l::g('Created by') ?> <?= o::tohtml(o::get_env("poll_organizer")->fullname) ?></div>
            <?php if (!empty(p::get_current_poll()->location)) { ?>
              <div class="poll_location">
                <span class="label"><?= l::g('Edit location') ?> :</span>
                <span class="location"><?= o::tohtml(p::get_current_poll()->location) ?></span>
              </div>
            <?php } ?>
					<?php if (!empty(p::get_current_poll()->description)) { ?>
						<div class="poll_description">
							<span class="label"><?= l::g('Description') ?> :</span>
							<span class="description"><?= o::tohtml(p::get_current_poll()->description) ?></span>
						</div>
					<?php } ?>
					<?php if (!p::get_current_poll()->deleted) { ?>
						<div class="poll_url">
							<span class="url"><?= e::GetPublicUrl(true) ?></span>
							<input type="text" id="input_url" value="<?= o::get_poll_url() ?>">
							<button class="copy_url customtooltip_bottom" title="<?= l::g('Clic here to copy URL') ?>" onclick="poll.command(copy_url)"><?= l::g('Copy URL') ?></button>
						</div>
					<?php } ?>
				</div>
				<?php if (!p::get_current_poll()->deleted) { ?>
					<div id="edit">
						<div id="poll">
							<?= s::GenerateProposalsTable(false) ?>
							<?php if (p::get_current_poll()->locked == 0 && !s::GetUserResponded() && !empty(p::get_current_poll()->proposals) && p::get_current_poll()->type != 'rdv') { ?>
								<div class="check">
									<?php if (p::get_current_poll()->if_needed) { ?>
										<a class="check_all_button" title="<?= l::g("Clic to check all checkboxes", false) ?>" onclick="poll.command(yes_to_all)"><?= l::g("Check all") ?></a>
									<?php } else { ?>
										<a class="check_all_button" title="<?= l::g("Clic to check all checkboxes", false) ?>" onclick="poll.command(check_all)"><?= l::g("Check all") ?></a>
									<?php }
									?>

									<div class="dropdown"></div>
									<div class="options">
										<ul>
											<?php if (p::get_current_poll()->if_needed) { ?>
												<li><a class="uncheck_all_button" title="<?= l::g("Clic to uncheck all checkboxes", false) ?>" onclick="poll.command(no_to_all)"><?= l::g("Uncheck all") ?></a></li>
											<?php } else { ?>
												<li><a class="uncheck_all_button" title="<?= l::g("Clic to uncheck all checkboxes", false) ?>" onclick="poll.command(uncheck_all)"><?= l::g("Uncheck all") ?></a></li>
											<?php }
											?>

											<?php if (p::get_current_poll()->type == 'date') { ?>
												<li><a class="check_all_button" title="<?= l::g("Clic here to automaticaly generate your response from your feebusy", false) ?>" onclick="poll.command(save_from_freebusy)"><?= l::g("Save from freebusy") ?></a>
												</li>
											<?php } ?>
										</ul>
									</div>
								</div>
							<?php } ?>
							<?php if (p::get_current_poll()->locked == 1) { ?>
								<div class="poll_footer">
									<div><?= l::g('Poll is locked, you can not respond') ?></div>
									<?php if (count(o::get_env("best_proposals")) > 0) { ?>
										<div class="best_proposals" style="<?= count(p::get_current_poll()->validate_proposals) > 0 ? "display: none;" : "" ?>">
											<?= s::GetBestProposalsText() ?></div>
									<?php } ?>
									<div class="validate_proposals"><?= s::GetValidateProposalsText() ?></div>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
	</div>
	<?php if (!p::get_current_poll()->deleted) { ?>
		<div class="dialog_popup" id="lock_poll_popup" title="<?= l::g('Lock the poll') ?>">
			<div class="dialog_popup_content">
				<div><?= l::g('Remember to lock the poll when it\'s finished') ?></div>
				<?php if (p::get_current_poll()->type == 'date') { ?>
					<div><?= l::g('So you can create the meeting') ?></div>
				<?php } ?>
				<div><a title="<?= l::g("Clic here to lock the poll", false) ?>" class="pure-button pure-button-edit-poll customtooltip_bottom" href="<?= o::url(null, ACT_LOCK, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"> <?= l::g('Clic here to lock the poll') ?></a></div>
			</div>
		</div>

		<div class="dialog_popup" id="validate_prop_popup" title="<?= l::g('Validate one or more prop') ?>">
			<div class="dialog_popup_content">
				<div><?= l::g('Your poll is now lock! You can validate one or more proposal to notify the attendees') ?></div>
				<div class="dialog_popup_content_separator"></div>
				<div><?= s::GetBestProposalPopup() ?></div>
			</div>
		</div>

		<div class="dialog_popup" id="add_to_calendar_popup" title="<?= l::g('Validate your presence') ?>">
			<div class="dialog_popup_content">
				<div><?= l::g('Organizer has validate one or more date, you can now say if you be there or not') ?></div>
				<div><?= s::GetAddCalendarProposalsPopup() ?></div>
			</div>
		</div>
	<?php } ?>

      <div class="dialog_popup" id="confirmation_rdv" title="Validation du rendez-vous">
        <div class="dialog_popup_content">
          <div class="event_info">
              <div><?= l::g('you have selected the date') ?> :</div>
              <div id="rdv_date" style="font-weight:bold"><p></p></div>
              <div id="email_area">
                <div><?= l::g('confirmation email will be send to') ?> :</div>
                <div id="email"style="font-weight:bold"><?= $_SESSION['user_email']?></div>
              </div>
              

          </div>
          <div id="reason">
            <label for="choose_reason" id="reason_label"><?= l::g('reason')?></label>
            <select name="reasons" id="choose_reason">
              <?php $reasons = explode(";", p::get_current_poll()->reasons)?>
              <?php foreach($reasons as $reason){ ?>
                  <option value="<?= $reason ?>"> <?= $reason ?> </option>
              <?php } ?>
            </select>
          </div>
          
          <div class="user_infos">
            <?php if (u::get_current_user()!=null && u::get_current_user()->is_cerbere){ ?>
              <?php if(p::get_current_poll()->phone_asked){ ?>
                <label for="phone"><?= l::g('phone_number') ?><?= p::get_current_poll()->phone_required ? "*": "" ?> : </label>
                <div id="phone_warning" style="color:red"></div>
                <?php if(u::get_current_user()->phone_number == null){ ?>
                  <input type="tel" class="user_info_input" id="phone" placeholder="">
                <?php }else{ ?>
                  <input type="tel" class="user_info_input" id="phone" value=<?= u::get_current_user()->phone_number?> readonly=true>
                <?php } ?>
              <?php } ?>
              <?php if(p::get_current_poll()->address_asked){ ?>
                <label for="postal_addr"><?= l::g('postal address') ?><?= p::get_current_poll()->address_required ? "*": "" ?> : </label>
                <div id="address_warning" style="color:red"></div>
                <?php if(u::get_current_user()->commune == null){ ?>
                  <input type="text" class="user_info_input" id="postal_addr" placeholder=""  style="width: 100%">
                <?php }else{ ?>
                  <input type="text" class="user_info_input" id="postal_addr" value="<?= u::get_current_user()->commune ?>"  readonly=true style="width: 100%">
                <?php } ?>
              <?php } ?>
              
            <?php }else{ ?>
              <?php if(p::get_current_poll()->phone_asked){ ?>
                <label for="phone" id="phone_label"><?= l::g('phone_number') ?><?= p::get_current_poll()->phone_required ? "*": "" ?> : </label>
                <div id="phone_warning" style="color:red"></div>
                <input type="tel" class="user_info_input" id="phone" placeholder="">
              <?php } ?>
              <?php if(p::get_current_poll()->address_asked){ ?>
                <label for="postal_addr" id="address_label"><?= l::g('postal address') ?><?= p::get_current_poll()->address_required ? "*": "" ?> : </label>
                <div id="address_warning" style="color:red"></div>
                <input type="text" class="user_info_input" id="postal_addr">
              <?php } ?>
            <?php } ?>
          </div>
          <input type="hidden" id="stock" value='' >
          
          <button  onclick="selectedreason(this.form)" type="submit" class="pure-button pure-button-submit customtooltip_top accept_continue" title="<?= l::g("Validate this proposal") ?>"><?= l::g('Validate this proposal') ?></button>
        </div>
      </div>
	
</body>
<?php t::inc('foot') ?>