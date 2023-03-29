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

// Gestion des données non enregistrés
var hasChanged = false;
var formSubmitting = false;
var setFormSubmitting = function () { window.formSubmitting = true; };
var isDirty = function () { return window.hasChanged; }
var date_input_width = 150;
var time_input_width = 100;

// 0005079: Lors de l'édition des propositions, prévenir si les props ne sont pas enregistré au changement de page
window.onload = function () {
  window.addEventListener("beforeunload", function (e) {
    if (window.formSubmitting || !isDirty()) {
      return undefined;
    }

    var confirmationMessage = poll.labels['Proposals have changed, if you leave the page, the changes will be lost'];

    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
  });
};

// Ce fichier permet de gérer le calendrier pour la gestion des dates
// Il va afficher le calendrier et gérer les intéractions
$(document)
  .ready(
    function () {
      var date = new Date();
      var d = date.getDate();
      var m = date.getMonth();
      var y = date.getFullYear();

      createList();

      var calendar = $('#calendar')
        .fullCalendar(
          {
            header: {
              left: 'list,agendaDay,agendaWeek,month',
              center: 'title',
              right: 'today,prev,next'
            },
            // aspectRatio: 1,
            height: $('#calendar').height() - 50,



            selectable: true,
            selectHelper: false,
            timezone: 'local',
            weekNumbers: true,
            select: function (start, end) {
              let allDay = !start.hasTime();
              let end2 = end.clone();
              let startToDate = start.clone().toDate();
              if (allDay) {
                end2 = end2.subtract(1, 'days')
                startToDate = start.clone();
              }

              if (!dateExist(startToDate, end2.toDate(), allDay)) {
                if (allDay) {
                  var id = addNewDate(start, null, allDay);
                }
                else {
                  var id = addNewDate(start.toDate(), end2.toDate(), allDay);
                }

                var event = {
                  id: id,
                  title: poll.env.poll_title,
                  start: start,
                  end: end,
                  allDay: allDay,
                };

                calendar.fullCalendar('renderEvent', event, true // make
                  // the
                  // event
                  // "stick"
                );
              }
            },
            eventMouseover: function (calEvent, domEvent) {
              if (calEvent.id.indexOf(poll.env.date_rdv) == -1)
                return;
              let found = poll.env.validate_proposal_key ? poll.env.validate_proposal_key.find(prop => prop == calEvent.id) : null;
              if (!found) {
                var layer = "<div id='events-layer' class='fc-transparent' style='position:absolute; width:100%; height:80%; top:2px; text-align:right; z-index:100; right: 2px;'> <a> <img border='0' width='12px' src='skins/"
                  + poll.env.skin
                  + "/images/1395421411_white_delete.png' title='"
                  + poll.labels.Delete
                  + "' onClick='deleteEvent("
                  + calEvent.id + ");'></a></div>";
              }
              $(this).append(layer);
            },
            eventMouseout: function (calEvent, domEvent) {
              $("#events-layer").remove();
            },
            eventDrop: function (event, dayDelta, revertFunc) {
              updateEvent(event);
            },
            eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
              updateEvent(event);
            },

            // // time formats
            slotLabelFormat: 'HH:mm',
            defaultView: 'agendaWeek',
            firstHour: 10,

            // editing
            editable: true,
            // eventResizableFromStart: true,
            eventColor: '#6B7F9F',

            // locale
            firstDay: 1,
            buttonText: {
              today: poll.labels.Today,
              month: poll.labels.Month,
              week: poll.labels.Week,
              day: poll.labels.Day
            },
            buttonIcons: {
              prev: 'left-single-arrow',
              next: 'right-single-arrow',
              prevYear: 'left-double-arrow',
              nextYear: 'right-double-arrow'
            },
            customButtons: {
              list: {
                text: 'Liste',
                click: function () {
                  $("#calendar .fc-center").hide();
                  $("#calendar .fc-right").hide();
                  $("#calendar .fc-view-container").hide();
                  $("#calendar .fc-button-group button").removeClass('fc-state-active');
                  $(this).addClass('fc-state-active')
                  $("#props_list").show();
                  $("#props_new").show();
                }
              }
            },

            loading: function (is_loading) {
              if (is_loading) {
                poll
                  .show_loading(poll.labels['Loading your events...']);
              } else {
                poll.hide_loading();
              }
            },

            // // Liste les évènements
            // events: getAllEvents()
            eventSources: [getAllEvents(), getExternEventSource(),]

          });

      $(window).resize(function () {
        $('#calendar').fullCalendar('option', 'height', $('#calendar').height() - 50);
      });
      $("#calendar .fc-button-group .fc-agendaDay-button").click(function () {
        if ($("#calendar .fc-view-container").is(":hidden")) {
          $("#calendar .fc-button-group .fc-list-button").removeClass('fc-state-active');
          $("#calendar .fc-center").show();
          $("#calendar .fc-right").show();
          $("#calendar .fc-view-container").show();
          $("#props_list").hide();
          $("#props_new").hide();
          $('#calendar').fullCalendar('changeView', 'agendaWeek');
          $('#calendar').fullCalendar('changeView', 'agendaDay');
        }
      });

      $("#calendar .fc-button-group .fc-agendaWeek-button").click(function () {
        if ($("#calendar .fc-view-container").is(":hidden")) {
          $("#calendar .fc-button-group .fc-list-button").removeClass('fc-state-active');
          $("#calendar .fc-center").show();
          $("#calendar .fc-right").show();
          $("#calendar .fc-view-container").show();
          $("#props_list").hide();
          $("#props_new").hide();
          $('#calendar').fullCalendar('changeView', 'agendaDay');
          $('#calendar').fullCalendar('changeView', 'agendaWeek');
        }
      });

      $("#calendar .fc-button-group .fc-month-button").click(function () {
        if ($("#calendar .fc-view-container").is(":hidden")) {
          $("#calendar .fc-button-group .fc-list-button").removeClass('fc-state-active');
          $("#calendar .fc-center").show();
          $("#calendar .fc-right").show();
          $("#calendar .fc-view-container").show();
          $("#props_list").hide();
          $("#props_new").hide();
          $('#calendar').fullCalendar('changeView', 'agendaWeek');
          $('#calendar').fullCalendar('changeView', 'month');
        }
      });

      $("#props_list").hide();
      $("#props_new").hide();

      $(document).on("focusout", ".list_input", function () {

        let value = this.id.split('-');
        let id = value[1];

        let changeStart = false;

        if (value[0].indexOf('start') != -1) {
          changeStart = true;
        }

        let date_start = $('#datepicker_start-' + id).val();
        let date_end = $('#datepicker_end-' + id).val();
        let time_start = $('#timepicker_start-' + id).val();
        let time_end = $('#timepicker_end-' + id).val();

        date = verifDate(date_start, date_end, time_start, time_end, changeStart, id);

        let date_input = date.split(' ');
        $('#datepicker_start-' + id).val(date_input[0]);
        if (date_input[3]) {
          $('#datepicker_end-' + id).val(date_input[3]);
        }
        else {
          $('#datepicker_end-' + id).val(date_input[2]);

        }
        $('#timepicker_start-' + id).val(date_input[1]);
        $('#timepicker_end-' + id).val(date_input[4]);
        $('#' + id).val(date);

        modifyCalendarEvent(id, date);
      });

      $("#add_new_date").click(function () {
        addDateDiv('');
        return false;
      });
      $(".change_type_poll")
        .click(
          function () {
            return confirm(poll.labels['Are you sure ? Not saved proposals are lost']);
          });
      $("#calendar").resizable();
      // Positionne le calendrier sur la premiere date
      if (poll.env.first_prop_date) {
        var date = poll.env.first_prop_date.split(" - ");
        $('#calendar').fullCalendar('gotoDate', dateForm(date[0]));
      }
      // Ajout du bouton de masquage des agendas
      if (poll.env.can_get_freebusy) {
        $('#calendar div.fc-right div.fc-button-group')
          .append(
            '<button type="button" style="-moz-user-select: none;" class="fc-button-hide-calendar fc-button hide-calendar fc-state-default fc-corner-right">' + poll.labels['hide calendar'] + '</button>');
        $('#calendar div.fc-right')
          .append(
            '<span class="fc-header-legend"><span class="fc-header-legend-name">'
            + poll.labels['Your freebusy']
            + ':</span><span class="fc-header-legend-confirmed">'
            + poll.labels['Confirmed']
            + '</span><span class="fc-header-legend-tentative">'
            + poll.labels['Tentative']
            + '</span><span class="fc-header-legend-none">'
            + poll.labels['None'] + '</span>');
        $('#calendar div.fc-right button.fc-button-next').removeClass(
          'fc-corner-right');
        $("#calendar div.fc-right button.fc-button-hide-calendar")
          .hover(function () {
            $(this).addClass('fc-state-hover');
          }, function () {
            $(this).removeClass('fc-state-hover');
          });
        $("#calendar div.fc-right button.fc-button-hide-calendar")
          .click(
            function () {
              if ($(this).hasClass('hide-calendar')) {
                $(this).removeClass('hide-calendar');
                //$(this).addClass('fc-state-active');
                $('#calendar span.fc-header-legend').hide();
                this.innerHTML = poll.labels['show calendar'];
                calendar.fullCalendar('removeEventSource',
                  getExternEventSource());
              } else {
                $(this).addClass('hide-calendar');
                //$(this).removeClass('fc-state-active');
                $('#calendar span.fc-header-legend').show();
                this.innerHTML = poll.labels['hide calendar'];
                calendar.fullCalendar('addEventSource',
                  getExternEventSource());
              }
            });
}
//Ajout du bouton de duplication des semaine
if (poll.env.is_type_rdv) {
    $('#calendar div.fc-right div.fc-button-group')
        .append(
            '<button type="button" style="-moz-user-select: none;" class="fc-button-duplicate-proposals fc-button duplicate-proposals fc-state-default fc-corner-right">' + poll.labels['duplicate proposals'] + '</button>');
    $("#calendar div.fc-right button.fc-button-duplicate-proposals")
        .hover(function() {
            $(this).addClass('fc-state-hover');
        }, function() {
            $(this).removeClass('fc-state-hover');
        });
    $("#calendar div.fc-right button.fc-button-duplicate-proposals")
        .click(
            function() {
                var events = $('#calendar').fullCalendar('clientEvents');
                var elements = document.getElementsByClassName("fc-day fc-mon");
                if(elements.length == 0){
                    console.log("prb affichage");
                    var currentDate = $('#calendar').fullCalendar('getDate');
                }else{
                    var currentDate = moment(elements[0].getAttribute('data-date'));
                }
                
                var endDate = currentDate.clone().add(7,'days');
                events.forEach(function(event){

                    if ( currentDate.isBefore(event.start) && event.start.isBefore(endDate)){

                        if (event.title == poll.env.poll_title){
                            var start = event.start.clone();
                            start.add(7,'days');
                            var end = event.end.clone();
                            end.add(7,'days');
                            var allDay = event.allDay;
                            var newEvent = {
                                title: poll.env.poll_title,
                                start : start,
                                end: end,
                                allDay: allDay
                            };
                            var addEvent = true;
                                                                
                            events.forEach(function(eventbis){
                                if (eventbis.title == newEvent.title){
                                    if(eventbis.start.toString() == newEvent.start.toString()){
                                        if(eventbis.end.toString() == newEvent.end.toString()){
                                            if(eventbis.allDay == newEvent.allDay){
                                                addEvent = false;
                                            }
                                        }
                                    }
                                }
                            });
                            if (addEvent){
                                $('#calendar').fullCalendar('renderEvent', newEvent, true);
                                addNewDate(newEvent.start.toDate(), newEvent.end.toDate(), newEvent.allDay);    
                            }
                        }
                    }
                });
                $('#calendar').fullCalendar('changeView', 'agendaWeek', endDate);
            
            });
      }
    });

