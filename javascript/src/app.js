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
function poll_app() {
  this.labels = {};

  // environment defaults
  this.env = {};

  // set environment variable(s)
  this.set_env = function(p, value) {
    if (p != null && typeof p === 'object' && !value)
      for ( var n in p)
        this.env[n] = p[n];
    else
      this.env[p] = value;
  };

  // add a localized label to the client environment
  this.add_label = function(p, value) {
    if (typeof p == 'string')
      this.labels[p] = value;
    else if (typeof p == 'object') $.extend(this.labels, p);
  };

  this.show_message = function(msg, type) {
    $('.message').html('<div class="' + type + '">' + msg + '</div>');
    $('.message').fadeIn().delay(3000).fadeOut('slow');
  }

  this.show_loading = function(msg) {
    $('.loading .loading_message').text(msg);
    $('.loading').show();
    document.body.style.cursor = 'wait';
  }

  this.hide_loading = function() {
    $('.loading').hide();
    document.body.style.cursor = 'default';
  }

  this.show_popup = function(e, popup) {
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
      if ($(this).attr('id') != popup && $(this).is(':visible')) {
        $('.button-' + $(this).attr('id')).removeClass('open');
        $(this).hide();
      }
    });
  }

  this.command = function(command, args) {
    try {
      command(args);
    }
    catch (err) {
      // Handle error(s) here
    }
  }

  this.confirm = function(aMessage, aYesButton, aNoButton, aYesCallback,
      aNoCallback) {
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
      buttons[aYesButton] = function() {
        aYesCallback();
        $(this).dialog("close");
      };
      buttons[aNoButton] = function() {
        aNoCallback();
        $(this).dialog("close");
      };
      $('<div></div>').appendTo('body').html('<div class="dialog_message">'
          + aMessage + '</div>').dialog({
        modal : true,
        zIndex : 10000,
        width : '450px',
        resizable : false,
        buttons : buttons,
        close : function(event, ui) {
          $(this).remove();
        }
      });
    }
  }

  this.popup = function(aPopupId, aCloseCallback) {
    $('#' + aPopupId).dialog({
      modal : false,
      draggable : false,
      zIndex : 10000,
      width : '350px',
      maxHeight : 500,
      resizable : false,
      autoOpen : false,
      position : {
        my : "right bottom",
        at : "right-20 bottom-30",
        of : window
      },
      close : aCloseCallback,
    }).parent().css({
      position : "fixed"
    }).end().dialog('open');
  }
}
$(document).ready(function() {
  $('.message').fadeIn().delay(3000).fadeOut('slow');
});
$(document).on({
  click : function() {
    $('.message').hide();
  }
}, ".message"); // pass the element as an argument to .on
$(document).on({
  click : function() {
    $('.popup').each(function() {
      if ($(this).is(':visible')) {
        $('.button-' + $(this).attr('id')).removeClass('open');
        $(this).hide();
      }
    });
  }
}, "html"); // pass the element as an argument to .on
$(document).on({
  click : function(e) {
    e.stopPropagation();
  }
}, ".popup"); // pass the element as an argument to .on

// Affichage de l'aide dans un pop up dialog
$(document).on({
  click : function(e) {
    if (!poll.env.help_pages_mapping) {
      return;
    }
    var help_dir = "/help/" + poll.env.localization + "/";
    // Récupération de la page
    if (poll.env.help_pages_mapping[poll.env.page]) {
      var page = help_dir + poll.env.help_pages_mapping[poll.env.page];
    }
    else {
      var page = help_dir + poll.env.help_pages_mapping["default"];
    }
    // Ouverture du dialog
    $('<div id="help_display_dialog"></div>').appendTo('body')
        .html('<iframe style="border: 0px; margin: 0px; " src="' + page
            + '" width="100%" height="100%"></iframe>').dialog({
          modal : false,
          zIndex : 10000,
          width : 850,
          height : 500,
          resizable : true,
          close : function(event, ui) {
            $(this).remove();
          }
        }).dialog('open');
  }
}, "#help-page-button"); // pass the element as an argument
// to .on

/** **** AUTOCOMPLETE ******* */

window.autoresultItemIndex = -1;
window.autoresultItemText = "";
window.timer = null;
window.s_focusin = false;

$(document).on({
  mouseenter : function() {
    // stuff to do on mouse enter
    if (window.autoresultItemIndex == -1)
      window.autoresultItemText = $("form.autocomplete input.autocomplete")
          .val();
    var cssClass = "autocomplete-results-item-hover";
    window.autoresultItemIndex = -1;
    var id = $(this).attr('id');
    $("form.autocomplete .autocomplete-results .autocomplete-results-item")
        .each(function() {
          window.autoresultItemIndex++;
          if ($(this).attr('id') == id) return false;
        });
    $("form.autocomplete .autocomplete-results .autocomplete-results-item")
        .removeClass(cssClass);
    $(this).addClass(cssClass);
  },
  mouseleave : function() {
    // stuff to do on mouse leave
    var cssClass = "autocomplete-results-item-hover";
    $(this).removeClass(cssClass);
    window.autoresultItemIndex = -1;
  },
  click : function() {
    var element = $(this);
    $("form.autocomplete .autocomplete-results").html("");
    $("form.autocomplete .autocomplete-results").hide();
    $("form.autocomplete input.autocomplete").val("");
    AutocompleteCallback(element);
  }
}, "form.autocomplete .autocomplete-results .autocomplete-results-item");
// pass the element as an argument to .on

