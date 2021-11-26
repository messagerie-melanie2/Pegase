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
use Program\Data\Poll as p;
use Program\Data\User as u;
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Session as s;
?>
<?php if (u::isset_current_user()) { ?>
<div class="toolbar">
	<a class="button listpoll customtooltip_bottom" title="<?= l::g("Go back to poll list", false) ?>" href="<?= o::url() ?>"><?= l::g('List poll') ?></a>
    <a class="button addpoll customtooltip_bottom" title="<?= l::g("Create a new poll", false) ?>" href="<?= o::url("edit", ACT_NEW) ?>"><?= l::g('New poll') ?></a>
    <?php if (o::get_env("page") == 'show'
			&& p::isset_current_poll()
      && !\Program\Data\Poll::get_current_poll()->deleted) { ?>
		<a class="button refreshpoll customtooltip_bottom" title="<?= l::g("Clic to refresh the poll", false) ?>" href="<?= o::url(null, null, array('u' => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Refresh poll') ?></a>
		<?php if (p::get_current_poll()->organizer_id == u::get_current_user()->user_id) { ?>
			<a id="button_edit_poll" title="<?= l::g("Clic to edit the poll", false) ?>" class="button editpoll customtooltip_bottom" style="width: 16%;" href="<?= o::url("edit", ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify poll') ?></a>
	        <a id="button_edit_prop_poll" title="<?= l::g("Clic to change poll proposals", false) ?>" class="button editproppoll customtooltip_bottom" style="width: 16%;" href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify propositions') ?></a>
	        <a id="button_modify_responses_poll" title="<?= l::g("Clic to change everybody responses", false) ?>" class="button modifyresponsespoll customtooltip_bottom" style="width: 16%;" href="<?= o::url(null, ACT_MODIFY_ALL, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify responses') ?></a>
	        <a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="button <?= (p::get_current_poll()->locked === 0 ? "lockpoll" : "unlockpoll") ?> customtooltip_bottom" style="width: 16%;" href="<?= o::url(null, (p::get_current_poll()->locked === 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= (p::get_current_poll()->locked === 0 ? l::g('Lock') : l::g('Unlock')) ?></a>
	        <a id="button_delete_poll" title="<?= l::g("Clic to delete the poll", false) ?>" class="button deletepoll customtooltip_bottom" style="width: 16%;" href="<?= o::url("main", ACT_DELETE, array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= l::g('Delete poll') ?></a>
	        <a id="button_export_csv" title="<?= l::g("Clic to export in CSV", false) ?>" class="button exportpoll customtooltip_bottom" style="width: 16%;" href="<?= o::url(null, ACT_DOWNLOAD_CSV, array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= l::g('Export CSV') ?></a>
        <?php } ?>
	<?php } elseif (o::get_env("page") == 'edit'
					&& p::isset_current_poll()
                    && o::get_env("action") != ACT_NEW) { ?>
            <a class="button showpoll customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>"  style="width: 15%;" href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
            <a id="button_edit_prop_poll" title="<?= l::g("Clic to change poll proposals", false) ?>" class="button editproppoll customtooltip_bottom" style="width: 16%;" href="<?= o::url("edit_".p::get_current_poll()->type, ACT_MODIFY, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Modify propositions') ?></a>
            <a id="button_lock_poll" title="<?= l::g("Clic to lock the poll", false) ?>" class="button <?= (p::get_current_poll()->locked === 0 ? "lockpoll" : "unlockpoll") ?> customtooltip_bottom" style="width: 16%;" href="<?= o::url("edit", (p::get_current_poll()->locked === 0 ? ACT_LOCK : ACT_UNLOCK), array("u" => p::get_current_poll()->poll_uid, "t" => s::getCSRFToken())) ?>"><?= (p::get_current_poll()->locked === 0 ? l::g('Lock') : l::g('Unlock')) ?></a>
    <?php } elseif (o::get_env("page") == 'edit_date' || o::get_env("page") == 'edit_prop') { ?>
    	<a class="button editpoll customtooltip_bottom" title="<?= l::g("Clic to get back to the poll modification", false) ?>" style="width: 25%;" href="<?= o::url("edit", ACT_MODIFY, array('u' => p::get_current_poll()->poll_uid)) ?>"><?= l::g('Return to the edit page of poll') ?></a>
		<?php if (p::isset_current_poll()
                    && o::get_env("action") != ACT_NEW) { ?>
            <a class="button showpoll customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>" style="width: 25%;" href="<?= o::url(null, null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
        <?php }?>
    <?php } elseif (o::get_env("page") == 'edit_end' && p::isset_current_poll()) { ?>
        <a class="button showpoll customtooltip_bottom" title="<?= l::g("Clic to view the poll", false) ?>"  style="width: 15%;" href="<?= o::url("show", null, array("u" => p::get_current_poll()->poll_uid)) ?>"><?= l::g('See the poll') ?></a>
    <?php }?>
    <a id="help-page-button" class="button help customtooltip_bottom" title="<?= l::g("View help of the page", false) ?>" href="#"><?= l::g('Help') ?></a>
</div>
<?php } ?>