/**
* Vérifie que la date de début et la date de fin soit cohérentes.
* @param datepicker_start
* @param datepicker_end
* @param timepicker_start
* @param timepicker_end
* @param changeStart
* @returns date
*/

function verifDate(date_start, date_end, time_start, time_end, changeStart, id) {
  $('#error_message-' + id).css('display', 'none');
  if (date_start != "" && date_end == "" && time_start == "" && time_end == "") {
    return date_start;
  }
  else if (date_start != "" && time_start != "" && date_end == "" && time_end == "") {
    time_end = moment(time_start, "HH:mm").add(2, 'h').format("HH:mm");

    if (time_end.substring(0, 2) <= 2) {
      date_end = moment(date_start).add(1, 'days').format("YYYY-MM-DD");
    }
    else {
      date_end = date_start;
    }
    return date_start + ' ' + time_start + ' - ' + date_end + ' ' + time_end;
  }
  else if (date_start != "" && date_end != "" && time_start == "" && time_end == "") {
    return verifDateValue(date_start, date_end, changeStart)
  }
  else if (date_start != "" && date_end != "" && time_start != "" && time_end != "") {
    return verifDateValue(date_start, date_end, changeStart, time_start, time_end);
  }
  else {
    $('#error_message-' + id).css('display', 'block');
    return false;
  }
}

