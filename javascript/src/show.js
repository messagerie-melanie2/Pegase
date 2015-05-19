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
	// Masque automatiquement les participants
	if ($(".prop_others_users_elements").length > 6) {
		hide_attendees(null);
	}
	$(document).on('click', "#proposals_table .prop_not_responded", function() {
	  if (poll.env.poll_if_needed) {
	    return;
	  }
		var checkbox = $(this).find('input:checkbox');
		if (checkbox.attr('checked')) {
			$(this).css('background-color', '#ffffff');
			checkbox.attr('checked', false);
		} else {
			$(this).css('background-color', '#E4EBF5');
			checkbox.attr('checked', 'checked');
		}
		$(this).html($(this).html());
	});
	$(document).on('click', "#proposals_table .prop_accepted", function() {
	  if (poll.env.poll_if_needed) {
      return;
    }
		var checkbox = $(this).find('input:checkbox');
		if (checkbox.attr('checked')) {
			checkbox.attr('checked', false);
		} else {
			checkbox.attr('checked', 'checked');
		}
		$(this).html($(this).html());
	});
	$(document).on('click', "#proposals_table .prop_refused", function() {
	  if (poll.env.poll_if_needed) {
      return;
    }
		var checkbox = $(this).find('input:checkbox');
		if (checkbox.attr('checked')) {
			checkbox.attr('checked', false);
		} else {
			checkbox.attr('checked', 'checked');
		}
		$(this).html($(this).html());
	});
	$( "#button_delete_poll" ).click(function() {
		return confirm(poll.labels['Are you sure you want to delete the poll ?']);
	});
	$( "#button_delete_response" ).click(function() {
		return confirm(poll.labels['Are you sure you want to delete your response ?']);
	});
	$( "#proposals_form" ).submit(function() {
		var name = $( "#user_username" ).val();
		var _this = $( "#user_username" );
		var isValide = true;
		$('.user_list_name').each(function() {
			if ($(this).html() == name
					&& !_this.attr('readonly')) {
				isValide = false;
				poll.show_message(poll.labels['Name already exists'], "error");
				return false;
			}
		});
		return isValide;
	});
	$( "#user_username" ).focusin(function() {
		$("#div_show_more_inputs").show();
		$(".prop_row_nb_props .hide_attendees_button").remove();
		$(".prop_row_nb_props .show_attendees_button").remove();
	});
	$("#proposals_form").submit(function(event) {
	  if (poll.env.poll_type == 'date'
	      && poll.env.user_auth
	      && !$("input[name='hidden_modify_all']").length
	      && poll.env.add_to_calendar
	      && confirm(poll.labels['Would you like to add responses to your calendar as tentative ?'])) {
		poll.show_loading(poll.labels['Adding prop to your calendar...']);
	    event.preventDefault();
	    var prop_keys = [];
	    if (poll.env.poll_if_needed) {
	      $("#proposals_form input:radio").each(function() {
          if ($(this).prop('checked') 
              && $(this).attr('value') != '') {
            prop_keys.push($(this).attr('name').replace('check_', ''));
          }
        });  
	    }
	    else {
	      $("#proposals_form input:checkbox").each(function() {
	        if ($(this).attr('checked')) {
	          prop_keys.push($(this).attr('name').replace('check_', ''));
	        }
	      });  
	    }
	    var _this = $(this);
	    $.ajax({
        type: 'POST', 
        url: '?_p=ajax&_a=add_tentative_calendar', 
        data: {
          token: poll.env.csrf_token,
          prop_keys: prop_keys,
          poll_uid: poll.env.poll_uid
        },
        success: function(data) {
          _this.unbind('submit').submit();
          poll.hide_loading();
        },
        error: function(o, status, err) {
          _this.unbind('submit').submit();
          poll.hide_loading();
        }
      });
	  }
	});
});