$(document)
    .ready(function() {
      $("form.autocomplete input.autocomplete")
          .keyup(function(e) {
            if (e.keyCode == 40) {
              Navigate(1);
            }
            else if (e.keyCode == 38) {
              Navigate(-1);
            }
            else if (e.keyCode == 27) {
              $("form.autocomplete .autocomplete-results").html("");
              $("form.autocomplete .autocomplete-results").hide();
              window.autoresultItemIndex = -1;
            }
            else if (e.keyCode == 13) {
              if ($("form.autocomplete .autocomplete-results .autocomplete-results-item.autocomplete-results-item-hover").length) {
                var element = $("form.autocomplete .autocomplete-results .autocomplete-results-item.autocomplete-results-item-hover");
              }
              else if ($("form.autocomplete .autocomplete-results .autocomplete-results-item").size() == 1) {
                var element = $("form.autocomplete .autocomplete-results .autocomplete-results-item");
              }
              else if ($("form.autocomplete input.autocomplete").val() == "") {
                return;
              }

              $("form.autocomplete .autocomplete-results").html("");
              $("form.autocomplete .autocomplete-results").hide();
              window.autoresultItemIndex = -1;
              if (element) {
                $("form.autocomplete input.autocomplete").val("");
              }              
              AutocompleteCallback(element);
            }
            else if ($("form.autocomplete input.autocomplete").val().length > 2) {
              clearTimeout(timer);
              timer = setTimeout(callSearchAjax, 200);
            }
            else {
              $("form.autocomplete .autocomplete-results").html("");
              $("form.autocomplete .autocomplete-results").hide();
              window.autoresultItemIndex = -1;
            }
          });
      $("form.autocomplete input.autocomplete").focusin(function() {
        if ($("form.autocomplete .autocomplete-results").val().length > 2) {
          clearTimeout(timer);
          timer = setTimeout(callSearchAjax, 400);
        }
        else {
          $("form.autocomplete .autocomplete-results").html("");
          $("form.autocomplete .autocomplete-results").hide();
          window.autoresultItemIndex = -1;
        }
        s_focusin = true;
        // Select input field contents
        $(this).select();
      });
      $("form.autocomplete input.autocomplete").focusout(function() {
        setTimeout(function() {
          $("form.autocomplete .autocomplete-results").html("");
          $("form.autocomplete .autocomplete-results").hide();
          window.autoresultItemIndex = -1;
        }, 200);
      });
      $("form.autocomplete input.autocomplete").mouseup(function(e) {
        if (s_focusin) e.preventDefault();
        s_focusin = false;
      });
      $("form.autocomplete").submit(function(e) {
        e.preventDefault();
        return false;
      });
    });

function callSearchAjax() {
  poll.show_loading(poll.labels['Loading...']);
  $
      .ajax({
        type : 'GET',
        url : '?_p=ajax&_a=autocomplete_search',
        data : {
          token : poll.env.csrf_token,
          search : $("form.autocomplete input.autocomplete").val(),
        },
        success : function(data) {
          if (data.success) {
            $("form.autocomplete .autocomplete-results").show();
            $("form.autocomplete .autocomplete-results").html(data.text);
            if ($("form.autocomplete .autocomplete-results .autocomplete-results-item")
                .size() == 1) {
              var cssClass = "autocomplete-results-item-hover";
              $("form.autocomplete .autocomplete-results .autocomplete-results-item")
                  .addClass(cssClass);
            }
          }
          poll.hide_loading();
        },
        error : function(o, status, err) {
          poll.hide_loading();
        }
      });
}

var Navigate = function(diff) {
  if (!$("form.autocomplete .autocomplete-results").is(":visible")) {
    if ($("form.autocomplete input.autocomplete").val().length > 2) {
      clearTimeout(timer);
      timer = setTimeout(callSearchAjax, 400);
    }
    return;
  }
  if (window.autoresultItemIndex == -1)
    window.autoresultItemText = $("form.autocomplete input.autocomplete").val();
  window.autoresultItemIndex += diff;
  var oBoxCollection = $("form.autocomplete .autocomplete-results .autocomplete-results-item");
  var cssClass = "autocomplete-results-item-hover";
  if (autoresultItemIndex >= oBoxCollection.length || autoresultItemIndex == -1) {
    autoresultItemIndex = -1;
    oBoxCollection.removeClass(cssClass);
  }
  else {
    if (autoresultItemIndex < -1) {
      autoresultItemIndex = oBoxCollection.length - 1;
    }
    oBoxCollection.removeClass(cssClass).eq(autoresultItemIndex)
        .addClass(cssClass);
  }
};