function verifDateValue(datepicker_start, datepicker_end, changeStart = null, timepicker_start = null, timepicker_end = null) {
  //Si date_start et date_end sont uniquement en param
  let date;
  if (!timepicker_start) {
    let date_start = new Date(datepicker_start);
    let date_end = new Date(datepicker_end);

    if (date_start > date_end) {
      if (changeStart) {
        datepicker_end = datepicker_start;
      }
      else {
        datepicker_start = datepicker_end;
      }
    }
    return datepicker_start + ' - ' + datepicker_end;
  }
  else {
    let date_start = new Date(datepicker_start + ' ' + timepicker_start);
    let date_end = new Date(datepicker_end + ' ' + timepicker_end);

    if (date_start >= date_end) {
      if (changeStart) {
        date_end = new Date(datepicker_start);
        date_end.setHours(date_start.getHours() + 1);
      }
      else {
        date_start = new Date(datepicker_end);
        date_start.setHours(date_end.getHours() - 1);
      }
    }
    date = getDateFromData(date_start, date_end, false);
  }
  return date;
}
/**
 * Retourne la source vers l'agenda Melanie2
 * @returns json
 */
function getExternEventSource() {
  return {
    url: './?_p=ajax&_a=get_user_events_json',
    type: 'POST',
    color: '#EBF1F6', // a non-ajax option
    textColor: 'black', // a non-ajax option
    cache: true,
    editable: false,
    cache: true,
  };
}
/**
 * Retourne une Date formatté
 * 
 * @param date
 * @returns
 */
