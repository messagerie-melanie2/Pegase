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
use Program\Lib\Request\Output as o;
use Program\Data\User as u;
use Program\Lib\Request\Localization as l;
?>
<div data-role="footer">
  <!-- <h6><?= l::g('copyright') ?> - Version <?= VERSION.'-'.BUILD ?></h6> -->
  <a class="ui-btn" href="<?= (o::get_env("page") == "show" ? o::url(null, ACT_DESKTOP, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid)) : o::url(null, ACT_DESKTOP)) ?>">Version <?= l::g("Desktop") ?></a>
</div>
