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

// Gestion des données non enregistrés
var hasChanged = false;
var formSubmitting = false;
var setFormSubmitting = function() { window.formSubmitting = true; };
var isDirty = function() { return window.hasChanged; }

// 0005079: Lors de l'édition des propositions, prévenir si les props ne sont pas enregistré au changement de page
window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        if (window.formSubmitting || !isDirty()) {
            return undefined;
        }

        var confirmationMessage = poll.labels['Proposals have changed, if you leave the page, the changes will be lost'];

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
};

/**
 * Ajoute le div de proposition générique
 * @param value
 */
function addPropDiv(value) {
	// Passer la valeur en a changé
    window.hasChanged = true;
	// Gets the number of elements with class yourClass
	poll.env.nb_prop++;
	id = "edit_prop" + poll.env.nb_prop;
	var html = '<div class="pure-control-group">';
	html += '<label style="width: 35%;" for="'+id+'">'+poll.labels['Edit proposition']+'</label>';
	html += ' <input id="'+id+'" type="text" name="'+id+'" class="edit_prop" placeholder="'+poll.labels['Edit proposition']+'" value="'+value+'">';
	html += ' <a class="pure-button pure-button-delete-date" style="padding-top: 3px;" onclick="deletePropDiv(\''+id+'\');" href = "#"></a>';
	html += '<br></div>';
	$('#props_list').append(html);
}

/**
 * Suppression d'une date dans la liste
 * Supprime l'évènement en même temps
 * @param id
 * @returns
 */
function deletePropDiv(id) {
  // Passer la valeur en a changé
  window.hasChanged = true;
  $('#'+id).parent().remove();
  return false;
}

$(document).ready(function() {
	$( "#add_new_prop" ).click(function() {
		addPropDiv('');
		if ($('#edit_prop_page').length) {
		  $("#edit_prop_page").trigger('create');
		}
		return false;
	});
	$( ".change_type_poll" ).click(function () {
		return confirm(poll.labels['Are you sure ? Not saved proposals are lost']);
	});
	$('input.edit_prop').on('input', function() {
		if ($(this).val().length) {
			// Passer la valeur en a changé
			window.hasChanged = true;
		}
	});
});