// Function pour valider/dévalider une proposition
// Utilise de l'ajax via jquery
function show_validate_prop(args)
{	
	$(document).ready(function() {
		if (args.action == "validate_prop") {
			args.params.send_mail = poll.env.send_mail && confirm(poll.labels['Do you want to send a message to the attendees ?']);
		}
		var html = $("#proposals_table #validate_prop_" + args.params.prop_key).html();
		$("#proposals_table #validate_prop_" + args.params.prop_key).html("");
		$("#proposals_table #validate_prop_" + args.params.prop_key).addClass("wait");
		return $.ajax({
	      type: 'POST', url: args.url, data: args.params,
	      success: function(data) {
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).html(html);
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).removeClass("wait");
	    	  if (data.success == true) {
	    		  poll.show_message(data.message, "success");
	    		  if (args.action == "validate_prop") {
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key).addClass("validate_prop");
	    			  $("#proposals_table #prop_header_" + args.params.prop_key).addClass("validate_prop_header");
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-unvalidate-prop").show();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").show();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-validate-prop").hide();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-unvalidate-prop").attr("title", poll.labels['Clic to unvalidate this proposal']);
	    			  if ($("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").hasClass("pure-button-disabled")) {
	    				  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").attr("title", poll.labels['This proposals is already in your calendar']);
	    			  } else {
	    				  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").attr("title", poll.labels['Clic to add this proposal to your calendar']);
	    			  }
	    		  } else {
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key).removeClass("validate_prop");
	    			  $("#proposals_table #prop_header_" + args.params.prop_key).removeClass("validate_prop_header");
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-unvalidate-prop").hide();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").hide();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-validate-prop").show();
	    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-validate-prop").attr("title", poll.labels['Clic to validate this proposal']);
	    		  }
	    		  get_valid_proposals_text(args);
	    	  } else {
	    		  poll.show_message(data.message, "error");
    		  }
    	  },
	      error: function(o, status, err) {
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).html(html);
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).removeClass("wait");
	    	  poll.show_message(err, "error"); 
    	  }
	    });
	});
}

//Function pour ajouter une proposition dans l'agenda de l'utilisateur
//Utilise de l'ajax via jquery
function show_add_to_calendar(args)
{	
	poll.show_loading(poll.labels['Adding prop to your calendar...']);
	$(document).ready(function() {
		var html = $("#proposals_table #validate_prop_" + args.params.prop_key).html();
		$("#proposals_table #validate_prop_" + args.params.prop_key).html("");
		$("#proposals_table #validate_prop_" + args.params.prop_key).addClass("wait");
		return $.ajax({
	      type: 'POST', url: args.url, data: args.params,
	      success: function(data) {
	    	  poll.hide_loading();
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).html(html);
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).removeClass("wait");
	    	  if (data.success == true) {
	    		  poll.show_message(data.message, "success");
    			  $("#proposals_table #validate_prop_" + args.params.prop_key + " .pure-button-calendar").remove();
	    	  } else {
	    		  poll.show_message(data.message, "error");
	    	  }
	 	  },
	      error: function(o, status, err) {
	    	  poll.hide_loading();
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).html(html);
	    	  $("#proposals_table #validate_prop_" + args.params.prop_key).removeClass("wait");
	    	  poll.show_message(err, "error"); 
	 	  }
	    });
	});
}

//Function pour ajouter une proposition dans l'agenda de l'utilisateur
//Utilise de l'ajax via jquery
function get_valid_proposals_text(args)
{
	$(document).ready(function() {
		return $.ajax({
	      type: 'POST', url: './?_p=ajax&_a=get_valid_proposals_text', data: args.params,
	      success: function(data) {
	    	  if (data.success == true) {
	    		  if (data.text != "") {
	    			  $(".best_proposals").hide();
	    			  $(".validate_proposals").show();
		    		  $(".validate_proposals").text(data.text);
	    		  } else {
	    			  $(".validate_proposals").hide();
	    			  $(".best_proposals").show();
	    		  }	    		  
	    	  }
	 	  },
	      error: function(o, status, err) {
	 	  }
	    });
	});
}

// Masque la liste des participants
function hide_attendees(args)
{
	$(document).ready(function() {
		$(".prop_others_users_elements").hide();
		$(".hide_attendees_button").hide();
		$(".show_attendees_button").show();
	});
}

//Affiche la liste des participants
function show_attendees(args)
{
	$(document).ready(function() {
		$(".prop_others_users_elements").show();
		$(".hide_attendees_button").show();
		$(".show_attendees_button").hide();
	});
}

var nb_new_attendee = 0;

