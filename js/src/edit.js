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
$(document).ready(function () {
  hideOrShowField()
  testMinAttendees()

  $(document).on('change', '#edit_poll_type', function () {
    hideOrShowField()
  });


  if ($("#edit_cgu_accepted").prop('checked') || $('#edit_form .pure-button-submit').hasClass('accept_continue')) {
    $('#edit_form .pure-button-submit').prop('disabled', false);
  }
  else {
    $('#edit_form .pure-button-submit').prop('disabled', true);
  }
  $("#edit_cgu_accepted").change(function () {
    if (this.checked) {
      $('#edit_form .pure-button-submit').prop('disabled', false);
    }
    else {
      $('#edit_form .pure-button-submit').prop('disabled', true);
    }
  });

  $("#edit_poll_type").change(function () {
    var action = $('#edit_form').attr('action');
    var new_action = '_p=edit_' + $("#edit_poll_type").val();

    if (action.indexOf("_p=edit_date") != -1) {
      action = action.replace("_p=edit_date", new_action);
    } else if (action.indexOf("_p=edit_prop") != -1) {
      action = action.replace("_p=edit_prop", new_action);
    } else if (action.indexOf("_p=edit_rdv") != -1) {
      action = action.replace("_p=edit_rdv", new_action);
    }


    $('#edit_form').attr('action', action);
    if (poll.env.poll_type
      && poll.env.poll_type != $("#edit_poll_type").val()) {
      $('#warning_change_poll_type').show();
    } else {
      $('#warning_change_poll_type').hide();
    }
  });

  if($("#edit_poll_type").value == 'rdv'){
    let i = "ok";
  }
  reasonItems = [];
  displayReasonsManager()
  let reasons = document.querySelector('#reasons');
  let reasonsList = reasons.value.split(';');
  reasonsList.forEach(reason => {
    if(reason !== '')
      addReason(reason,reasonItems);
});
const addbutton = document.querySelector('.js-reason-add');
addbutton.addEventListener('click', event => {
  const input = document.querySelector('.js-reason-input');
  const text = input.value.trim();
  if(text !== ''){
    addReason(text,reasonItems);
    input.value = '';
    input.focus();
    updateReasons(reasonItems);
  }
})
const list = document.querySelector('.js-reason-list');
list.addEventListener('click', event => {
  if (event.target.classList.contains('js-delete-reason')){
    const itemKey = event.target.parentElement.dataset.key;
    deleteReason(itemKey);
    updateReasons(reasonItems);
  }
})
$('#addr_req').hide();
$('#phone_req').hide();
$("#address_asked").change(function () {
  if (this.checked) {
    $('#addr_req').show();
    document.getElementById("address_required").checked = true;
  }
  else {
    $('#addr_req').hide();
    document.getElementById("address_required").checked = false;
  }
});
$("#phone_asked").change(function () {
  if (this.checked) {
    $('#phone_req').show();
    document.getElementById("phone_required").checked = true;
  }
  else {
    $('#phone_req').hide();
    document.getElementById("phone_required").checked = false;
  }
});

});

// Permet de switcher de type de sondage lors d'un clic sur le span
function switch_poll_type(args) {
  var type = args.type ? args.type : 'date';
  $('#edit_poll_type').val(type);
  $('#edit_fieldset .poll_type span').removeClass('selected');
  $('#edit_fieldset .poll_type span.poll_type_' + type).addClass('selected');
  $("#edit_poll_type").change();
}

function update_select_poll_type(){
  var type = $('#edit_poll_type').val();
  $('#edit_fieldset .poll_type span').removeClass('selected');
  $('#edit_fieldset .poll_type span.poll_type_' + type).addClass('selected');
}

