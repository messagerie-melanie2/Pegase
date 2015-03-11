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
$(document).ready(function() {
	$( "#edit_poll_type" ).change(function() {
		var action = $('#edit_form').attr('action');
		var new_action = '_p=edit_' + $( "#edit_poll_type" ).val();
		if (action.indexOf("_p=edit_date") != -1) {
			action = action.replace("_p=edit_date", new_action);
		} else if (action.indexOf("_p=edit_prop") != -1) {
			action = action.replace("_p=edit_prop", new_action);
		}
		$('#edit_form').attr('action', action);
		if (poll.env.poll_type
				&& poll.env.poll_type != $( "#edit_poll_type" ).val()) {
			$('#warning_change_poll_type').show();
		} else {
			$('#warning_change_poll_type').hide();
		}
	});
});
