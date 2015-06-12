/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL Cette
 * application est un doodle-like permettant aux utilisateurs d'effectuer des
 * sondages sur des dates ou bien d'autres criteres
 * 
 * L'application est écrite en PHP5,HTML et Javascript et utilise une base de
 * données postgresql et un annuaire LDAP pour l'authentification
 * 
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 * 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// Utilisé à la fin de la création d'un sondage
// Permet d'inviter des participants au sondage
$(document).ready(function() {
  //Charge la liste des participants
  if (poll.env.attendees_list) {
    for(var k in poll.env.attendees_list) {
      addUserAttendee(
          poll.env.attendees_list[k].fullname, 
          poll.env.attendees_list[k].email);
    }
  }
  else {
    $('#notify_attendees').hide();
  }
  // Suppression d'un invité
  $(document).on('click', "#attendees-list .attendees-list-item .attendees-list-item-delete", function() {
    var parent = $(this).parent();
    parent.remove();
  });
  // Validation de l'autocompletion
  $(document).on('click', "form.autocomplete button.button-validate-autocomplete", function(event) {
    if ($("form.autocomplete .autocomplete-results .autocomplete-results-item").size() == 0) {
      AutocompleteCallback();
      $("form.autocomplete .autocomplete-results").html("");
      $("form.autocomplete .autocomplete-results").hide();
    }
  });
  // Envoie de l'invitation
  $(document).on('click', "#notify_attendees .button-send-invitation", function(event) {
    $(this).addClass('wait');
    poll.show_loading(poll.labels['Sending invitation...']);
    var attendees_list = [];
    // Parcours les invités pour créer l'objet
    $("#attendees-list .attendees-list-item").each(function () {
      attendees_list.push({
        'fullname': $(this).find('.attendees-list-item-fullname').text(),
        'email': $(this).find('.attendees-list-item-email').text(),
      });
    });
    // Appel de l'envoi de l'invitation en ajax
    $.ajax({
      type : 'POST',
      url : '?_p=ajax&_a=send_invitation',
      data : {
        token : poll.env.csrf_token,
        poll_uid : poll.env.poll_uid,
        _attendees_list : attendees_list,        
      },
      success : function(data) {
        if (data.success == true) {
          $('#notify_attendees').html(poll.labels['Invitation has been sent']);
          $('#notify_attendees').addClass('success');            
        }
        else {
          $(this).removeClass('wait');
          poll.show_message(data.message, 'error');
        }
        poll.hide_loading();
      },
      error : function(o, status, err) {
        $(this).removeClass('wait');
        poll.hide_loading();
        poll.show_message('Error', 'error');
      }
    });
  });
});

/**
 * Fonction de callback après l'appel de l'autocomplétion
 * @param element
 */
function AutocompleteCallback(element) {
  if (!element) {
    if ($("form.autocomplete input.autocomplete").val() == "") {
      return;
    }
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    var email = $("form.autocomplete input.autocomplete").val();
    if (re.test(email)) {
      var tmp = email.split('@')[0].split('.');
      var fullname = '';
      for(var i in tmp) {
        if (fullname != "") {
          fullname += " ";
        }
        var f = tmp[i].charAt(0).toUpperCase();
        fullname += f + tmp[i].substr(1);
      }
      $("form.autocomplete input.autocomplete").val("");
    }
    else {
      poll.show_message(poll.labels['This is not a valid email'], 'error');
      return;
    }
  }
  else {
    var fullname = element.find('.autocomplete-results-item-name').text();
    var email = element.find('.autocomplete-results-item-email').text();
  }
  // Ajoute à l'interface
  addUserAttendee(fullname, email);
}

/**
 * Ajoute un nouveau participant à inviter
 * @param fullname
 * @param email
 */
function addUserAttendee(fullname, email) {
  var exists = false;
  $('#attendees-list .attendees-list-items ul .attendees-list-item .attendees-list-item-email')
      .each(function() {
        if (email == $(this).text()) {
          exists = true;
          return;
        }
      });
  if (!exists) {
    $('#attendees-list .attendees-list-items ul')
        .append('<li class="attendees-list-item">'
            + '<span class="attendees-list-item-delete">x</span>'
            + '<div class="attendees-list-item-fullname">' + fullname + '</div>'
            + '<div class="attendees-list-item-email">' + email + '</div>'
            + '</li>');
  }
}