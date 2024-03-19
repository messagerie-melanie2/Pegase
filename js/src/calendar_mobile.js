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
  $( "#add_new_date" ).click(function() {
    addDateDiv('');
    $("#edit_date_page").trigger('create');
    return false;
  });
  $( ".change_type_poll" ).click(function () {
    return confirm(poll.labels['Are you sure ? Not saved proposals are lost']);
  });
});
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
		html += '<a class="pure-button pure-button-delete-date" "style"="padding-top: 3px;" onclick="deleteDate(\''+id+'\');" href = "#">';
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