function dateForm(date) {
  if (date.length == 16 || date.length == 19) {
    var tmp = date.split(" ");
    var date_tmp = tmp[0].split("-");
    var time_tmp = tmp[1].split(":");
    if (date_tmp[0] && date_tmp[1] && date_tmp[2] && time_tmp[0] && time_tmp[1]) {
      // Formatte la date avec toutes les données
      return new Date(date_tmp[0], date_tmp[1] - 1, date_tmp[2], time_tmp[0],
        time_tmp[1]);
    } else {
      // Impossible de formatter la date on essaye tel quel
      return new Date(date);
    }
  } else {
    // Date retournée
    return new Date(date);
  }
}
/**
 * Parcourir les dates déjà définies pour les afficher dans le calendrier
 */
function getAllEvents() {
  // Gestion de la liste des évènements
  var events = [];
  $("#props_list ." + poll.env.date_rdv).each(function () {
    if ($(this).attr("value") || this.value) {
      var date = $(this).attr("value").split(" - ");
      if (!date)
        date = this.value.split(" - ");
      var id = $(this).attr("id");
      fillList(id, this.value);
      if (!id)
        id = this.id;

      if (date[1]) {
        // Ajout de l'évènement
        let end;
        if (date[0].length == 10 && date[1].length == 10) {
          end = moment(dateForm(date[1])).add(1, 'days');
        }
        else {
          end = moment(dateForm(date[1]));
        }
        events.push({
          id: id,
          title: poll.env.poll_title,
          start: moment(dateForm(date[0])),
          end: end,
          allDay: date[0].length == 10 && date[1].length == 10
        });

      } else {
        events.push({
          id: id,
          title: poll.env.poll_title,
          start: moment(date[0]).format(),
          allDay: date[0].length == 10
        });
      }
    }
  });
  return events;
}

function createList() {
  // new_html += 'style="display:none"';
  var new_html = '<div class="pure-control-group">';
  new_html += '<input style="display:none" id="new_date" type="text" name="new_date" class="' + poll.env.date_rdv + '"> ';
  new_html += 'De : ';
  new_html += '<input style="width: ' + date_input_width + 'px;" id="datepicker_start-new_date" type="date" class="date_input"> ';
  new_html += '<input style="width: ' + time_input_width + 'px;" id="timepicker_start-new_date" type="time" class="time_input">  ';
  new_html += ' à : ';
  new_html += '<input style="width: ' + date_input_width + 'px;" id="datepicker_end-new_date" type="date"  class="date_input"> ';
  new_html += '<input style="width: ' + time_input_width + 'px;" id="timepicker_end-new_date" type="time" class="time_input">  ';
  new_html += '<a class="pure-button pure-button-submit-date" onclick="getNewDate()">Ajouter';
  new_html += '</a>';
  new_html += '<span id="error_message-new_date" style="display:none; color:red" >Merci de remplir les champs nécessaires</span>';
  new_html += '</div>';

  $('#error_message').css('display', 'none');
  $('#props_new').append(new_html);
}
/**
 * Affiche la liste des dates déjà définies
 * @param id de l'évènement
 * @param value
 */
