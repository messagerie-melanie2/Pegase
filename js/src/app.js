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
function poll_app() {
	this.labels = {};

	// environment defaults
	this.env = {
	};

	// set environment variable(s)
	this.set_env = function (p, value) {
		if (p != null && typeof p === 'object' && !value)
			for (var n in p)
				this.env[n] = p[n];
		else
			this.env[p] = value;
	};

	// add a localized label to the client environment
	this.add_label = function (p, value) {
		if (typeof p == 'string')
			this.labels[p] = value;
		else if (typeof p == 'object')
			$.extend(this.labels, p);
	};

	this.show_message = function (msg, type) {
		$('.message').html('<div class="' + type + '">' + msg + '</div>');
		$('.message').fadeIn().delay(3000).fadeOut('slow');
	}

	this.show_loading = function (msg) {
		$('.loading .loading_message').text(msg);
		$('.loading').show();
		document.body.style.cursor = 'wait';
	}

	this.hide_loading = function () {
		$('.loading').hide();
		document.body.style.cursor = 'default';
	}

	this.show_popup = function (e, popup) {
		e.preventDefault();
		e.stopPropagation();
		if ($('#' + popup).is(':visible')) {
			$('#' + popup).hide();
			$('.button-' + popup).removeClass('open');
		}
		else {
			$('#' + popup).show();
			$('.button-' + popup).addClass('open');
		}
		$('.popup').each(function () {
			if ($(this).attr('id') != popup
				&& $(this).is(':visible')) {
				$('.button-' + $(this).attr('id')).removeClass('open');
				$(this).hide();
			}
		});
	}

	this.command = function (command, args) {
		try {
			command(args);
		}
		catch (err) {
			// Handle error(s) here
		}
	}

	this.confirm = function (aMessage, aYesButton, aNoButton, aYesCallback, aNoCallback) {
		// Gestion de la skin mobile
		if (this.env.mobile) {
			if (confirm(aMessage)) {
				aYesCallback();
			}
			else {
				aNoCallback();
			}
		}
		else {
			// Génération des boutons
			var buttons = {};
			buttons[aYesButton] = function () {
				aYesCallback();
				$(this).dialog("close");
			};
			buttons[aNoButton] = function () {
				aNoCallback();
				$(this).dialog("close");
			};
			$('<div></div>').appendTo('body')
				.html('<div class="dialog_message">' + aMessage + '</div>')
				.dialog({
					modal: true,
					zIndex: 10000,
					width: '450px',
					resizable: false,
					buttons: buttons,
					close: function (event, ui) {
						$(this).remove();
					}
				});
		}
	}

	if ($.ui.dialog) {
		$.ui.dialog.prototype._focusTabbable = function () {};
	}

	this.popup = function (aPopupId, aCloseCallback) {
		if (aPopupId == "confirmation_rdv"){
			$('#' + aPopupId)
			.dialog({
				modal: false,
				draggable: false,
				zIndex: 10000,
				width: '350px',
				maxHeight: 500,
				resizable: false,
				autoOpen: false,
				position: { my: "center center", at: "center center", of: window },
				close: aCloseCallback,
			})
			.parent().css({ position: "fixed" }).end().dialog('open');
		}else{
		$('#' + aPopupId)
			.dialog({
				modal: false,
				draggable: false,
				zIndex: 10000,
				width: '350px',
				maxHeight: 500,
				resizable: false,
				autoOpen: false,
				position: { my: "right bottom", at: "right-20 bottom-30", of: window },
				close: aCloseCallback,
			})
			.parent().css({ position: "fixed" }).end().dialog('open');
	}
}
}

$(document).ready(function () {
	$('.message').fadeIn().delay(3000).fadeOut('slow');
	// List expanded
	var expanded = JSON.parse(sessionStorage.getItem('expanded'));
	if (expanded) {
		for (var prop in expanded) {
			if (expanded[prop]) {
				if (!$('#' + prop + ' > .folder > .treetoggle').hasClass('expanded')) {
					$('#' + prop + ' > .folder > .treetoggle').addClass('expanded');
					$('#' + prop + ' > .children').show();
				}
			}
			else {
				if ($('#' + prop + ' > .folder > .treetoggle').hasClass('expanded')) {
					$('#' + prop + ' > .folder > .treetoggle').removeClass('expanded');
					$('#' + prop + ' > .children').hide();
				}
			}
		}
	}
});
$(document).on({
	click: function () {
		$('.message').hide();
	}
}, ".message"); //pass the element as an argument to .on
$(document).on({
	click: function () {
		var expanded = JSON.parse(sessionStorage.getItem('expanded'));
		if (!expanded) {
			expanded = {};
		}
		if ($(this).hasClass('expanded')) {
			$(this).parent().parent().find('.children').hide();
			$(this).removeClass('expanded');
			expanded[$(this).parent().parent().attr('id')] = false
		}
		else {
			$(this).parent().parent().find('.children').show();
			$(this).addClass('expanded');
			expanded[$(this).parent().parent().attr('id')] = true
		}
		sessionStorage.setItem('expanded', JSON.stringify(expanded));
	}
}, ".treetoggle"); //pass the element as an argument to .on
$(document).on({
	click: function () {
		$('.popup').each(function () {
			if ($(this).is(':visible')) {
				$('.button-' + $(this).attr('id')).removeClass('open');
				$(this).hide();
			}
		});
	}
}, "html"); //pass the element as an argument to .on
$(document).on({
	click: function (e) {
		e.stopPropagation();
	}
}, ".popup"); //pass the element as an argument to .on

// Affichage de l'aide dans un pop up dialog
$(document).on({
	click: function (e) {
		var help_map = {
			"main": "1-PageAccueil.html",
			"edit": "1-NouveauSondage.html",
			"edit_date": "2-SondageDeDate.html",
			"edit_prop": "3-SondageLibre.html",
			"edit_end": "1-NouveauSondage.html",
			"show": "2-VosParticipations.html",
			"default": "1-PageAccueil.html",
		};
		// Récupération de la page
		if (help_map[poll.env.page]) {
			var page = "/help/fr_FR/co/" + help_map[poll.env.page];
		}
		else {
			var page = "/help/fr_FR/co/" + help_map["default"];
		}
		// Ouverture du dialog
		$('<div id="help_display_dialog"></div>').appendTo('body')
			.html('<iframe style="border: 0px; margin: 0px; " src="' + page + '" width="100%" height="100%"></iframe>')
			.dialog({
				modal: false,
				zIndex: 10000,
				width: 850,
				height: 500,
				resizable: true,
				close: function (event, ui) {
					$(this).remove();
				}
			}).dialog('open');
	}
}, "#help-page-button"); //pass the element as an argument to .on

$(document).on({
	change: function(){
		var url = location.href;
		var regex = new RegExp('username' + '=[^&$]*', 'i');
		if(regex.test(url)){
			window.location.search = window.location.search.replace(regex, 'username=' + this.value);
		}else{
			window.location.search += "&username=" + this.value;
		}
	}
}, "#balselect");

//Copier l'url du sondage
function copy_url(args) {
	/* Get the text field */
	var copyText = document.getElementById("input_url");

	/* Select the text field */
	copyText.select();

	/* Copy the text inside the text field */
	document.execCommand("copy");

	var old_text = $('#head .poll_url .copy_url').text();
	$('#head .poll_url .copy_url').text(poll.labels['Copied URL']);
	setTimeout(function () { $('#head .poll_url .copy_url').text(old_text); }, 3000);
}
