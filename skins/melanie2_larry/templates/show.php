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
<div id="prevcontent">
	<?php t::inc('toolbar') ?>
    <div id="content">
        <?php t::inc('message') ?>
        <?php if (p::isset_current_poll()) { ?>
            <div id="title">
                <h1><img alt="Find" src="skins/<?= o::get_env("skin") ?>/images/1397844066_poll.png" height="30px"/> <?= o::tohtml(p::get_current_poll()->title) ?></h1>
            </div>
            <div id="edit">
                <div class="poll_information">
                    <div class="pure-control-group">
                    	<label style="width: 35%;"><?= l::g('URL to the poll') ?> : </label>
                    	<?= e::GetPublicUrl(true) ?>
                    </div>
                    <?php if (\Program\Data\Poll::get_current_poll()->auth_only
	                            && !\Program\Data\User::isset_current_user()) { ?>
                        <div class="pure-control-group">
                            <label style="width: 35%;"> <?= l::g('Last modification time') ?> </label> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?>
                        </div>
                    <?php } else { ?>
                        <div class="pure-control-group">
                        	<label style="width: 35%;"><?= l::g('Created by') ?> </label>
                        	<?php if (u::isset_current_user() && o::get_env("poll_organizer")->user_id == u::get_current_user()->user_id) {?>
                        	    <b><?= o::tohtml(l::g('You')) ?></b>
                        	<?php } else {?>
                        	    <?php if (!\Program\Data\User::isset_current_user()) { ?>
                        	        <b><?= o::tohtml(s::AnonymName(o::get_env("poll_organizer")->fullname)) ?></b>
                        	    <?php } else { ?>
                        	        <b><?= o::tohtml(o::get_env("poll_organizer")->fullname) ?></b>
                        	    <?php } ?>
                        	<?php }?>
                        	 <?= o::date_format(strtotime(p::get_current_poll()->created)) ?>.
                        	 <label style="width: 35%;"> <?= l::g('Last modification time') ?> </label> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?>
                        </div>
                    <?php } ?>
                    <?php if (p::get_current_poll()->auth_only) { ?>
                        <div class="pure-control-group">
                        	<label style="width: 35%;"><?= l::g('This poll only accept auth users') ?></label>
                        </div>
                    <?php } ?>
                    <?php if (p::get_current_poll()->anonymous) { ?>
                        <div class="pure-control-group">
                        	<label style="width: 35%;"><?= l::g('This poll is anonyme, user cannot see others responses until the poll is lock') ?></label>
                        </div>
                    <?php } ?>
                    <br>
                </div>
                <?php if (!\Program\Data\Poll::get_current_poll()->auth_only
	                            || \Program\Data\User::isset_current_user()) { ?>
                    <?php if (!empty(p::get_current_poll()->location)) {?>
                    <div class="pure-control-group">
                    	<label style="width: 35%;"><b><?= l::g('Edit location') ?> : </b></label>
                    	<?= o::tohtml(p::get_current_poll()->location) ?>
                    </div>
                    <br>
                    <?php }
                        if (!empty(p::get_current_poll()->description)) { ?>
                    <div class="pure-control-group">
                    	<label style="width: 35%;"><b><?= l::g('Edit description') ?> : </b></label>
                    	<div><?= o::tohtml(p::get_current_poll()->description) ?></div>
                    </div>
                    <br>
                    <?php } ?>
                    <br>
                    <div id="poll">
                        <?= s::GenerateProposalsTable() ?>
                    </div>
                    <?php if (p::get_current_poll()->locked == 1) { ?>
                        <br>
                        <div><?= l::g('Poll is locked, you can not respond') ?></div>
                        <?php if (count(o::get_env("best_proposals")) > 0) { ?>
                            <div class="best_proposals" style="<?= count(p::get_current_poll()->validate_proposals) > 0 ? "display: none;" : "" ?>"><?= s::GetBestProposalsText() ?></div>
                        <?php } ?>
                        <div class="validate_proposals"><?= s::GetValidateProposalsText() ?></div>
                    <?php } ?>
                    <br>
                <?php } ?>
        	</div>
    	<?php } ?>
    </div>
    <?php t::inc('copyright') ?>
</div>
<div class="dialog_popup" id="lock_poll_popup" title="<?= l::g('Lock the poll') ?>">
	<div class="dialog_popup_content">
		<div><?= l::g('Remember to lock the poll when it\'s finished') ?></div>
		<?php if (\Program\Data\Poll::get_current_poll()->type == 'date') { ?>
			<div><?= l::g('So you can create the meeting') ?></div>
		<?php } ?>
		<div><a title="<?= l::g("Clic here to lock the poll", false) ?>" class="pure-button pure-button-edit-poll customtooltip_bottom" href="<?= o::url(null, ACT_LOCK, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><img alt="Lock" src="skins/<?= o::get_env("skin") ?>/images/1395932256_link-01_white.png" height="12px"/> <?= l::g('Clic here to lock the poll') ?></a></div>
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

</body>
<?php t::inc('foot') ?>