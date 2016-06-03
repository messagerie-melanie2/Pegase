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
$(document)
    .ready(function() {
      // Masque automatiquement les participants
      if ($(".prop_others_users_elements").length > 6) {
        hide_attendees(null);
      }
      var ajax_freebusy = true;
      // Utiliser ajax pour des actions spécifiques
      if (poll.env.action) {
        if (poll.env.action == 'delete_tentatives'
            && poll.env.request_source == "mail" && poll.env.user_auth) {
          ajax_freebusy = false;
          setTimeout(function() {
            delete_tentatives({});
          }, 300);
        }
        else if (poll.env.action == 'add_calendar'
            && poll.env.request_source == "mail") {
          if (poll.env.user_auth) {
            ajax_freebusy = false;
            setTimeout(function() {
              // Lance la command qui appel l'ajax
              show_add_to_calendar({
                url : '?_p=ajax&_a=' + poll.env.action,
                action : poll.env.action,
                params : {
                  prop_key : poll.env.prop_key,
                  poll_uid : poll.env.poll_uid,
                  token : poll.env.csrf_token,
                  part_status : poll.env.part_status,
                }
              });
            }, 300);
          }
          else {
            window.location.href = "?_a=download_ics&_prop="
                + poll.env.prop_key + "&_u=" + poll.env.poll_uid + "&_t="
                + poll.env.csrf_token;
          }
        }
      }
      // Chargement des freebusy au chargement de la page
      if ($('table#proposals_table tr.prop_row_freebusy').length
          && ajax_freebusy) {
        refresh_freebusy();
      }
      // Affichage des pop up au chargement de la page
      if (poll.env.show_lock_popup) {
        setTimeout(function() {
          poll.popup('lock_poll_popup');
        }, 600);
      }
      else if (poll.env.show_validate_prop_popup) {
        setTimeout(function() {
          poll.popup('validate_prop_popup');
        }, 200);
      }
      else if (poll.env.show_add_calendar_popup) {
        setTimeout(function() {
          poll.popup('add_to_calendar_popup');
        }, 200);
      }

      // Click sur les checkboxes
      $(document)
          .on('click', "#proposals_table .prop_not_responded", function() {
            if (poll.env.poll_if_needed) {
              return;
            }
            var checkbox = $(this).find('input:checkbox');
            if (checkbox.attr('checked')) {
              $(this).css('background-color', '#ffffff');
              checkbox.attr('checked', false);
            }
            else {
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
        }
        else {
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
        }
        else {
          checkbox.attr('checked', 'checked');
        }
        $(this).html($(this).html());
      });
      // Click sur le bouton de suppression du sondage
      $("#button_delete_poll")
          .click(function(event) {
            event.preventDefault();
            var _this = $(this);
            poll
                .confirm('<div>'
                    + poll.labels['Are you sure you want to delete the poll ?']
                    + '</div>'
                    + '<br><form><label><input type="checkbox" class="delete_poll_notification" name="send_notification" value="true" checked> '
                    + poll.labels['Notify attendees'] + '</label><br></form>', poll.labels['Yes'], poll.labels['No'], function Yes() {
                  if ($('input.delete_poll_notification:checked').length) {
                    window.location.href = _this.attr('href')
                        + '&_send_notif=1';
                  }
                  else {
                    window.location.href = _this.attr('href');
                  }
                }, function No() {

                });
          });
      // Click sur le bouton de suppression d'une réponse
      $("#button_delete_response")
          .click(function(event) {
            event.preventDefault();
            var _this = $(this);
            poll
                .confirm(poll.labels['Are you sure you want to delete your response ?'], poll.labels['Yes'], poll.labels['No'], function Yes() {
                  poll.show_loading(poll.labels['Deleting tentatives...']);
                  $.ajax({
                    type : 'GET',
                    url : '?_p=ajax&_a=delete_tentatives',
                    data : {
                      token : poll.env.csrf_token,
                      poll_uid : poll.env.poll_uid,
                    },
                    success : function(data) {
                      poll.hide_loading();
                      window.location.href = _this.attr('href');
                    },
                    error : function(o, status, err) {
                      poll.hide_loading();
                      window.location.href = _this.attr('href');
                    }
                  });
                }, function No() {

                });
          });
      // Envoi du formulaire de réponse
      // Vérifie que le nom n'existe pas déjà
      $("#proposals_form").submit(function() {
        var name = $("#user_username").val();
        var _this = $("#user_username");
        var isValide = true;
        $('.user_list_name').each(function() {
          if ($(this).html() == name && !_this.attr('readonly')) {
            isValide = false;
            poll.show_message(poll.labels['Name already exists'], "error");
            return false;
          }
        });
        return isValide;
      });
      // Affichage des informations supplémentaires lors de la saisie du nom
      $("#user_username").focusin(function() {
        $("#div_show_more_inputs").show();
        $(".prop_row_nb_props .hide_attendees_button").remove();
        $(".prop_row_nb_props .show_attendees_button").remove();
      });
      // Envoi du formulaire de réponse
      // Ajout des réponses en provisoire
      $("#proposals_form")
          .submit(function(event) {
            if (poll.env.poll_type == 'date' && poll.env.user_auth
                && poll.env.can_write_calendar
                && !$("input[name='hidden_modify_all']").length) {
              event.preventDefault();
              var _this = $(this);
              poll
                  .confirm(poll.labels['Would you like to add responses to your calendar as tentative ?'], poll.labels['Yes'], poll.labels['No'], function Yes() {
                    poll
                        .show_loading(poll.labels['Adding prop to your calendar...']);
                    var prop_keys = [];
                    if (poll.env.poll_if_needed) {
                      $("#proposals_form input:radio").each(function() {
                        if ($(this).prop('checked')
                            && $(this).attr('value') != '') {
                          prop_keys.push($(this).attr('name')
                              .replace('check_', ''));
                        }
                      });
                    }
                    else {
                      $("#proposals_form input:checkbox").each(function() {
                        if ($(this).prop('checked')) {
                          prop_keys.push($(this).attr('name')
                              .replace('check_', ''));
                        }
                      });
                    }
                    $.ajax({
                      type : 'POST',
                      url : '?_p=ajax&_a=add_tentative_calendar',
                      data : {
                        token : poll.env.csrf_token,
                        prop_keys : prop_keys,
                        poll_uid : poll.env.poll_uid
                      },
                      success : function(data) {
                        _this.unbind('submit').submit();
                        poll.hide_loading();
                      },
                      error : function(o, status, err) {
                        _this.unbind('submit').submit();
                        poll.hide_loading();
                      }
                    });
                  }, function No() {
                    poll.show_loading(poll.labels['Deleting tentatives...']);
                    $.ajax({
                      type : 'GET',
                      url : '?_p=ajax&_a=delete_tentatives',
                      data : {
                        token : poll.env.csrf_token,
                        poll_uid : poll.env.poll_uid,
                      },
                      success : function(data) {
                        poll.hide_loading();
                        _this.unbind('submit').submit();
                      },
                      error : function(o, status, err) {
                        poll.hide_loading();
                        _this.unbind('submit').submit();
                      }
                    });
                  });
            }
          });
    });

