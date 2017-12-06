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
  // Desactiver l'ajax pour les formulaires jquery mobile
  $.mobile.ajaxEnabled = false;
  
  var lastScrollLeft = 0;
  $('div#proposals_div').scroll(function() {
      var documentScrollLeft = $('div#proposals_div').scrollLeft();
      if (lastScrollLeft != documentScrollLeft) {
        if (lastScrollLeft < documentScrollLeft) {
           if (lastScrollLeft == 0 && !$('#proposals_div').hasClass('swipe') ) {
             $('#proposals_div').addClass('swipe');
             $('#proposals_table .first_col').addClass('swipe');
             $('#proposals_table .nb_attendees').addClass('swipe');
             $('#proposals_table td.user_list_name').addClass('swipe');
             $('#proposals_table td.user_freebusy_first_col').addClass('swipe');
           }
        }
        else {
          if (documentScrollLeft == 0 && $('div#proposals_div')[0].scrollWidth > $('div#proposals_div').width() && $('#proposals_div').hasClass('swipe')) {
            $('#proposals_div').removeClass('swipe');
            $('#proposals_table .first_col').removeClass('swipe');
            $('#proposals_table .nb_attendees').removeClass('swipe');
            $('#proposals_table td.user_list_name').removeClass('swipe');
            $('#proposals_table td.user_freebusy_first_col').removeClass('swipe');
          }
        }
        lastScrollLeft = documentScrollLeft;
      }
  });
});