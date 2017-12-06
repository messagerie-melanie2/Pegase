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
function poll_app()
{
	this.labels = {};
	
	// environment defaults
	this.env = {
	};
	
	// set environment variable(s)
	this.set_env = function(p, value)
	{
		if (p != null && typeof p === 'object' && !value)
	      for (var n in p)
	        this.env[n] = p[n];
	    else
	      this.env[p] = value;
	};

	// add a localized label to the client environment
	this.add_label = function(p, value)
	{
	    if (typeof p == 'string')
	      this.labels[p] = value;
	    else if (typeof p == 'object')
	      $.extend(this.labels, p);
	};
	
	this.show_message = function(msg, type)
	{
		$('.message').html('<div class="'+type+'">'+msg+'</div>');
		$('.message').fadeIn().delay(3000).fadeOut('slow'); 
	}
	
	this.show_loading = function(msg)
	{
		$('.loading .loading_message').text(msg);
		$('.loading').show();
		document.body.style.cursor = 'wait';
	}
	
	this.hide_loading = function()
	{
		$('.loading').hide();
		document.body.style.cursor = 'default';
	}
	
	this.show_popup = function(e, popup)
	{
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
		$('.popup').each(function() {
			if ($(this).attr('id') != popup
					&& $(this).is(':visible')) {
				$('.button-' + $(this).attr('id')).removeClass('open');
				$(this).hide();
			}
		});
	}
	
	this.command = function(command, args)
	{
		try {
			command(args);
	    }
	    catch (err) {
	        // Handle error(s) here
	    }
	}
	
	this.confirm = function(aMessage, aYesButton, aNoButton, aYesCallback, aNoCallback)
	{
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
	
	this.popup = function(aPopupId, aCloseCallback) {
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
            .parent().css({position:"fixed"}).end().dialog('open');
	}
}
$(document).ready(function() {
	$('.message').fadeIn().delay(3000).fadeOut('slow');
});
$(document).on({
    click: function () {
    	$('.message').hide();
    }
}, ".message"); //pass the element as an argument to .on
$(document).on({
    click: function () {
    	$('.popup').each(function() {
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

