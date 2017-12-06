<?php
/**
 * Template pour la page de creation de l'utilisateur pour l'application de sondage
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
use Program\Lib\Request\Session as s;
?>
<?php t::inc('head') ?>
    <body>
    <div id="prevcontent">
        <div id="register_content">
            <?php t::inc('message') ?>
            <div id="title">
                <h1><?= l::g('Application name') ?></h1>
            </div>
            <div id="register">
                <form action="<?= o::url("register") ?>" method="post" class="pure-form pure-form-aligned" autocomplete="on">
                    <fieldset>
                        <legend><?= l::g('Register in the app') ?></legend>
                        <div class="pure-control-group">
                            <label style="width: 150px;" for="fullname"><?= l::g('Fullname') ?><span style="color: red;">*</span></label>
                            <input style="width: 200px;" id="fullname" type="text" name="fullname"
                                   value="<?= r::getInputValue('fullname', POLL_INPUT_POST) ?>"
                                   placeholder="<?= l::g('Fullname') ?>" required x-moz-errormessage="<?= l::g('You have to put your fullname') ?>" autofocus/>
                        </div>
                        <div class="pure-control-group">
                            <label style="width: 150px;" for="email"><?= l::g('Email') ?><span style="color: red;">*</span></label>
                            <input style="width: 200px;" id="email" type="text" name="email"
                                   value="<?= r::getInputValue('email', POLL_INPUT_POST) ?>"
                                   placeholder="<?= l::g('Email') ?>" required x-moz-errormessage="<?= l::g('You have to put your email') ?>" autofocus/>
                        </div>
                        <div class="pure-control-group">
                            <label style="width: 150px;" for="username"><?= l::g('Username') ?><span style="color: red;">*</span></label>
                            <input style="width: 200px;" id="username" type="text" name="username"
                                   value="<?= r::getInputValue('username', POLL_INPUT_POST) ?>"
                                   placeholder="<?= l::g('Username') ?>" required x-moz-errormessage="<?= l::g('You have to put your username to register') ?>" autofocus/>
                        </div>
                        <div class="pure-control-group">
                            <label style="width: 150px;" for="password"><?= l::g('Password') ?><span style="color: red;">*</span></label>
                            <input style="width: 200px;" id="password" type="password" name="password"
                                   placeholder="<?= l::g('Password') ?>" x-moz-errormessage="<?= l::g('You have to put your password to register') ?>" required />
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
                        <div class="pure-controls" style="margin-left: 30%;">
                            <button type="submit" class="pure-button pure-button-submit"><?= l::g('Register') ?></button>
                            <a href="<?= o::url("main") ?>"><?= l::g('Back') ?></a>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <?php t::inc('login_copyright') ?>
    </div>
    </body>
<?php t::inc('foot') ?>