/**
 * Function pour valider/dévalider une proposition Utilise de l'ajax via jquery
 * 
 * @param args
 */
function show_validate_prop(args) {
  $(document)
      .ready(function() {
        if (args.action == "validate_prop" && poll.env.send_mail) {
          poll
              .confirm(poll.labels['Do you want to send a message to the attendees ?'], poll.labels['Yes'], poll.labels['No'], function Yes() {
                args.params.send_mail = true;
                ajax_validate_prop(args);
              }, function No() {
                args.params.send_mail = false;
                ajax_validate_prop(args);
              });
        }
        else {
          args.params.send_mail = false;
          ajax_validate_prop(args);
        }
      });
}
/**
 * Appel en ajax la requête de validation de la proposition
 * 
 * @param args
 * @returns
 */
function ajax_validate_prop(args) {
  var html = $("#proposals_table #validate_prop_" + args.params.prop_key)
      .html();
  $("#proposals_table #validate_prop_" + args.params.prop_key).html("");
  $("#proposals_table #validate_prop_" + args.params.prop_key).addClass("wait");
  if ($("#validate_prop_popup").hasClass("ui-dialog-content")
      && $("#validate_prop_popup").dialog("isOpen")) {
    $("#validate_prop_popup").dialog("close");
  }
  return $
      .ajax({
        type : 'POST',
        url : args.url,
        data : args.params,
        success : function(data) {
          $("#proposals_table #validate_prop_" + args.params.prop_key)
              .html(html);
          $("#proposals_table #validate_prop_" + args.params.prop_key)
              .removeClass("wait");
          if (data.success == true) {
            poll.show_message(data.message, "success");
            if (args.action == "validate_prop") {
              $("#proposals_table #validate_prop_" + args.params.prop_key)
                  .addClass("validate_prop");
              $("#proposals_table #prop_header_" + args.params.prop_key)
                  .addClass("validate_prop_header");
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-unvalidate-prop").show();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-calendar").show();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-validate-prop").hide();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-unvalidate-prop")
                  .attr("title", poll.labels['Clic to unvalidate this proposal']);
              if ($("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-calendar").hasClass("pure-button-disabled")) {
                $("#proposals_table #validate_prop_" + args.params.prop_key
                    + " .pure-button-calendar")
                    .attr("title", poll.labels['This proposals is already in your calendar']);
              }
              else {
                if (poll.env.can_write_calendar) {
                  $("#proposals_table #validate_prop_" + args.params.prop_key
                      + " .pure-button-calendar")
                      .attr("title", poll.labels['Clic to add this proposal to your calendar']);
                  // Ajouter automatiquement au calendrier une date validée
                  $("#proposals_table #validate_prop_" + args.params.prop_key
                      + " .pure-button-calendar").click();
                }
                else if (poll.env.can_generate_ics) {
                  $("#proposals_table #validate_prop_" + args.params.prop_key
                      + " .pure-button-calendar")
                      .attr("title", poll.labels['Clic to download ICS of the proposal and add it to your calendar client']);
                }
                
              }
            }
            else {
              $("#proposals_table #validate_prop_" + args.params.prop_key)
                  .removeClass("validate_prop");
              $("#proposals_table #prop_header_" + args.params.prop_key)
                  .removeClass("validate_prop_header");
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-unvalidate-prop").hide();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-calendar").hide();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-validate-prop").show();
              $("#proposals_table #validate_prop_" + args.params.prop_key
                  + " .pure-button-validate-prop")
                  .attr("title", poll.labels['Clic to validate this proposal']);
              // Chargement des freebusy au chargement de la page
              if ($('table#proposals_table tr.prop_row_freebusy').length) {
                refresh_freebusy();
              }
            }
            get_valid_proposals_text(args);
          }
          else {
            poll.show_message(data.message, "error");
          }
        },
        error : function(o, status, err) {
          $("#proposals_table #validate_prop_" + args.params.prop_key)
              .html(html);
          $("#proposals_table #validate_prop_" + args.params.prop_key)
              .removeClass("wait");
          poll.show_message(err, "error");
        }
      });
}

