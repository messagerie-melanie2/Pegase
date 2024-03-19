<?php
/**
 * Template pour la gestion des erreurs
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
?>
<div class="message">
<?php if (o::isset_env("message")) { ?>
    <div class="success"><?= l::g(o::get_env("message")) ?></div>
<?php } ?>
<?php if (o::isset_env("error")) { ?>
    <div class="error"><?= l::g(o::get_env("error")) ?></div>
<?php } ?>
</div>
<div class="fixedmessage">
  <?= l::g('fixed message') ?>
</div>

<div class="loading">
	<div class="loading_message">
		Loading...
	</div>
</div>