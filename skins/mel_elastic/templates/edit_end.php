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
use Program\Lib\Request\Template as t;
use Program\Lib\Request\Request as r;
use Program\Lib\Templates\Edit_end as e;
use Program\Data\Poll as p;
?>
<?php t::inc('head') ?>
<body>
<div id="prevcontent">
	<?php t::inc('toolbar') ?>
    <div id="content" class="edit_end">
      <?php t::inc('message') ?>
      <h1><?= l::g('Poll created with success') ?></h1>
      <div id="head" class="<?= o::tohtml(p::get_current_poll()->type) ?>">
    			<div class="poll_title" title="<?= l::g('Created by') . ' ' . o::tohtml(o::get_env("poll_organizer")->fullname) . ' ' . o::date_format(strtotime(p::get_current_poll()->created)) . '. ' . l::g('Last modification time') . ' ' . o::date_format(strtotime(p::get_current_poll()->modified)) ?>"><?= o::tohtml(p::get_current_poll()->title) ?></div>
    			<div class="poll_location"><?= o::tohtml(p::get_current_poll()->location) ?></div>
    			<?php if (!empty(p::get_current_poll()->description)) { ?>
      			<div class="poll_description">
      				<span class="label"><?= l::g('Description') ?> :</span>
      				<span class="description"><?= o::tohtml(p::get_current_poll()->description) ?></span>
          	</div>
        	<?php } ?>
        	<div class="poll_url">
        		<span class="url"><?= e::GetPublicUrl(true) ?></span>
        		<input type="text" id="input_url" value="<?= o::get_poll_url() ?>">
        		<button class="copy_url customtooltip_bottom" title="<?=l::g('Clic here to copy URL')?>" onclick="poll.command(copy_url)"><?= l::g('Copy URL') ?></button>
					</div>
    		</div>
    		<div class="buttons">
    			<span class="left_button"><a class="pure-button" href="<?= o::url(null, null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Open the poll') ?></a></span>
    		</div>
    </div>
    <?php t::inc('copyright') ?>
</div>
</body>
<?php t::inc('foot') ?>