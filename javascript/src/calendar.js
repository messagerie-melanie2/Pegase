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
// Ce fichier permet de gérer le calendrier pour la gestion des dates
// Il va afficher le calendrier et gérer les intéractions
$(document).ready(function() {
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	
	var calendar = $('#calendar').fullCalendar({
		header: {
			left: 'agendaDay,agendaWeek,month',
			center: 'title',
			right: 'today,prev,next'
		},
		aspectRatio: 1.8,
		selectable: true,
		selectHelper: false,
		select: function(start, end, allDay) {
		  if (!dateExist(start, end, allDay)) {
		    var id = addNewDate(start, end, allDay);
	      var event = {
	          id: id,
	          title: poll.env.poll_title,
	          start: start,
	          end: end,
	          allDay: allDay
	        };
	      calendar.fullCalendar('renderEvent',
	        event,
	        true // make the event "stick"
	      );
		  }
		},
		eventMouseover: function(calEvent, domEvent) {
		  if (calEvent.id.indexOf('edit_date') == -1) return;
			var layer =	"<div id='events-layer' class='fc-transparent' style='position:absolute; width:100%; height:100%; top:2px; text-align:right; z-index:100; right: 2px;'> <a> <img border='0' width='12px' src='skins/"+poll.env.skin+"/images/1395421411_white_delete.png' title='"+poll.labels.Delete+"' onClick='deleteEvent("+calEvent.id+");'></a></div>";
			$(this).append(layer);
		},   
		eventMouseout: function(calEvent, domEvent) {
			$("#events-layer").remove();
		},
		eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			updateEvent(event);
	    },
	    eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
	    	updateEvent(event);
	    },
			
		weekends: true,
		weekNumbers: false,
		weekNumberCalculation: 'iso',
		weekNumberTitle: 'S',
		
		// time formats
		titleFormat: {
			month: 'MMMM yyyy',
			week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
			day: 'dddd, d MMM, yyyy'
		},
		columnFormat: {
			month: 'ddd',
			week: 'ddd d/M',
			day: 'dddd d/M'
		},
		timeFormat: { // for event elements
			'': 'HH:mm', // default
			agenda: 'HH:mm{ - HH:mm}'
		},	
		axisFormat: 'HH:mm',
		defaultView: 'agendaWeek',
		firstHour: 10,
		
		// editing
		editable: true,
		eventColor: '#6B7F9F',
		
		// locale
		firstDay: 1,
		monthNames: [poll.labels.January, poll.labels.February, poll.labels.March, poll.labels.April, poll.labels.May, poll.labels.June, poll.labels.July, poll.labels.August, poll.labels.September, poll.labels.October, poll.labels.November, poll.labels.December],
		monthNamesShort: [poll.labels.Jan, poll.labels.Feb, poll.labels.Mar, poll.labels.Apr, poll.labels.May, poll.labels.Jun, poll.labels.Jul, poll.labels.Aug, poll.labels.Sep, poll.labels.Oct, poll.labels.Nov, poll.labels.Dec],
		dayNames: [poll.labels.Sunday, poll.labels.Monday, poll.labels.Tuesday, poll.labels.Wednesday, poll.labels.Thursday, poll.labels.Friday, poll.labels.Saturday],
		dayNamesShort: [poll.labels.Sun, poll.labels.Mon, poll.labels.Tue, poll.labels.Wed, poll.labels.Thu, poll.labels.Fri, poll.labels.Sat],
		allDayText: poll.labels['All day'],
		buttonText: {
			prev: "<span class='fc-text-arrow'>&lsaquo;</span>",
			next: "<span class='fc-text-arrow'>&rsaquo;</span>",
			prevYear: "<span class='fc-text-arrow'>&laquo;</span>",
			nextYear: "<span class='fc-text-arrow'>&raquo;</span>",
			today: poll.labels.Today,
			month: poll.labels.Month,
			week: poll.labels.Week,
			day: poll.labels.Day
		},
		
//		// Liste les évènements
//		events: getAllEvents()
		eventSources: [
		               getAllEvents(),
		           ]

	});
	$( "#add_new_date" ).click(function() {
		addDateDiv('');
		return false;
	});
	$( ".change_type_poll" ).click(function () {
		return confirm(poll.labels['Are you sure ? Not saved proposals are lost']);
	});
	$( "#calendar" ).resizable();
	// Positionne le calendrier sur la premiere date
	if (poll.env.first_prop_date) {
		var date = poll.env.first_prop_date.split(" - ");
		$('#calendar').fullCalendar('gotoDate', dateForm(date[0]));
	}
	// Ajout du bouton de masquage des agendas
	$('#calendar td.fc-header-right').append('<span style="-moz-user-select: none;" unselectable="on" class="fc-button fc-button-hide-calendar fc-state-default fc-corner-right">'+poll.labels['show calendar']+'</span>');
	$('#calendar td.fc-header-right').append('<span class="fc-header-legend" style="display: none;"><span class="fc-header-legend-name">'+poll.labels['Your freebusy']+':</span><span class="fc-header-legend-confirmed">'+poll.labels['Confirmed']+'</span><span class="fc-header-legend-tentative">'+poll.labels['Tentative']+'</span><span class="fc-header-legend-none">'+poll.labels['None']+'</span></span>');
	$('#calendar td.fc-header-right span.fc-button-next').removeClass('fc-corner-right');
	$( "#calendar td.fc-header-right span.fc-button-hide-calendar" ).hover(
    function() {
	    $(this).addClass('fc-state-hover');
    }, function() {
      $(this).removeClass('fc-state-hover');
    }
  );
	$( "#calendar td.fc-header-right span.fc-button-hide-calendar" ).click(function() {
    if ($(this).hasClass('hide-calendar')) {
      $(this).removeClass('hide-calendar');
      $(this).removeClass('fc-state-active');
      $('#calendar span.fc-header-legend').hide();
      calendar.fullCalendar('removeEventSource',
          getM2EventSource()
        );
    }
    else {
      $(this).addClass('hide-calendar');      
      $(this).addClass('fc-state-active');
      $('#calendar span.fc-header-legend').show();
      calendar.fullCalendar('addEventSource',
          getM2EventSource()
        );
    }
  });
});
/**
 * Retourne la source vers l'agenda Melanie2
 * @returns json
 */
