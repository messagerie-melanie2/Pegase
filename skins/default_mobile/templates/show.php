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
use Program\Lib\Request\Session as Session;
use Program\Lib\Templates\Show as s;
use Program\Lib\Templates\Edit_end as e;
use Program\Data\Poll as p;
use Program\Data\User as u;
?>
<?php t::inc('head') ?>

<body>
  <div data-role="page">
    <div data-role="header" data-tap-toggle="false">
      <h6> </h6>
      <?php if (
        p::isset_current_poll()
        && u::isset_current_user()
        && p::get_current_poll()->organizer_id == u::get_current_user()->user_id
        && !p::get_current_poll()->deleted
      ) { ?>
        <a href="#manage-poll-left-panel" data-icon="gear" class="ui-mini ui-btn-left">Menu</a>
      <?php } ?>
      <?php if (u::isset_current_user()) { ?>
        <div data-role="controlgroup" data-type="horizontal" class="ui-mini ui-btn-right">
          <?php if (o::get_env("page") != "main") { ?>
            <a class="pure-button-home ui-btn ui-btn-icon-right ui-icon-home ui-btn-icon-notext" data-role="button" title="<?= l::g("Go back to the main page", false) ?>" href="<?= o::url("main") ?>"><?= l::g('Return to the index') ?></a>
          <?php } ?>
          <a class="pure-button-new-poll ui-btn ui-btn-icon-right ui-icon-plus ui-btn-icon-notext" data-role="button" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>"><?= l::g('New poll') ?></a>
          <a class="pure-button-disconnect ui-btn ui-btn-icon-right ui-icon-power ui-btn-icon-notext" data-role="button" title="<?= l::g("Disconnect from the app", false) ?>" href="<?= o::url("logout") ?>"><?= l::g('Disconnect') ?></a>
        </div>
      <?php } ?>
    </div>
    <div data-role="panel" data-position-fixed="true" data-display="push" data-theme="b" id="manage-poll-left-panel">
      <?php if (
        p::isset_current_poll()
        && u::isset_current_user()
        && p::get_current_poll()->organizer_id == u::get_current_user()->user_id
        && !p::get_current_poll()->deleted
      ) { ?>
        <h2>Menu</h2>
        <ul data-role="listview">
          <li><a id="button_edit_poll" title="<?= l::g("Clic to edit the poll", false) ?>" class="pure-button-edit-poll" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify poll') ?></a></li>
          <li><a id="button_edit_prop_poll" title="<?= l::g("Clic to change poll proposals", false) ?>" class="pure-button-edit-poll" href="<?= o::url("edit_" . p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify propositions') ?></a></li>
          <?php if (p::get_current_poll()->type != 'rdv') { ?>
            <div><a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="pure-button pure-button-edit-poll" href="<?= o::url(null, (p::get_current_poll()->locked === 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><img alt="Lock" src="skins/<?= o::get_env("skin") ?>/images/1395932256_link-01_white.png" height="40px" /> <?= (p::get_current_poll()->locked === 0 ? l::g('Lock') : l::g('Unlock')) ?></a></div>
          <?php } ?>
          <li><a id="button_delete_poll" title="<?= l::g("Clic to delete the poll", false) ?>" class="pure-button-edit-poll" href="<?= o::url("main", ACT_DELETE, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><?= l::g('Delete poll') ?></a></li>
        </ul>
      <?php } ?>
    </div>
    <div data-role="panel" data-position="right" data-position-fixed="true" data-display="overlay" data-theme="b" id="connected-right-panel">
      <?php t::inc('connected') ?>
    </div>
    <div role="main" class="ui-content">
      <?php t::inc('message') ?>
      <?php if (p::isset_current_poll()) { ?>
        <div id="title">
          <h3><?= o::tohtml(p::get_current_poll()->title) ?></h3>
        </div>
        <?php if (!u::isset_current_user()) { ?>
          <div><a class="pure-button-connect-with-account" data-role="button" href="<?= o::url("login", null, array("url" => urlencode(o::url(null, null, array("u" => p::get_current_poll()->poll_uid))))) ?>"><?= l::g('Login, to respond with your account') ?></a></div>
          <br>
        <?php } ?>
        <div id="edit">
          <br>
          <label><?= l::g('URL to the poll') ?> : <?= e::GetPublicUrl() ?></label>
          <br>
          <?php if (
            !p::get_current_poll()->auth_only
            || u::isset_current_user()
          ) { ?>
            <label><?= l::g('Created by') ?>
              <?php if (u::isset_current_user() && o::get_env("poll_organizer")->user_id == u::get_current_user()->user_id) { ?>
                <b><?= o::tohtml(l::g('You')) ?></b>
              <?php } else { ?>
                <?php if (!u::isset_current_user()) { ?>
                  <b><?= o::tohtml(s::AnonymName(o::get_env("poll_organizer")->fullname)) ?></b>
                <?php } else { ?>
                  <b><?= o::tohtml(o::get_env("poll_organizer")->fullname) ?></b>
                <?php } ?>
              <?php } ?>
              <?= o::date_format(strtotime(p::get_current_poll()->created)) ?>
            </label>
          <?php } ?>
          <label><?= l::g('Last modification time') ?> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?></label>
          <?php if (p::get_current_poll()->auth_only) { ?>
            <label><i><?= l::g('This poll only accept auth users') ?></i></label>
          <?php } ?>
          <?php if (p::get_current_poll()->anonymous) { ?>
            <label><i><?= l::g('This poll is anonyme, user cannot see others responses until the poll is lock') ?></i></label>
          <?php } ?>
          <?php if (p::get_current_poll()->deleted) { ?>
            <label><i><?= l::g('This poll is deleted') ?></i></label>
          <?php } ?>
          <?php if ((!p::get_current_poll()->auth_only
              || u::isset_current_user())
            && !p::get_current_poll()->deleted
          ) { ?>
            <?php if (!empty(p::get_current_poll()->location)) { ?>
              <br>
              <label><i><?= l::g('Edit location') ?> : </i></label>
              <?= o::tohtml(p::get_current_poll()->location) ?>
            <?php }
            if (!empty(p::get_current_poll()->description)) { ?>
              <br><br>
              <label><i><?= l::g('Edit description') ?> : </i></label>
              <div><?= o::tohtml(p::get_current_poll()->description) ?></div>
            <?php } ?>
            <br><br>
            <div id="poll">
              <?= s::GenerateProposalsTable() ?>
              <?php if (p::get_current_poll()->locked == 1) { ?>
                <br>
                <div><?= l::g('Poll is locked, you can not respond') ?></div>
                <?php if (count(o::get_env("best_proposals")) > 0) { ?>
                  <div class="best_proposals" style="<?= count(p::get_current_poll()->validate_proposals) > 0 ? "display: none;" : "" ?>"><?= s::GetBestProposalsText() ?></div>
                <?php } ?>
                <div class="validate_proposals"><?= s::GetValidateProposalsText() ?></div>
              <?php } ?>
            </div>
            <br>
          <?php } ?>
        <?php } ?>
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
            <div><?= l::g('reason')?>:</div>
            <select name="reasons" id="choose_reason">
              <?php $reasons = explode(";", p::get_current_poll()->reasons)?>
              <?php foreach($reasons as $reason){ ?>
                  <option value="<?= $reason ?>"> <?= $reason ?> </option>
              <?php } ?>
            </select>
          </div>
          <br>
          
          <div class="user_infos">
            <?php if (u::get_current_user()!=null && u::get_current_user()->is_cerbere){ ?>
              <?php if(p::get_current_poll()->phone_asked || p::get_current_poll()->address_asked){?>
                <div style="font-weight:bold"><?= l::g('contact info')?> :</div>
              <?php } ?>
              <?php if(p::get_current_poll()->phone_asked){ ?>
                <div><?= l::g('phone_number') ?><?= p::get_current_poll()->phone_required ? "*": "" ?> : </div>
                <div id="phone_warning" style="color:red"></div>
                <?php if(u::get_current_user()->phone_number == null){ ?>
                  <input type="tel" id="phone" placeholder="">
                <?php }else{ ?>
                  <input type="tel" id="phone" value=<?= u::get_current_user()->phone_number?> readonly=true>
                <?php } ?>
              <?php } ?>
              <?php if(p::get_current_poll()->address_asked){ ?>
                <div><?= l::g('postal address') ?><?= p::get_current_poll()->address_required ? "*": "" ?> : </div>
                <div id="address_warning" style="color:red"></div>
                <?php if(u::get_current_user()->commune == null){ ?>
                  <input type="text" id="postal_addr" placeholder=""  style="width: 100%">
                <?php }else{ ?>
                  <input type="text" id="postal_addr" value="<?= u::get_current_user()->commune ?>"  readonly=true style="width: 100%">
                <?php } ?>
              <?php } ?>
              
            <?php }else{ ?>
              <?php if(p::get_current_poll()->phone_asked || p::get_current_poll()->address_asked){?>
                <div style="font-weight:bold"><?= l::g('contact info')?> :</div>
              <?php } ?>
              <?php if(p::get_current_poll()->phone_asked){ ?>
                <div><?= l::g('phone_number') ?><?= p::get_current_poll()->phone_required ? "*": "" ?> : </div>
                <div id="phone_warning" style="color:red"></div>
                <input type="tel" id="phone" placeholder="">
              <?php } ?>
              <?php if(p::get_current_poll()->address_asked){ ?>
                <div><?= l::g('postal address') ?><?= p::get_current_poll()->address_required ? "*": "" ?> : </div>
                <div id="address_warning" style="color:red"></div>
                <input type="text" id="postal_addr"  style="width: 100%">
              <?php } ?>
            <?php } ?>
          </div>
          <input type="hidden" id="stock" value='' >
          
          <button  onclick="selectedreason(this.form)" type="submit" class="pure-button pure-button-submit customtooltip_top accept_continue" title="<?= l::g("Validate this proposal") ?>"><?= l::g('Validate this proposal') ?></button>
        </div>
  </div>
</body>
<?php t::inc('foot') ?>