function fillList(id, value) {
  date_input = value.split(' ');

  var html_list = '<div class="pure-control-group">';
  html_list += 'De : ';
  html_list += '<input style="width: ' + date_input_width + 'px;" id="datepicker_start-' + id + '" type="date" class="list_input date_input" value="' + date_input[0] + '" > ';
  html_list += '<input style="width: ' + time_input_width + 'px;" id="timepicker_start-' + id + '" type="time" class="list_input time_input" value="' + date_input[1] + '" >  ';
  html_list += ' à : ';
  if (date_input[3]) {
    html_list += '<input style="width: ' + date_input_width + 'px;" id="datepicker_end-' + id + '" type="date"  class="list_input date_input" value="' + date_input[3] + '"> ';
  }
  else {
    html_list += '<input style="width: ' + date_input_width + 'px;" id="datepicker_end-' + id + '" type="date"  class="list_input date_input" value="' + date_input[2] + '"> ';

  }
  html_list += '<input style="width: ' + time_input_width + 'px;" id="timepicker_end-' + id + '" type="time" class="list_input time_input" value="' + date_input[4] + '" >  ';
  html_list += '<a class="pure-button pure-button-delete-date" "style"="padding-top: 3px;" onclick="deleteDate(\'' + id + '\');">';
  html_list += '</a>';
  html_list += '<span id="error_message-' + id + '" style="display:none; color:red" >Merci de remplir les champs nécessaires</span>';
  html_list += '</div>';

  $('#props_list').append(html_list);
}

/**
 * Ajoute le div de date
 * 
 * @param value
 */
function addDateDiv(value) {
  // Passer la valeur en a changé
  window.hasChanged = true;
  if (poll.env.mobile) {
    // Gets the number of elements with class yourClass
    var numItems = $('.edit_date_start').length + 1;
    var id = poll.env.date_rdv + numItems;
    var html = '<div class="pure-control-group">';
    html += '<label for="edit_date_start' + numItems + '">'
      + poll.labels['Edit date (Y-m-d H:i:s)'] + '</label>';
    html += '' + poll.labels['Start'] + ' ';
    html += '<input id="edit_date_start' + numItems + '" name="edit_date_start'
      + numItems + '" class="edit_date_start" type="date" value="">';
    html += '<input id="edit_time_start' + numItems + '" name="edit_time_start'
      + numItems + '" class="edit_time_start" type="time" value="">';
    html += '' + poll.labels['End'] + ' ';
    html += '<input id="edit_date_end' + numItems + '" name="edit_date_end'
      + numItems + '" class="edit_date_end" type="date" value="">';
    html += '<input id="edit_time_end' + numItems + '" name="edit_time_end'
      + numItems + '" class="edit_time_end" type="time" value="">';
    html += '</div>';
    html += '<br>';
  } else {
    // Gets the number of elements with class yourClass
    var numItems = 0;
    $("." + poll.env.date_rdv).each(function () {
      var val = parseInt(this.id.replace(poll.env.date_rdv, ""));
      if (val > numItems) {
        numItems = val;
      }
    });
    numItems++;
    var id = poll.env.date_rdv + numItems;
    var html = '<div class="pure-control-group">';
    // html += 'style="display:none"';
    html += '<input style="display:none" id="' + id + '" type="text" name="' + id + '" class="' + poll.env.date_rdv + '" value="' + value + '" > ';
    html += '</div> ';

    fillList(id, value);
  }
  $('#props_list').append(html);
  return id;
}

/**
 * Ajoute le div de date depuis champs liste
 * @returns id
 */
