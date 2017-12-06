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
  $(".button_delete_poll").click(
      function(event) {
        event.preventDefault();
        var _this = $(this);
        poll.confirm(
            '<div>' +
            poll.labels['Are you sure you want to delete the poll ?']
            + '</div>'
            + '<br><form><label><input type="checkbox" class="delete_poll_notification" name="send_notification" value="true" checked> '+poll.labels['Notify attendees']+'</label><br></form>',
            poll.labels['Yes'], poll.labels['No'], function Yes() {
              if ($('input.delete_poll_notification:checked').length) {
                window.location.href = _this.attr('href') + '&_send_notif=1';
              }
              else {
                window.location.href = _this.attr('href');
              }              
            }, function No() {

            });
      });
});
