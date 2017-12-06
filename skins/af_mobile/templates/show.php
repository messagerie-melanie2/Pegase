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
    <div id="content">
        <?php t::inc('message') ?>
        <?php if (p::isset_current_poll()) { ?>
            <div id="title">
                <h1><img alt="Find" src="skins/<?= o::get_env("skin") ?>/images/1395837531_aiga_information_bg_blue.png" height="50px"/> <?= o::tohtml(p::get_current_poll()->title) ?></h1>
            </div>
            <?php if (u::isset_current_user() 
                    && p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
                    <div><a id="button_edit_poll" title="<?= l::g("Clic to edit the poll", false) ?>" class="pure-button pure-button-edit-poll" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><img alt="Modify" src="skins/<?= o::get_env("skin") ?>/images/1395932254_gear-01_white.png" height="40px"/> <?= l::g('Modify poll') ?></a></div>
                    <div><a id="button_edit_prop_poll" title="<?= l::g("Clic to change poll proposals", false) ?>" class="pure-button pure-button-edit-poll" href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><img alt="List" src="skins/<?= o::get_env("skin") ?>/images/1395932290_list-01_white.png" height="40px"/> <?= l::g('Modify propositions') ?></a></div>
                    <div><a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="pure-button pure-button-edit-poll" href="<?= o::url(null, (p::get_current_poll()->locked === 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><img alt="Lock" src="skins/<?= o::get_env("skin") ?>/images/1395932256_link-01_white.png" height="40px"/> <?= (p::get_current_poll()->locked === 0 ? l::g('Lock') : l::g('Unlock')) ?></a></div>
                    <div><a id="button_delete_poll" title="<?= l::g("Clic to delete the poll", false) ?>" class="pure-button pure-button-edit-poll" href="<?= o::url("main", ACT_DELETE, array("u" => p::get_current_poll()->poll_uid, "t" => Session::getCSRFToken())) ?>"><img alt="Delete" src="skins/<?= o::get_env("skin") ?>/images/1395836978_remove-01_white.png" height="40px"/> <?= l::g('Delete poll') ?></a></div>
                    <br>
            <?php } elseif (!u::isset_current_user()) { ?>
                    <div><a class="pure-button pure-button-connect-with-account" href="<?= o::url("login", null, array("url" => urlencode(o::url(null, null, array("u" => p::get_current_poll()->poll_uid))))) ?>"><?= l::g('Login, to respond with your account') ?></a></div>
                    <br>
            <?php } ?>
            <div id="edit">
            <div class="pure-control-group">
                	<label style="width: 35%;"><?= l::g('URL to the poll') ?> : </label>
                	<?= e::GetPublicUrl() ?>
                </div>
                <div class="pure-control-group">
                	<label style="width: 35%;"><?= l::g('Created by') ?> </label>
                	<?php if (u::isset_current_user() && o::get_env("poll_organizer")->user_id == u::get_current_user()->user_id) {?>
                	    <b><?= o::tohtml(l::g('You')) ?></b>
                	<?php } else {?>
                	    <b><?= o::tohtml(o::get_env("poll_organizer")->fullname) ?></b>
                	<?php }?>
                	 <?= o::date_format(strtotime(p::get_current_poll()->created)) ?>
                </div>
                <div class="pure-control-group"> 
                	<label style="width: 35%;"><?= l::g('Last modification time') ?> </label> <?= o::date_format(strtotime(p::get_current_poll()->modified)) ?>
                </div>
                <br>
                <?php if (p::get_current_poll()->auth_only) { ?>
                    <div class="pure-control-group">
                    	<label style="width: 35%;"><i><?= l::g('This poll only accept auth users') ?></i></label>
                    </div>
                    <br>
                <?php } ?>
                <?php if (!empty(p::get_current_poll()->location)) {?>
                <div class="pure-control-group">
                	<label style="width: 35%;"><i><?= l::g('Edit location') ?> : </i></label>
                	<?= o::tohtml(p::get_current_poll()->location) ?>
                </div>
                <br>
                <?php }
                    if (!empty(p::get_current_poll()->description)) { ?>
                <div class="pure-control-group">
                	<label style="width: 35%;"><i><?= l::g('Edit description') ?> : </i></label>
                	<div><?= o::tohtml(p::get_current_poll()->description) ?></div>
                </div>
                <br>
                <?php } ?>
                <br>
                <div id="poll">
                    <?= s::GenerateProposalsTable() ?>
                    <?php if (p::get_current_poll()->locked === 1) { ?>
                        <br>
                        <div><?= l::g('Poll is locked, you can not respond') ?></div>
                        <?php if (count(o::get_env("best_proposals")) > 0) { ?>
                            <div class="best_proposals" style="font-weight:bold;"><?= count(o::get_env("best_proposals")) === 1 ? l::g('Proposal with the most responses is ') . implode(', ', o::get_env("best_proposals")) : l::g('Proposals with the most responses are ') . implode(', ', o::get_env("best_proposals")) ?></div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <br>
        	</div>
    	<?php } ?>
    	<?php t::inc('connected') ?>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>