function getNewDate() {
  let datepicker_start = $('#datepicker_start-new_date').val();
  let datepicker_end = $('#datepicker_end-new_date').val();
  let timepicker_start = $('#timepicker_start-new_date').val();
  let timepicker_end = $('#timepicker_end-new_date').val();

  let date = verifDate(datepicker_start, datepicker_end, timepicker_start, timepicker_end, true, 'new_date');

  var id;
  if (poll.env.mobile) {
    $("." + poll.env.date_rdv).each(function () {
      if (!$(this).attr("value")) {
        $(this).attr("value", date);
        this.value = date;
        id = this.id;
        return false;
      }
    });
  }
  // Si la valeur n'a pas été ajoutée, on ajoute un nouvel input
  if (!id) {
    id = addDateDiv(date);
    updateList(id, date)
    modifyCalendarEvent(id, date);
  }

  //On reset les valeurs des champs
  $('#datepicker_start-new_date').val("");
  $('#datepicker_end-new_date').val("");
  $('#timepicker_start-new_date').val("");
  $('#timepicker_end-new_date').val("");
  // Retourne l'id
  return id;

}

/**
 * Test si la date existe déjà
 * 
 * @param start
 * @param end
 * @param allDay
 * @returns {Boolean}
 */
function dateExist(start, end, allDay) {
  if (allDay) {
    var date = start.format();
  }
  else {
    var date = getDateFromData(start, end, allDay);
  }
  var exist = false;
  $("." + poll.env.date_rdv).each(function () {
    if (this.value == date) {
      exist = true;
    }
  });
  return exist;
}
/**
 * Ajout une nouvelle date
 */
function addNewDate(start, end, allDay) {
  if (allDay) {
    var date = start.format();
  }
  else {
    var date = getDateFromData(start, end, allDay);
  }
  // Parcourir les valeurs pour en trouver une vide
  var id;
  if (poll.env.mobile) {
    $("." + poll.env.date_rdv).each(function () {
      if (!$(this).attr("value")) {
        $(this).attr("value", date);
        this.value = date;
        id = this.id;
        return false;
      }
    });
  }
  // Si la valeur n'a pas été ajoutée, on ajoute un nouvel input
  if (!id) {
    id = addDateDiv(date);
    updateList(id, date)
  }
  // Retourne l'id
  return id;
}
/**
 * Mise à jour de l'événement
 * @param event
 */
function updateEvent(event) {
  let end2;

  if (event.end != null) {
    if (event.allDay) {
      end2 = event.end.clone();
      end2 = end2.subtract(1, 'days').toDate();
    }
    else {
      end2 = event.end.toDate();
    }
  }
  else {
    if (!event.allDay) {
      end2 = event.start.clone();
      end2 = end2.add(2, 'h').toDate();
    }
  }

  if (event.allDay) {
    var date = event.start.format();
  }
  else {
    var date = getDateFromData(event.start.toDate(), end2, event.allDay);
  }
  
  $("." + poll.env.date_rdv).each(function () {
    if (this.id == event.id) {
      $(this).attr("value", date);
      this.value = date;
      updateList(this.id, date);

      // Passer la valeur en a changé
      window.hasChanged = true;
      return false;
    }
  });
}
/**
 * Mise à jour de la liste
 * @param id de l'évènement
 * @param date
 */
function updateList(id, date) {

  value = date.split(' - ');

  let time1 = value[0].split(' ');
  let time2;
  if (value[1]) {
    time2 = value[1].split(' ');
  }

  $('#datepicker_start-' + id).val("");
  $('#datepicker_end-' + id).val("");
  $('#timepicker_start-' + id).val("");
  $('#timepicker_end-' + id).val("");


  $('#datepicker_start-' + id).val(time1[0]);
  if (time2) {
    $('#datepicker_end-' + id).val(time2[0]);
    if (time2[1]) {
      $('#timepicker_end-' + id).val(time2[1]);
    }
  }
  if (time1[1]) {
    $('#timepicker_start-' + id).val(time1[1]);
  }

}
/**
 * Suppression d'une date dans la liste Supprime l'évènement en même temps
 * 
 * @param id
 * @returns
 */
function deleteDate(id) {
  $('#calendar').fullCalendar('removeEvents', id);
  $('#' + id).parent().remove();
  $('#datepicker_start-' + id).parent().remove();
  return false;
}
/**
 * Suppression d'un évènement
 * 
 * @param event
 */