/**
 * Fonction pour ajouter une proposition dans l'agenda de l'utilisateur Utilise
 * de l'ajax via jquery
 * 
 * @param args
 */
function show_add_to_calendar(args) {
  // Annuler le traitement si le bouton est desactivé
  if (args.params.part_status == "ACCEPTED"
      && $("#validate_prop_" + args.params.prop_key
          + " .pure-button-calendar-accept").hasClass('pure-button-disabled')
      || args.params.part_status == "DECLINED"
      && $("#validate_prop_" + args.params.prop_key
          + " .pure-button-calendar-decline").hasClass('pure-button-disabled')
      || !args.params.part_status
      && $("#validate_prop_" + args.params.prop_key + " .pure-button-calendar")
          .hasClass('pure-button-disabled')) {
    return;
  }
  poll.show_loading(poll.labels['Adding prop to your calendar...']);
  $(document)
      .ready(function() {
        var html = $("#proposals_table #validate_prop_" + args.params.prop_key)
            .html();
        $("#proposals_table #validate_prop_" + args.params.prop_key).html("");
        $("#proposals_table #validate_prop_" + args.params.prop_key)
            .addClass("wait");
        var html_popup = "";
        if ($("#add_to_calendar_popup").hasClass("ui-dialog-content")
            && $("#add_to_calendar_popup").dialog("isOpen")) {
          html_popup = $("#add_to_calendar_popup .popup_calendar_"
              + args.params.prop_key).html();
          $("#add_to_calendar_popup .popup_calendar_" + args.params.prop_key)
              .html("");
          $("#add_to_calendar_popup .popup_calendar_" + args.params.prop_key)
              .addClass("wait");
        }
        return $
            .ajax({
              type : 'POST',
              url : args.url,
              data : args.params,
              success : function(data) {
                $("#proposals_table #validate_prop_" + args.params.prop_key)
                    .html(html);
                $("#proposals_table #validate_prop_" + args.params.prop_key)
                    .removeClass("wait");
                poll.hide_loading();
                if (data.success == true) {
                  poll.show_message(data.message, "success");
                  $("#proposals_table #validate_prop_" + args.params.prop_key
                      + " .pure-button-calendar")
                      .addClass("pure-button-disabled");
                  if (args.params.part_status) {
                    if (args.params.part_status == "ACCEPTED") {
                      $("#proposals_table #validate_prop_"
                          + args.params.prop_key
                          + " .pure-button-calendar-accept")
                          .addClass("pure-button-disabled");
                      $("#proposals_table #validate_prop_"
                          + args.params.prop_key
                          + " .pure-button-calendar-decline")
                          .removeClass("pure-button-disabled");
                    }
                    else if (args.params.part_status == "DECLINED") {
                      $("#proposals_table #validate_prop_"
                          + args.params.prop_key
                          + " .pure-button-calendar-accept")
                          .removeClass("pure-button-disabled");
                      $("#proposals_table #validate_prop_"
                          + args.params.prop_key
                          + " .pure-button-calendar-decline")
                          .addClass("pure-button-disabled");
                    }
                    if ($("#add_to_calendar_popup")
                        .hasClass("ui-dialog-content")
                        && $("#add_to_calendar_popup").dialog("isOpen")) {
                      $("#add_to_calendar_popup .popup_calendar_"
                          + args.params.prop_key).removeClass("wait");
                      $("#add_to_calendar_popup .popup_calendar_"
                          + args.params.prop_key).addClass("valide_date");
                      if (!$("#add_to_calendar_popup .pure-button-calendar-accept-text").length) {
                        setTimeout(function() {
                          $("#add_to_calendar_popup").dialog("close");
                        }, 300);
                      }
                    }
                  }
                  // Chargement des freebusy au chargement de la page
                  if ($('table#proposals_table tr.prop_row_freebusy').length) {
                    refresh_freebusy();
                  }
                }
                else {
                  $("#add_to_calendar_popup .popup_calendar_"
                      + args.params.prop_key).html(html_popup)
                  $("#add_to_calendar_popup .popup_calendar_"
                      + args.params.prop_key).removeClass("wait");
                  poll.show_message(data.message, "error");
                }
              },
              error : function(o, status, err) {
                poll.hide_loading();
                $("#proposals_table #validate_prop_" + args.params.prop_key)
                    .html(html);
                $("#proposals_table #validate_prop_" + args.params.prop_key)
                    .removeClass("wait");
                $("#add_to_calendar_popup .popup_calendar_"
                    + args.params.prop_key).html(html_popup)
                $("#add_to_calendar_popup .popup_calendar_"
                    + args.params.prop_key).removeClass("wait");
                poll.show_message(err, "error");
                if ($("#add_to_calendar_popup").hasClass("ui-dialog-content")
                    && $("#add_to_calendar_popup").dialog("isOpen")) {
                  $("#add_to_calendar_popup .popup_calendar_"
                      + args.params.prop_key).html(html_popup);
                }
              }
            });
      });
}