function getM2EventSource() {
  return {
    url: './?_p=ajax&_a=get_user_events_json',
    type: 'POST',
    color: '#EBF1F6',   // a non-ajax option
    textColor: 'black', // a non-ajax option
    cache: true,
    editable: false,
    cache: true,
  };
}
/**
 * Retourne une Date formatté
 * @param date
 * @returns
 */
function dateForm(date) {
	if (date.length == 16
			|| date.length == 19) {
		var tmp = date.split(" ");
		var date_tmp = tmp[0].split("-");
		var time_tmp = tmp[1].split(":");
		if (date_tmp[0] 
				&& date_tmp[1]
				&& date_tmp[2]
				&& time_tmp[0]
				&& time_tmp[1]) {
			// Formatte la date avec toutes les données
			return new Date(date_tmp[0], date_tmp[1] - 1, date_tmp[2], time_tmp[0], time_tmp[1]);
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
	$( "#props_list .edit_date" ).each(function( ) {
		if ($(this).attr("value")
				|| this.value) {
			var date = $(this).attr("value").split(" - ");
			if (!date) date = this.value.split(" - ");
			var id = $(this).attr("id");
			if (!id) id = this.id;
			if (date[1]) {
				// Ajout de l'évènement
				events.push({
					id: id,
					title: poll.env.poll_title,
					start: dateForm(date[0]),
					end: dateForm(date[1]),
					allDay: date[0].length == 10 && date[1].length == 10
			    });
			} else {
				events.push({
					id: id,
					title: poll.env.poll_title,
					start: dateForm(date[0]),
					allDay: date[0].length == 10
			    });
			}
		}
	});
	return events;
}
/**
 * Ajoute le div de date
 * @param value
 */
function addDateDiv(value) {
	if (poll.env.mobile) {
		// Gets the number of elements with class yourClass
		var numItems = $('.edit_date_start').length + 1;
		var id = "edit_date" + numItems;
		var html = '<div class="pure-control-group">';
			html += '<label for="edit_date_start'+numItems+'">'+poll.labels['Edit date (Y-m-d H:i:s)']+'</label>';
			html += ''+poll.labels['Start']+' ';
			html += '<input id="edit_date_start'+numItems+'" name="edit_date_start'+numItems+'" class="edit_date_start" type="date" value="">';
			html += '<input id="edit_time_start'+numItems+'" name="edit_time_start'+numItems+'" class="edit_time_start" type="time" value="">';
			html += ''+poll.labels['End']+' ';
			html += '<input id="edit_date_end'+numItems+'" name="edit_date_end'+numItems+'" class="edit_date_end" type="date" value="">';
			html += '<input id="edit_time_end'+numItems+'" name="edit_time_end'+numItems+'" class="edit_time_end" type="time" value="">';
			html += '</div>';
			html += '<br>';
	} else {
		// Gets the number of elements with class yourClass
		var numItems = 0;
		$( ".edit_date" ).each(function( ) {
			var val = parseInt(this.id.replace("edit_date",""));
			if (val > numItems) {
				numItems = val;
			}
		});
		numItems++;
		var id = "edit_date" + numItems;
		var html = '<div class="pure-control-group">';
		html += '<input style="margin-left: 25%; width: 50%;" id="'+id+'" type="text" name="'+id+'" class="edit_date" value="'+value+'" readonly> ';
		html += '<a class="pure-button pure-button-delete-date" "style"="padding-top: 3px;" onclick="deleteDate(\''+id+'\');">';
		html += '</a>';
		html += '</div>';
	}
	$('#props_list').append(html);
	return id;
}

/**
 * Test si la date existe déjà
 * @param start
 * @param end
 * @param allDay
 * @returns {Boolean}
 */
function dateExist(start, end, allDay) {
  var date = getDateFromData(start, end, allDay);
  var exist = false;
  $( ".edit_date" ).each(function( ) {
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
	var date = getDateFromData(start, end, allDay);
	// Parcourir les valeurs pour en trouver une vide
	var id;
	if (poll.env.mobile) {
		$( ".edit_date" ).each(function( ) {
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
	}
	// Retourne l'id
	return id;
}
/**
 * Mise à jour de l'événement
 * @param event
 */
function updateEvent(event) {
	var date = getDateFromData(event.start, event.end, event.allDay);
	$( ".edit_date" ).each(function( ) {
		if (this.id == event.id) {
			$(this).attr("value", date);
			this.value = date;
			return false;
		}
	});
}
/**
 * Suppression d'une date dans la liste
 * Supprime l'évènement en même temps
 * @param id
 * @returns
 */
function deleteDate(id) {
	$('#calendar').fullCalendar('removeEvents', id);
	$('#'+id).parent().remove();
	return false;
}
/**
 * Suppression d'un évènement
 * @param event
 */
function deleteEvent(event) {
	// Suppression de l'évènement
	$( ".edit_date" ).each(function( ) {
		if (this.id == event.id) {
			//$(this).attr("value", "");
			//$(this).value = "";
			$(this).parent().remove();
			return false;
		}
	});
	$('#calendar').fullCalendar('removeEvents', event.id);
}
/**
 * Met à jour le calendrier en fonction de la date passée
 * @param id
 */
function modifyCalendarEvent(id, value) {
	var events = $('#calendar').fullCalendar('clientEvents', id);

	if (events.length == 0) {
		if (value) {
			var date = value.split(" - ");
			// Création
			var title = $('#poll_title').text();
			var event;
			if (date[1]) {
				// Generation de l'évènement
				event = {
						id: id,
						title: title,
						start: new Date(date[0]),
						end: new Date(date[1]),
						allDay: date[0].length == 10 && date[1].length == 10
					};
			} else {
				// Generation de l'évènement
				event = {
						id: id,
						title: title,
						start: new Date(date[0]),
						allDay: date[0].length == 10
					};
			}
			$('#calendar').fullCalendar('renderEvent',
				event,
				true // make the event "stick"
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
				event.start = new Date(date[0]);
				if (date[1]) {
					event.end = new Date(date[1]);
					event.allDay = date[0].length == 10 && date[1].length == 10
				} else {
					event.allDay = date[0].length == 10;
				}
				$('#calendar').fullCalendar('updateEvent', event);
			}
		}
	}
}
/**
 * Génération de la date en fonction des données passées
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
		var start_month = (start.getMonth() + 1) < 10 ? "0" + (start.getMonth() + 1) : (start.getMonth() + 1);
		var end_month = (end.getMonth() + 1) < 10 ? "0" + (end.getMonth() + 1) : (end.getMonth() + 1);
		var start_day = start.getDate() < 10 ? "0" + start.getDate() : start.getDate();
		var end_day = end.getDate() < 10 ? "0" + end.getDate() : end.getDate();
		if (allDay) {
			date = start.getFullYear() + "-" + start_month + "-" + start_day
				+ " - " + 
				end.getFullYear() + "-" + end_month + "-" + end_day;
		} else {
			var start_hour =  start.getHours() < 10 ? "0" + start.getHours() : start.getHours();
			var end_hour =  end.getHours() < 10 ? "0" + end.getHours() : end.getHours();
			var start_minutes =  start.getMinutes() < 10 ? "0" + start.getMinutes() : start.getMinutes();
			var end_minutes =  end.getMinutes() < 10 ? "0" + end.getMinutes() : end.getMinutes();
			
			date = start.getFullYear() + "-" + start_month + "-" + start_day + " " + start_hour + ":" + start_minutes
				+ " - " + 
				end.getFullYear() + "-" + end_month + "-" + end_day + " " + end_hour + ":" + end_minutes;
		}
	} else if (start) {
		// Cas d'une seule date de début
		var start_month = (start.getMonth() + 1) < 10 ? "0" + (start.getMonth() + 1) : (start.getMonth() + 1);
		var start_day = start.getDate() < 10 ? "0" + start.getDate() : start.getDate();
		if (allDay) {
			date = start.getFullYear() + "-" + start_month + "-" + start_day;
		} else {
			var start_hour =  start.getHours() < 10 ? "0" + start.getHours() : start.getHours();
			var start_minutes =  start.getMinutes() < 10 ? "0" + start.getMinutes() : start.getMinutes();
			date = start.getFullYear() + "-" + start_month + "-" + start_day + " " + start_hour + ":" + start_minutes;
		}
	}
	return date;
}