// Ajoute un nouveau champ pour ajouter un participant
function add_attendee(args) {
	$(document).ready(function() {
		nb_new_attendee++;
		var td = '';		
		$('#proposals_table thead td.prop_header').each(function() {
			var prop = $(this).attr('id').replace('prop_header_', '');
			if (poll.env.poll_if_needed) {
			  td += '<td class="prop_not_responded" align="center">';
			  td += '<input id="newradio--' + nb_new_attendee + '--' + prop + '" name="newradio--' + nb_new_attendee + '--' + prop + '" value="' + poll.env.proposals[prop] + '" type="radio">';
			  td += '<label for="newradio--' + nb_new_attendee + '--' + prop + '">' + poll.labels['Yes'] + '</label>';
			  td += '<br>';
			  td += '<input id="newradio--if_needed' + nb_new_attendee + '--' + prop + '" name="newradio--' + nb_new_attendee + '--' + prop + '" value="' + poll.env.proposals[prop] + ':if_needed" type="radio">';
        td += '<label for="newradio--if_needed' + nb_new_attendee + '--' + prop + '">' + poll.labels['If needed'] + '</label>';
        td += '<br>';
        td += '<input id="newradio--declined' + nb_new_attendee + '--' + prop + '" name="newradio--' + nb_new_attendee + '--' + prop + '" value="" type="radio">';
        td += '<label for="newradio--declined' + nb_new_attendee + '--' + prop + '">' + poll.labels['No'] + '</label>';
			  td += '</td>'; 
			}
			else {
			  td += '<td class="prop_not_responded" align="center"><input id="newcheck--' + nb_new_attendee + '--' + prop + '" name="newcheck--' + nb_new_attendee + '--' + prop + '" value="' + poll.env.proposals[prop] + '" type="checkbox"></td>';  
			}			
		});
		var first_col = '<td class="first_col"><input style="width: 100%;" class="newuser" id="newuser--' + nb_new_attendee + '" name="newuser--' + nb_new_attendee + '" placeholder="' + poll.labels['Username'] + '" required="required" type="text"><input style="width: 100%;" id="newemail--' + nb_new_attendee + '" class="newemail" name="newemail--' + nb_new_attendee + '" placeholder="' + poll.labels['Email address'] + '" type="text"></td>';
		var last_col = '<td class="prop_cell_nobackground last_col" align="center"><a onclick="poll.command(remove_attendee, {_this: this});" class="remove_attendee_button customtooltip_bottom tooltipstered">' + poll.labels['Remove'] + '</a></td>';
		var insert = '<tr class="prop_new_element">' + first_col + td + last_col + '</tr>';
		
		$('#proposals_table tr.prop_row_nb_props').before(insert);
	});
}

// Supprime le champ d'ajout d'un participant
function remove_attendee(args) {
	$(document).ready(function() {
		$(args._this).parents('tr').remove();
	});
}

// Coche toutes les checkboxes de la ligne
function check_all(args) {
	$(document).ready(function() {
		var tr = $('.check_all_button').parents('tr');
		tr.find('input:checkbox').attr('checked', 'checked');
		if (tr.hasClass('prop_row_new_response')) {
			tr.find('td.prop_not_responded').css('background-color', '#E4EBF5');
		}
		tr.html(tr.html());
	});
}

// Décoche toutes les checkboxes de la ligne
function uncheck_all(args) {
	$(document).ready(function() {
		var tr = $('.uncheck_all_button').parents('tr');
		tr.find('input:checkbox').attr('checked', false);
		if (tr.hasClass('prop_row_new_response')) {
			tr.find('td.prop_not_responded').css('background-color', '#ffffff');
		}
		tr.html(tr.html());
	});
}

// Valide tous les boutons radio à yes
function yes_to_all(args) {
  $(document).ready(function() {
    var tr = $('.yes_to_all_button').parents('tr');
    tr.find('input:radio').each(function() {
      if ($(this).attr('value').length 
          && $(this).attr('value').indexOf(":if_needed") == -1) {
        $(this).attr('checked', 'checked');
      }
      else {
        $(this).attr('checked', false);
      }
    });
    tr.html(tr.html());
  });
}

//Valide tous les boutons radio à if_needed
function if_needed_to_all(args) {
  $(document).ready(function() {
    var tr = $('.if_needed_to_all_button').parents('tr');
    tr.find('input:radio').each(function() {
      if ($(this).attr('value').length 
          && $(this).attr('value').indexOf(":if_needed") != -1) {
        $(this).attr('checked', 'checked');
      }
      else {
        $(this).attr('checked', false);
      }
    });
    tr.html(tr.html());
  });
}

//Valide tous les boutons radio à no
function no_to_all(args) {
  $(document).ready(function() {
    var tr = $('.no_to_all_button').parents('tr');
    tr.find('input:radio').each(function() {
      if (!$(this).attr('value').length) {
        $(this).attr('checked', 'checked');
      }
      else {
        $(this).attr('checked', false);
      }
    });
    tr.html(tr.html());
  });
}