/**
 * Fonction pour récupérer le texte des propositions validées par l'organisateur
 * Utilise de l'ajax via jquery
 * 
 * @param args
 */
function get_valid_proposals_text(args) {
  $(document).ready(function() {
    return $.ajax({
      type : 'POST',
      url : './?_p=ajax&_a=get_valid_proposals_text',
      data : args.params,
      success : function(data) {
        if (data.success == true) {
          if (data.text != "") {
            $(".best_proposals").hide();
            $(".validate_proposals").show();
            $(".validate_proposals").text(data.text);
          }
          else {
            $(".validate_proposals").hide();
            $(".best_proposals").show();
          }
        }
      },
      error : function(o, status, err) {
      }
    });
  });
}

/**
 * Appel la suppression des provisoires en ajax Puis rafraichis la liste des
 * freebusy
 * 
 * @param args
 */
function delete_tentatives(args) {
  poll
      .confirm(poll.labels['Would you like to delete tentatives events of this poll from your calendar ?'], poll.labels['Yes'], poll.labels['No'], function Yes() {
        poll.show_loading(poll.labels['Deleting tentatives...']);
        $
            .ajax({
              type : 'GET',
              url : '?_p=ajax&_a=delete_tentatives',
              data : {
                token : poll.env.csrf_token,
                poll_uid : poll.env.poll_uid,
              },
              success : function(data) {
                poll.hide_loading();
                if (data.success) {
                  if ($('table#proposals_table tr.prop_row_freebusy').length) {
                    refresh_freebusy();
                  }
                  poll
                      .show_message(poll.labels['Tentatives correctly deleted'], "success");
                  $("#button_delete_tentatives").hide();
                }
                else {
                  poll.show_message(data.message, "error");
                }
              },
              error : function(o, status, err) {
                poll.hide_loading();
              }
            });
      }, function No() {
      });
}