//Permet d'afficher les champs nécessaires en fonction du type de sondage
//Les champs sont configurés dans config/.../ihm.php
function hideOrShowField() {
  let showField = poll.env.ALL_FIELDS.filter(x => poll.env.SHOW_FIELDS[$('#edit_poll_type').val()].includes(x));
  let checkField = poll.env.ALL_FIELDS.filter(x => poll.env.CHECK_FIELDS[$('#edit_poll_type').val()].includes(x));
  let requiredField =  poll.env.REQUIRED_FIELDS[$('#edit_poll_type').val()];
  let notRequiredField =  poll.env.NOT_REQUIRED_FIELDS[$('#edit_poll_type').val()];

  showField.forEach(field => {
    $('label[for="' + field + '"]').show();
    $('#' + field).show();
    if (checkField.includes(field) && poll.env.action == "new") {
      $('#' + field).prop("checked", true);
      if (poll.env.mobile) {
        $('label[for="' + field + '"]').removeClass('ui-checkbox-off').addClass('ui-checkbox-on');
      }
    }
  });

  let hideField = poll.env.ALL_FIELDS.filter(x => !poll.env.SHOW_FIELDS[$('#edit_poll_type').val()].includes(x));
  hideField.forEach(field => {
    $('label[for="' + field + '"]').hide();
    $('#' + field).hide();
    $('#' + field).prop("checked", false);
  });

  if (requiredField) {
    requiredField.forEach(field => {
      console.log('required',field);
      $('#' + field).prop("required", true);
    });
  }

  if (notRequiredField) {
    notRequiredField.forEach(field => {
      $('#' + field).prop("required", false);
    });
  }
}


function testMinAttendees() {
  let message = '';
  let min = poll.env.MIN_ATTENDEES ? poll.env.MIN_ATTENDEES : 1;
  if (poll.env.MIN_ATTENDEES && poll.env.MIN_ATTENDEES > 1) {
    message = "Votre sondage possède déjà au moins " + poll.env.MIN_ATTENDEES + " participants sur l'une des propositions"
  }
  else {
    message = "Un sondage ne peut avoir moins d'un participant par proposition"
  }
  $("#edit_max_attendees_per_prop").find(':input').on('focusout', function () {
    if ($("#edit_max_attendees_per_prop").find(':input').val() < min && !$("#edit_max_attendees_per_prop").find(':input').val() == "") {
      $("#edit_max_attendees_per_prop").find(':input').val(min)
      $("#warning_max_attendees").text(message);
    }
    else {
      $("#warning_max_attendees").text("");
    }
  })
}

function displayReasonsManager() {
  var displayReason = document.querySelector('#enable_reason_checkbox');
  var dispValue = true;
  if (displayReason.checked == true)
    dispValue = false;
  var reasonManager = document.querySelector('.reasons-manager');
  reasonManager.hidden= dispValue;
}

function addReason(text, reasonItems) {
  const reason = {
    text,
    id: Math.random() * Date.now(),
  };
  reasonItems.push(reason);
  renderReason(reason);
}

function deleteReason(key){
  const index =  reasonItems.findIndex(item => item.id === Number(key));
  const reason = {
    deleted: true,
    ...reasonItems[index]
  };
  reasonItems = reasonItems.filter(item => item.id !== Number(key));
  renderReason(reason);
}

function updateReasons(reasonItems){
  reasonsList = "";
  reasonTable = [];
  reasonItems.forEach(reason => {
    reasonTable.push(reason.text);
  });
  reasonsList = reasonTable.join(';');
  document.querySelector("#reasons").value = reasonsList;

}

function renderReason(reason){
  const list = document.querySelector('.js-reason-list');
  const item = document.querySelector(`[data-key='${reason.id}']`);

  if (reason.deleted){
    item.remove();
    return
  }
  const node = document.createElement("li");
  node.setAttribute('class', 'reason-item');
  node.setAttribute('data-key', reason.id);
  node.innerHTML = `
    <span>${reason.text}</span>
    <button class="delete-reason js-delete-reason">
    X
    </button>
  `;
  list.append(node);
}

function hidePhone(){
  
}