function deleteEvent(event) {
  // Suppression de l'évènement
  $("." + poll.env.date_rdv).each(function () {
    if (this.id == event.id) {
      // $(this).attr("value", "");
      // $(this).value = "";
      $(this).parent().remove();
      $('#datepicker_start-' + this.id).parent().remove();

      // Passer la valeur en a changé
      window.hasChanged = true;
      return false;
    }
  });
  $('#calendar').fullCalendar('removeEvents', event.id);
}
/**
 * Met à jour le calendrier en fonction de la date passée
 * 
 * @param id
 */
function modifyCalendarEvent(id, value) {
  var events = $('#calendar').fullCalendar('clientEvents', id);

  if (events.length == 0) {
    if (value) {
      var date = value.split(" - ");

      // Création
      var title = poll.env.poll_title;
      var event;
      if (date[1]) {
        // Generation de l'évènement
        let end;
        if (date[0].length == 10 && date[1].length == 10) {
          end = moment(dateForm(date[1])).add(1, 'days');
        }
        else {
          end = moment(dateForm(date[1]));
        }

        event = {
          id: id,
          title: title,
          start: dateForm(date[0]),
          end: end,
          allDay: date[0].length == 10 && date[1].length == 10
        };
      } else {
        // Generation de l'évènement
        event = {
          id: id,
          title: title,
          start: dateForm(date[0]),
          allDay: date[0].length == 10
        };
      }
      $('#calendar').fullCalendar('renderEvent', event, true // make the event
        // "stick"
      );
    }
  } else {
    // Modification
    for (var key in events) {
      var event = events[key];
      if (!value) {
        $('#calendar').fullCalendar('removeEvents', event.id);
      } else {
        var date = value.split(" - ");
        event.start = moment(new Date(date[0]));

        if (date[1]) {
          if (date[0].length == 10 && date[1].length == 10) {
            event.end = moment(dateForm(date[1])).add(1, 'days');
          }
          else {
            event.end = moment(dateForm(date[1]));
          }
          event.allDay = date[0].length == 10 && date[1].length == 10
        } else {
          event.end = new Date(date[0]);
          event.allDay = date[0].length == 10;
        }
        $('#calendar').fullCalendar('updateEvent', event);
      }
    }
  }
}
/**
 * Génération de la date en fonction des données passées
 * 
 * @param start
 * @param end
 * @param allDay
 * @returns
 */
function getDateFromData(start, end, allDay) {
  var date;
  // Generation de la date
  if (start && end && start < end) {
    // Cas d'une date de début et de fin
    var start_month = (start.getMonth() + 1) < 10 ? "0"
      + (start.getMonth() + 1) : (start.getMonth() + 1);
    var end_month = (end.getMonth() + 1) < 10 ? "0" + (end.getMonth() + 1)
      : (end.getMonth() + 1);
    var start_day = start.getDate() < 10 ? "0" + start.getDate() : start
      .getDate();
    var end_day = end.getDate() < 10 ? "0" + end.getDate() : end.getDate();
    if (allDay) {
      date = start.getFullYear() + "-" + start_month + "-" + start_day + " - "
        + end.getFullYear() + "-" + end_month + "-" + end_day;
    } else {
      var start_hour = start.getHours() < 10 ? "0" + start.getHours() : start
        .getHours();
      var end_hour = end.getHours() < 10 ? "0" + end.getHours() : end
        .getHours();
      var start_minutes = start.getMinutes() < 10 ? "0" + start.getMinutes()
        : start.getMinutes();
      var end_minutes = end.getMinutes() < 10 ? "0" + end.getMinutes() : end
        .getMinutes();

      date = start.getFullYear() + "-" + start_month + "-" + start_day + " "
        + start_hour + ":" + start_minutes + " - " + end.getFullYear() + "-"
        + end_month + "-" + end_day + " " + end_hour + ":" + end_minutes;
    }
  } else if (start) {
    // Cas d'une seule date de début
    var start_month = (start.getMonth() + 1) < 10 ? "0"
      + (start.getMonth() + 1) : (start.getMonth() + 1);
    var start_day = start.getDate() < 10 ? "0" + start.getDate() : start
      .getDate();
    if (allDay) {
      date = start.getFullYear() + "-" + start_month + "-" + start_day;
    } else {
      var start_hour = start.getHours() < 10 ? "0" + start.getHours() : start
        .getHours();
      var start_minutes = start.getMinutes() < 10 ? "0" + start.getMinutes()
        : start.getMinutes();
      date = start.getFullYear() + "-" + start_month + "-" + start_day + " "
        + start_hour + ":" + start_minutes;
    }
  }
  return date;
}