/**
 * Rafraichi la liste des freebusy pour l'utilisateur
 */
function refresh_freebusy() {
  poll.show_loading(poll.labels['Load freebusy...']);
  $
      .ajax({
        type : 'GET',
        url : '?_p=ajax&_a=get_user_freebusy',
        data : {
          token : poll.env.csrf_token,
          poll_uid : poll.env.poll_uid,
        },
        success : function(data) {
          if (data.success) {
            for ( var key in data.text) {
              var td = $('table#proposals_table tr.prop_row_freebusy td.freebusy_prop_'
                  + data.text[key].prop_key);
              if (!poll.env.mobile) {
                td.tooltipster('destroy');
              }
              td.attr("title", data.text[key].title);
              td.attr("class", data.text[key].class);
              td.text(data.text[key].text);
              if (!poll.env.mobile) {
                td.tooltipster({
                  position : 'bottom',
                  animation : 'fade',
                  delay : 200,
                  theme : 'af-tooltip-theme',
                  touchDevices : false,
                  trigger : 'hover'
                });
              }
            }
          }
          poll.hide_loading();
        },
        error : function(o, status, err) {
          poll.hide_loading();
        }
      });
}

/**
 * Valide automatiquement les réponses au sondage en fonction des disponibilités
 */
function save_from_freebusy(args) {
  $(document).ready(function() {
    if (poll.env.poll_if_needed) {
      var tr = $('.yes_to_all_button').parents('tr');
      tr.find('input:radio').each(function() {
        var id = $(this).attr('name').replace('check_', '');
        if ($(this).attr('value').length
            && $(this).attr('value').indexOf(":if_needed") == -1
            && $('table#proposals_table tr.prop_row_freebusy td.freebusy_prop_'
                + id).hasClass("freebusy_none")) {
          $(this).attr('checked', 'checked');
        }
        else {
          $(this).attr('checked', false);
        }
      });
    }
    else {
      var tr = $('.check_all_button').parents('tr');
      tr.find('input:checkbox').each(function() {
        var id = $(this).attr('name').replace('check_', '');
        if ($('table#proposals_table tr.prop_row_freebusy td.freebusy_prop_'
            + id).hasClass("freebusy_none")) {
          $(this).attr('checked', 'checked');
          $(this).parent().css('background-color', '#E4EBF5');
        }
        else {
          $(this).attr('checked', false);
          $(this).parent().css('background-color', '#FFF');
        }
      });
    }
    refresh_tr_html(tr);
    // Ne pas valider automatiquement le formulaire
    //$("#proposals_form").submit();
  });
}

/**
 * Masque la liste des participants
 */
function hide_attendees(args) {
  $(document).ready(function() {
    $(".prop_others_users_elements").hide();
    $(".hide_attendees_button").hide();
    $(".show_attendees_button").show();
  });
}
/**
 * Affiche la liste des participants
 */
function show_attendees(args) {
  $(document).ready(function() {
    $(".prop_others_users_elements").show();
    $(".hide_attendees_button").show();
    $(".show_attendees_button").hide();
  });
}

var nb_new_attendee = 0;
/**
 * Ajoute un nouveau champ pour ajouter un participant
 * 
 * @param args
 */
