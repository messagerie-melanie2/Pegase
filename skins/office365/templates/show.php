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
<?php t::inc('header') ?>
<div id="prevcontent">
	<?php t::inc('left_panel') ?>
    <div id="content">
        <?php t::inc('message') ?>
        <?php if (p::isset_current_poll()) { ?>
            <div id="title">
                <h1><?= o::tohtml(p::get_current_poll()->title) ?></h1>
            </div>
            <div id="edit">
                <div class="poll_information">
                    <div class="pure-control-group">
                    	<label style="width: 35%;"><?= l::g('URL to the poll') ?> : </label>
                    	<?= e::GetPublicUrl() ?>
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
                    <div>
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
    	<br>
    </div>
</div>
</body>
<?php t::inc('foot') ?>