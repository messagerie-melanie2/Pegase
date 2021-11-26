<?php

/**
 * Template pour la page de login de l'application de sondage
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
	<div class="background-image"></div>
	<div id="login_content">
		<div class="box-inner" role="main">
			<div class="box-left">
				<div class="productname"></div>
				<div class="productioninformation">
					Plateforme de communication réservée aux agents des ministères
				</div>
			</div>
			<div class="box-right">
				<?= t::inc('message') ?>
				<div id="title">
					<h1><?= l::g('Welcome to doodle of the MEDDE') ?></h1>
					<legend><?= l::g('Log-in to create new poll and list all your polls') ?></legend>
				</div>
				<div id="login">
					<form action="<?= o::url("login") ?>" method="post" class="pure-form pure-form-aligned">
						<table id="formlogintable">
							<tbody>
								<tr class="">
									<td class="title"><label for="username">Nom d'utilisateur</label>
									</td>
									<td class="input"><input name="username" id="username" size="40" value="<?= r::getInputValue('username', POLL_INPUT_POST) ?>" type="text" required x-moz-errormessage="<?= l::g('You have to put your username') ?>" autofocus required></td>
								</tr>
								<tr>
									<td class="title"><label for="password">Mot de passe</label>
									</td>
									<td class="input"><input name="password" id="password" size="40" autocomplete="off" type="password" x-moz-errormessage="<?= l::g('You have to put your password') ?>" required></td>
								</tr>
							</tbody>
						</table>
						<input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>" />
						<p class="formbuttons">
							<input class="button mainaction" type="submit" value="<?= l::g('Connect') ?>">
						</p>
					</form>
					<div class="footerbox">
						<?= l::g('Respect the principles of labeling in force on the internet, namely a set of rules of good manners concerning the use of the internet') ?>
					</div>
				</div>
				<?php t::inc('copyright') ?>
			</div>
		</div>
	</div>
	<div id="loginfooter">
		<span>
			<?= l::g('© Ministry of the Ecological and Inclusive Transition, © Ministry of Territorial Cohesion and Relations with Local Authorities, © Ministry of Agriculture and Food') ?>
		</span>
		<span>
			<?= l::g('SNUM - Digital service') ?>
		</span>
	</div>
</body>
<?php t::inc('foot') ?>