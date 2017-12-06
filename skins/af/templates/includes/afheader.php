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
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Output as o;
?>

<div id="header">
	<a href="<?= Config\IHM::$AF_SERVICES_URL ?>"><img title="" alt="" src="skins/<?= o::get_env("skin") ?>/images/banniere_federateur.png" id="logo" height="30px"></a>
	<span id="header-buttons">
	    <span class="header-button"><a class="pure-button pure-button-ldap" title="Ldap" href="<?= Config\IHM::$AF_SERVICES_URL.Config\IHM::$ANNUAIRE_URL ?>"><img alt="" src="skins/<?= o::get_env("skin") ?>/images/1377179872_active_directory.png" height="20px"/> LDAP</a></span>
		<span class="header-button"><a target="_blank" class="pure-button pure-button-im" title="IM" href="<?= Config\IHM::$AF_SERVICES_URL.Config\IHM::$IM_URL ?>"><img alt="" src="skins/<?= o::get_env("skin") ?>/images/1377179099_IM.png" height="20px"/> IM</a></span>
		<span class="header-button"><a target="_blank" class="pure-button pure-button-owncloud" title="ownCloud" href="<?= Config\IHM::$AF_SERVICES_URL.Config\IHM::$OWNCLOUD_URL ?>"><img alt="" src="skins/<?= o::get_env("skin") ?>/images/owncloud.png" height="20px"/>&nbsp;</a></span>
		<span class="header-button"><a class="pure-button pure-button-poll pure-button-disabled" title="<?= l::g('Poll', false) ?>"><img alt="" src="skins/<?= o::get_env("skin") ?>/images/1395868331_519660-164_QuestionMark.png" height="20px"/> <?= l::g('Poll', false) ?></a></span>
	</span>
</div>