function add_attendee(args) {
  $(document)
      .ready(function() {
        nb_new_attendee++;
        var td = '';
        $('#proposals_table thead td.prop_header')
            .each(function() {
              var prop = $(this).attr('id').replace('prop_header_', '');
              if (poll.env.poll_if_needed) {
                td += '<td class="prop_not_responded" align="center">';
                td += '<input id="newradio--' + nb_new_attendee + '--' + prop
                    + '" name="newradio--' + nb_new_attendee + '--' + prop
                    + '" value="' + poll.env.proposals[prop]
                    + '" type="radio">';
                td += '<label for="newradio--' + nb_new_attendee + '--' + prop
                    + '">' + poll.labels['Yes'] + '</label>';
                td += '<br>';
                td += '<input id="newradio--if_needed' + nb_new_attendee + '--'
                    + prop + '" name="newradio--' + nb_new_attendee + '--'
                    + prop + '" value="' + poll.env.proposals[prop]
                    + ':if_needed" type="radio">';
                td += '<label for="newradio--if_needed' + nb_new_attendee
                    + '--' + prop + '">' + poll.labels['If needed']
                    + '</label>';
                td += '<br>';
                td += '<input id="newradio--declined' + nb_new_attendee + '--'
                    + prop + '" name="newradio--' + nb_new_attendee + '--'
                    + prop + '" value="" type="radio">';
                td += '<label for="newradio--declined' + nb_new_attendee + '--'
                    + prop + '">' + poll.labels['No'] + '</label>';
                td += '</td>';
              }
              else {
                td += '<td class="prop_not_responded" align="center"><input id="newcheck--'
                    + nb_new_attendee
                    + '--'
                    + prop
                    + '" name="newcheck--'
                    + nb_new_attendee
                    + '--'
                    + prop
                    + '" value="'
                    + poll.env.proposals[prop] + '" type="checkbox"></td>';
              }
            });
        var first_col = '<td class="first_col"><input style="width: 100%;" class="newuser" id="newuser--'
            + nb_new_attendee
            + '" name="newuser--'
            + nb_new_attendee
            + '" placeholder="'
            + poll.labels['Username']
            + '" required="required" type="text"><input style="width: 100%;" id="newemail--'
            + nb_new_attendee
            + '" class="newemail" name="newemail--'
            + nb_new_attendee
            + '" placeholder="'
            + poll.labels['Email address'] + '" type="text"></td>';
        var last_col = '<td class="prop_cell_nobackground last_col" align="center"><a onclick="poll.command(remove_attendee, {_this: this});" class="remove_attendee_button customtooltip_bottom tooltipstered">'
            + poll.labels['Remove'] + '</a></td>';
        var insert = '<tr class="prop_new_element">' + first_col + td
            + last_col + '</tr>';

        $('#proposals_table tr.prop_row_nb_props').before(insert);
      });
}

/**
 * Supprime le champ d'ajout d'un participant
 */
function remove_attendee(args) {
  $(document).ready(function() {
    $(args._this).parents('tr').remove();
  });
}

/**
 * Coche toutes les checkboxes de la ligne
 */
function check_all(args) {
  $(document).ready(function() {
    var tr = $('.check_all_button').parents('tr');
    tr.find('input:checkbox').attr('checked', 'checked');
    if (tr.hasClass('prop_row_new_response')) {
      tr.find('td.prop_not_responded').css('background-color', '#E4EBF5');
    }
    refresh_tr_html(tr);
  });
}
/**
 * Décoche toutes les checkboxes de la ligne
 */
function uncheck_all(args) {
  $(document).ready(function() {
    var tr = $('.uncheck_all_button').parents('tr');
    tr.find('input:checkbox').attr('checked', false);
    if (tr.hasClass('prop_row_new_response')) {
      tr.find('td.prop_not_responded').css('background-color', '#ffffff');
    }
    refresh_tr_html(tr);
  });
}
/**
 * Valide tous les boutons radio à yes
 */
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
    refresh_tr_html(tr);
  });
}
/**
 * Valide tous les boutons radio à if_needed
 */
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
    refresh_tr_html(tr);
  });
}
/**
 * Valide tous les boutons radio à no
 */
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
    refresh_tr_html(tr);
  });
}

function refresh_tr_html(tr) {
  var val = tr.find("#select_calendar_new_response").val();
  tr.html(tr.html());
  tr.find("#select_calendar_new_response").val(val);
}