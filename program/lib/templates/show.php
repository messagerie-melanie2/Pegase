<?php

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
namespace Program\Lib\Templates;

// Utilisation des namespaces
use Program\Lib\Request\Request as Request, Program\Lib\Request\Output as o, Program\Lib\Request\Cookie as Cookie, Program\Lib\Request\Session as Session, Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion de l'affichage du sondage
 *
 * @package Lib
 * @subpackage Request
 */
class Show extends Template {
  /**
   * Tableau HTML pour l'affichage du sondage
   *
   * @var \Program\Lib\HTML\html_table
   */
  private static $table;
  /**
   * Array contenant la liste des propositions du sondage courant
   *
   * @var array
   */
  private static $proposals;
  /**
   * Array contenant la liste des propositions validées par l'organisateur
   *
   * @var array
   */
  private static $validate_proposals = [];
  /**
   * Array contenant la liste des réponses du sondage courant
   *
   * @var \Program\Data\Response[]
   */
  private static $responses;
  /**
   * Compte le nombre de réponses par propositions
   *
   * @var array
   */
  private static $nb_resp;
  /**
   * Conserve le nombre max de réponses pour les stats
   *
   * @var int
   */
  private static $max;
  /**
   * Conserve le nombre max de réponses si besoin pour les stats
   *
   * @var int
   */
  private static $max_if_needed;
  /**
   * Défini si l'utilisateur à répondu ou non
   *
   * @var boolean
   */
  private static $user_responded = false;
  /**
   * Compte le nombre de réponses pour les autres utilisateurs
   *
   * @var int
   */
  private static $nb_others_responses = 0;

  /**
   * Execution de la requête
   */
  public static function Process() {
    if (! \Program\Data\Poll::isset_current_poll()) {
      o::set_env("page", "error");
      o::set_env("error", "Poll does not exist");
      return;
    }
    // Passage en version mobile
    Main::MobileVersion();
    // Vérouillage du sondage
    Edit::LockPoll();
    o::set_env("poll_organizer", \Program\Drivers\Driver::get_driver()->getUser(\Program\Data\Poll::get_current_poll()->organizer_id), false);
    // Positionne si c'est un sondage if needed
    o::set_env("poll_if_needed", \Program\Data\Poll::get_current_poll()->if_needed);
    // Position le token pour l'utilisation dans la page
    o::set_env("csrf_token", Session::getCSRFToken());
    // Ajoute le type de sondage
    o::set_env("poll_type", \Program\Data\Poll::get_current_poll()->type);
    // Est-ce que l'utilisateur est authentifié
    o::set_env("user_auth", \Program\Data\User::isset_current_user() && \Program\Data\User::get_current_user()->auth == 1);
    // L'application doit elle envoyer des emails
    o::set_env("send_mail", \Config\IHM::$SEND_MAIL);
    // L'application doit elle enregistrer les événements dans l'agenda
    o::set_env("can_get_freebusy", \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GET_FREEBUSY);
    o::set_env("can_generate_ics", \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS);
    o::set_env("can_write_calendar", \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR);
    // Ajout de la source de la requête si elle existe
    o::set_env("request_source", \Program\Lib\Request\Request::getInputValue("_s", POLL_INPUT_GET));
    // Ajout des labels
    o::add_label([
            'Are you sure you want to delete the poll ?',
            'Notify attendees','Are you sure you want to delete your response ?',
            'Name already exists','Clic to validate this proposal','Clic to unvalidate this proposal',
            'Clic to add this proposal to your calendar','This proposals is already in your calendar',
            'Clic to download ICS of the proposal and add it to your calendar client',
            'Remove','Do you want to send a message to the attendees ?',
            'Would you like to add responses to your calendar as tentative ?',
            'Username','Email address','Yes','No','If needed',
            'Adding prop to your calendar...','Load freebusy...',
            'Would you like to delete tentatives events of this poll from your calendar ?',
            'Deleting tentatives...','Tentatives correctly deleted','Remember to lock the poll when it\'s finished',
            'Lock the poll']);
    // Gestion du téléchargement de l'ICS
    if (o::get_env("action") == ACT_DOWNLOAD_ICS
    		&& \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS
    		&& (\Program\Data\User::isset_current_user() || ! \Program\Data\Poll::get_current_poll()->auth_only)) {
      self::_action_download_ics();
    }
    // Bloquer le modify all si on n'est pas organisateur
    if (o::get_env("action") == ACT_MODIFY_ALL && \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id != \Program\Data\User::get_current_user()->user_id) {
      o::set_env("action", null);
    }
    else if (o::get_env("action") == ACT_MODIFY_ALL) {
      o::set_env("proposals", unserialize(\Program\Data\Poll::get_current_poll()->proposals));
    }
    else if (o::get_env("action") == ACT_ADD_CALENDAR) {
      // Ajoute les données de get pour le javascript
      o::set_env("part_status", \Program\Lib\Request\Request::getInputValue('_part_status', POLL_INPUT_GET));
      o::set_env("prop_key", \Program\Lib\Request\Request::getInputValue('_prop_key', POLL_INPUT_GET));
    }
    $username = Request::getInputValue("user_username", POLL_INPUT_POST);
    $hidden_modify = Request::getInputValue("hidden_modify", POLL_INPUT_POST);
    $hidden_modify_all = Request::getInputValue("hidden_modify_all", POLL_INPUT_POST);
    if (o::get_env("action") == ACT_DELETE && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
      if (Session::validateCSRFToken($csrf_token)) {
        if (\Program\Drivers\Driver::get_driver()->deletePoll(\Program\Data\Poll::get_current_poll()->poll_id)) {
          o::set_env("message", "Poll has been deleted");
          \Program\Data\Poll::set_current_poll(null);
        }
        else {
          o::set_env("error", "Error while deleting the poll");
        }
      }
      else {
        o::set_env("error", "Invalid request");
      }
    }
    elseif (o::get_env("action") == ACT_DELETE_RESPONSE) {
      self::_action_delete_reponse();
    }
    elseif (\Program\Data\Poll::get_current_poll()->locked == 0 && (isset($username) && $username != "" || isset($hidden_modify))) {
      self::_action_modify();
    }
    elseif (isset($_POST['user_username']) && $username == "") {
      o::set_env("error", "Please add your name");
      return;
    }
    elseif (isset($hidden_modify_all) && $hidden_modify_all == \Program\Data\Poll::get_current_poll()->poll_id && \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      self::_action_modify_all();
    }

    if (\Program\Data\Poll::isset_current_poll() && \Program\Data\User::isset_current_user() && \Program\Data\User::get_current_user()->user_id == \Program\Data\Poll::get_current_poll()->organizer_id && \Program\Data\Poll::get_current_poll()->locked == 0 && \Program\Data\Poll::get_current_poll()->type == "date") {
      // Récupération des réponses du sondage
      if (! isset(self::$responses)) {
        self::$responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      }
      // L'application doit elle afficher le pop up de rappel pour le verrouillage du sondage
      o::set_env("show_lock_popup",
          // S'il y a au moins N réponses
          count(self::$responses) >= \Config\IHM::$POPUP_NB_RESPONSES &&
          // Si le sondage existe depuis au moins N secondes
          time() - strtotime(\Program\Data\Poll::get_current_poll()->created) > \Config\IHM::$POPUP_TIME_CREATED);
    }

    if (\Program\Data\Poll::isset_current_poll() && \Program\Data\User::isset_current_user() && \Program\Data\User::get_current_user()->user_id == \Program\Data\Poll::get_current_poll()->organizer_id && \Program\Data\Poll::get_current_poll()->locked == 1) {
      // Récupération des propositions validées
      self::$validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
      // L'application doit elle afficher le pop up de rappel pour la validation des propositions
      o::set_env("show_validate_prop_popup",
          // Si aucune proposition n'est validée
          count(self::$validate_proposals) == 0);
    }

    if (\Program\Data\Poll::isset_current_poll()
        && \Program\Data\Poll::get_current_poll()->type == "date"
        && \Program\Data\User::isset_current_user()
        && \Program\Data\User::get_current_user()->user_id != \Program\Data\Poll::get_current_poll()->organizer_id
        && \Program\Data\Poll::get_current_poll()->locked == 1
        && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
      // Récupération des propositions validées
      self::$validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
      $show_pop_up = false;
      // Récupération des events du sondage
      if (\Program\Data\EventsList::isset_current_eventslist() && \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_CONFIRMED) {
        $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
        foreach (self::$validate_proposals as $prop_value => $value) {
          if (! isset($events[$prop_value])) {
            $show_pop_up = true;
            break;
          }
        }
      }
      else {
        $show_pop_up = true;
      }
      // L'application doit elle afficher le pop up de rappel pour la validation des propositions
      o::set_env("show_add_calendar_popup",
          // Si aucune proposition n'est validée
          count(self::$validate_proposals) > 0 && $show_pop_up);
    }
  }
  /**
   * Génération du tableau HTML contenant la liste des propositions et des réponses des utilisateurs
   */
  public static function GenerateProposalsTable() {
    if (! \Program\Data\Poll::isset_current_poll()) {
      o::set_env("page", "show");
      o::set_env("error", "Poll does not exist");
      return;
    }
    $html = "";
    // Liste des propositions du sondage
    self::$proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
    if (! is_array(self::$proposals))
      self::$proposals = array();
    if (count(self::$proposals) > 0) {
      // Récupération des propositions validées
      self::$validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
      // Récupération des réponses du sondage
      if (! isset(self::$responses)) {
        self::$responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      }
      self::$table = new \Program\Lib\HTML\html_table(array("id" => "proposals_table"));
      // Compte le nombre de réponse
      self::$nb_resp = array();
      self::$max = 0;
      self::$max_if_needed = 0;
      // Gestion des en tête de la table HTML
      if (\Program\Data\Poll::get_current_poll()->type == "prop" || o::get_env("mobile")) {
        // Si c'est un sondage de propositions personnalisées
        self::view_type_prop_headers();
      }
      else {
        // Si c'est un sondage de date, on adapte l'affichage
        self::view_type_date_complex_headers();
      }
      self::$user_responded = false;
      if (o::get_env("action") != ACT_MODIFY_ALL) {
        foreach (self::$responses as $response) {
          if (\Program\Data\User::isset_current_user() && $response->user_id == \Program\Data\User::get_current_user()->user_id && \Program\Data\Poll::get_current_poll()->locked == 0) {
            self::$user_responded = true;
            // Afficher les freebusy de l'utilisateur
            self::view_user_freebusy();
            // Réponse de l'utilisateur courant authentifié, si le sondage n'est pas locké
            self::view_current_user_unlock_response($response);
            break;
          }
          elseif (Session::is_set("user_noauth_id") && Session::is_set("user_noauth_name") && Session::is_set("user_noauth_poll_id") && Session::get("user_noauth_id") == $response->user_id && Session::get("user_noauth_poll_id") == \Program\Data\Poll::get_current_poll()->poll_id && \Program\Data\Poll::get_current_poll()->locked == 0) {
            // Réponse de l'utilisateur courant non authentifié, si le sondage n'est pas locké
            self::view_unauthenticate_current_user_unlock_response($response);
            break;
          }
          elseif (\Program\Data\User::isset_current_user() && $response->user_id == \Program\Data\User::get_current_user()->user_id) {
            // Réponse de l'utilisateur
            self::view_user_response($response);
            break;
          }
          elseif (Session::is_set("user_noauth_id") && Session::is_set("user_noauth_name") && Session::is_set("user_noauth_poll_id") && Session::get("user_noauth_id") == $response->user_id && Session::get("user_noauth_poll_id") == \Program\Data\Poll::get_current_poll()->poll_id) {
            // Réponse de l'utilisateur non authentifié
            self::view_user_response($response);
            break;
          }
        }
        if (\Program\Data\Poll::get_current_poll()->locked == 1 || ! \Program\Data\Poll::get_current_poll()->anonymous || \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
          foreach (self::$responses as $response) {
            if ((! \Program\Data\User::isset_current_user() || $response->user_id != \Program\Data\User::get_current_user()->user_id) && (! Session::is_set("user_noauth_id") || Session::get("user_noauth_id") != $response->user_id)) {
              // Réponse de l'utilisateur
              self::view_user_response($response);
            }
          }
        }
        if (\Program\Data\Poll::get_current_poll()->locked == 1) {
          // Afficher les freebusy de l'utilisateur
          self::view_user_freebusy();
        }
      }
      elseif (o::get_env("action") == ACT_MODIFY_ALL && \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
        foreach (self::$responses as $response) {
          // Ajout des modifications de réponse pour l'utilisateur
          self::view_modify_user_response($response);
        }
      }
      // Si l'utilisateur n'a pas répondu
      if (! self::$user_responded && \Program\Data\Poll::get_current_poll()->locked == 0 && o::get_env("action") != ACT_MODIFY_ALL && (! \Program\Data\Poll::get_current_poll()->auth_only || \Program\Data\User::isset_current_user())) {

        // Ajout du formulaire pour que l'utilisateur puisse répondre
        self::view_new_response();
        // Ajout des freebusy
        self::view_user_freebusy();
      }
      if (\Program\Data\Poll::get_current_poll()->locked == 1 || ! \Program\Data\Poll::get_current_poll()->anonymous || \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
        // Ajout du nombre de réponses par proposition
        self::view_number_responses();
      }
      // Ajout des boutons de validation d'une date si le sondage est vérouillé qu'on est organisateur
      if (\Program\Data\Poll::get_current_poll()->locked == 1) {
        // Ajout des boutons de validation des propositions
        self::view_validation_buttons();
      }
      $hidden_field_csrf_token = new \Program\Lib\HTML\html_hiddenfield(array("name" => "csrf_token","value" => Session::getCSRFToken()));
      // Generation du tableau html
      if (o::get_env("action") != ACT_MODIFY_ALL) {
        $html = \Program\Lib\HTML\html::tag("form", array("id" => "proposals_form","class" => "pure-form pure-form-aligned","action" => o::url(null, null, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid), false) . "#poll","method" => "post"), \Program\Lib\HTML\html::div(array("id" => "proposals_div"), self::$table->show()) . $hidden_field_csrf_token->show() . (o::get_env("mobile") && \Program\Data\Poll::get_current_poll()->locked == 0 && (! \Program\Data\Poll::get_current_poll()->auth_only || \Program\Data\User::isset_current_user()) ? \Program\Lib\HTML\html::tag("input", array("class" => "pure-button pure-button-save","id" => "proposals_form_submit","form" => "proposals_form","type" => "submit","value" => Localization::g("Save"))) . " " . \Program\Lib\HTML\html::a(array("href" => o::url(null, ACT_DELETE_RESPONSE, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid,"t" => Session::getCSRFToken()), false),"id" => "button_delete_response","data-role" => "button","class" => "pure-button pure-button-save customtooltip_bottom","title" => Localization::g("Clic to delete your response", false)), Localization::g("delete")) : ""));
      }
      else {
        $hidden_field_modify_all = new \Program\Lib\HTML\html_hiddenfield(array("name" => "hidden_modify_all","value" => \Program\Data\Poll::get_current_poll()->poll_id));
        $html = \Program\Lib\HTML\html::tag("form", array("id" => "proposals_form","class" => "pure-form pure-form-aligned","action" => o::url(null, null, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid), false) . "#poll","method" => "post"), \Program\Lib\HTML\html::div(array("id" => "proposals_div"), self::$table->show()) . $hidden_field_modify_all->show() . $hidden_field_csrf_token->show() . \Program\Lib\HTML\html::tag("input", array("class" => "pure-button pure-button-submit","style" => "margin-top: 15px; margin-bottom: 15px; margin-left: 45%;","type" => "submit","value" => Localization::g("Save"))) . " " . \Program\Lib\HTML\html::a(array("href" => o::url(null, null, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid), false)), Localization::g("Cancel")));
      }
    }
    else {
      $html = \Program\Lib\HTML\html::div(array(), Localization::g('Empty proposals'));
    }
    return $html;
  }
  /**
   * Génération du text pour les meilleurs propositions
   *
   * @return string
   */
  public static function GetBestProposalsText($html = true) {
    // Ne pas afficher la table si l'utilisateur n'est pas connecté et qu'il s'agit d'un sondage authentifié
    if (\Program\Data\Poll::get_current_poll()->auth_only && ! \Program\Data\User::isset_current_user()) {
      return "";
    }
    return count(o::get_env("best_proposals")) == 1 ? Localization::g('Proposal with the most responses is ', $html) . implode(', ', o::get_env("best_proposals")) : Localization::g('Proposals with the most responses are ', $html) . implode(', ', o::get_env("best_proposals"));
  }

  /**
   * Génération du pop up l'affichage de la meilleur proposition avec le bouton de validation
   *
   * @return string
   */
  public static function GetBestProposalPopup() {
    $html = "";
    if (is_array(self::$proposals)) {
      foreach (self::$proposals as $prop_key => $prop_value) {
        if (! isset(self::$nb_resp[$prop_value]))
          self::$nb_resp[$prop_value] = 0;
        if (self::$max == self::$nb_resp[$prop_value] && self::$max != 0 && ! \Program\Data\Poll::get_current_poll()->if_needed || self::$max == self::$nb_resp[$prop_value] && self::$max != 0 && isset(self::$nb_resp["$prop_value:if_needed"]) && self::$max_if_needed == (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value])) {
          $prop = o::format_prop_poll(\Program\Data\Poll::get_current_poll(), $prop_value);
          if (isset(self::$nb_resp["$prop_value:if_needed"])) {
            $best_proposal = '"' . $prop . '" (' . self::$nb_resp[$prop_value] . ' (' . self::$nb_resp["$prop_value:if_needed"] . ') ' . (self::$nb_resp[$prop_value] > 1 ? Localization::g('responses') : Localization::g('response')) . ')';
          }
          else {
            $best_proposal = '"' . $prop . '" (' . self::$nb_resp[$prop_value] . ' ' . (self::$nb_resp[$prop_value] > 1 ? Localization::g('responses') : Localization::g('response')) . ')';
          }
          if (count(o::get_env("best_proposals")) == 1) {
            $html .= \Program\Lib\HTML\html::div([], Localization::g('Proposal with the most responses is ', $html) . $best_proposal);
          }
          else {
            $html .= \Program\Lib\HTML\html::div([], Localization::g('One of the proposals with the most responses is ', $html) . $best_proposal);
          }

          $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_validate_prop", o::url("ajax", ACT_VALIDATE_PROP, null, false), ACT_VALIDATE_PROP, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken())),"class" => "pure-button pure-button-validate-prop customtooltip_bottom","title" => Localization::g("Clic to validate this proposal", false) . " : " . $prop_value,"style" => (isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), Localization::g("Validate this proposal"));
          break;
        }
      }
    }
    return $html;
  }

  /**
   * Generation du pop up d'ajout à l'agenda en fonction des dates validées
   *
   * @return string
   */
  public static function GetAddCalendarProposalsPopup() {
    $html = "";
    // Récupération des propositions validées
    self::$validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
    // Récupération des events du sondage
    if (\Program\Data\EventsList::isset_current_eventslist() && \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_CONFIRMED) {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
    }
    else {
      $events = [];
    }
    if (is_array(self::$proposals)) {
      foreach (self::$proposals as $prop_key => $prop_value) {
        if (isset(self::$validate_proposals[$prop_value]) && ! isset($events[$prop_value])) {
          $prop = o::format_prop_poll(\Program\Data\Poll::get_current_poll(), $prop_value);
          $html .= \Program\Lib\HTML\html::div(["class" => "dialog_popup_content_separator"], " ");
          $html .= \Program\Lib\HTML\html::div([], Localization::g($prop, $html));
          $html .= \Program\Lib\HTML\html::div(["class" => "popup_calendar popup_calendar_$prop_key"], \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_ACCEPTED)),"class" => "pure-button pure-button-calendar-accept-text customtooltip_bottom","title" => Localization::g("Clic here to participate", false)), Localization::g("I'll be there")) . \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_DECLINED)),"class" => "pure-button pure-button-calendar-decline-text customtooltip_bottom","title" => Localization::g("Clic here to decline participation", false)), Localization::g("I'll not be there")));

        }
      }
    }
    return $html;
  }

  /**
   * Génération du text pour les propositions validées par l'organisateur
   *
   * @return string
   */
  public static function GetValidateProposalsText($html = true) {
    // Ne pas afficher la table si l'utilisateur n'est pas connecté et qu'il s'agit d'un sondage authentifié
    if (\Program\Data\Poll::get_current_poll()->auth_only && ! \Program\Data\User::isset_current_user()) {
      return "";
    }
    $validate_proposals = \Program\Data\Poll::get_current_poll()->validate_proposals;
    $validate_proposals_text = array();
    ksort($validate_proposals);
    foreach ($validate_proposals as $prop => $value) {
      if (\Program\Data\Poll::get_current_poll()->type == "date") {
        $values = explode(' - ', $prop);
        $time = strtotime($values[0]);
        $month = Localization::g(date("F", $time));
        $day = Localization::g(date("l", $time));
        $d = date("d", $time);
        $year = date("Y", $time);
        $hour = date("H", $time);
        $minute = date("i", $time);
        $prop = "$day $d $month $year";
        if (strlen($values[0]) != 10)
          $prop .= " - " . $hour . "h" . $minute;
      }
      $validate_proposals_text[] = $prop;
    }
    if (count($validate_proposals) == 0)
      return "";
    return count($validate_proposals) == 1 ? Localization::g('Proposal validate by the organizer is ', $html) . implode(', ', $validate_proposals_text) : Localization::g('Proposals validate by the organizer are ', $html) . implode(', ', $validate_proposals_text);
  }

  /**
   * Génération de l'entête du tableau pour un sondage de type prop
   */
  private static function view_type_prop_headers() {
    // Ajout de la nouvelle ligne header
    self::$table->add_header(array("class" => "nb_attendees first_col"), count(self::$responses) . " " . (count(self::$responses) > 1 ? Localization::g("attendees") : Localization::g("attendee")));
    // Ajoute les propositions en header
    foreach (self::$proposals as $prop_key => $prop_value) {
      $class = "";
      if (isset(self::$validate_proposals[$prop_value])) {
        $class = " validate_prop_header";
      }
      if (\Program\Data\Poll::get_current_poll()->type == "date") {
        if (strlen($prop_value) == 10)
          $prop = date("d/m/Y", strtotime($prop_value));
        else {
          if (strpos($prop_value, ' - ') === false) {
            $prop = date("d/m/Y - H:i", strtotime($prop_value));
          }
          else {
            $tmp = explode(' - ', $prop_value, 2);
            if (strlen($tmp[0]) == 10) {
              $prop = date("d/m/Y", strtotime($tmp[0])) . ' - ' . date("d/m/Y", strtotime($tmp[1]));
            }
            else {
              $prop = date("d/m/Y - H:i", strtotime($tmp[0])) . ' - ' . date("d/m/Y - H:i", strtotime($tmp[1]));
            }
          }
        }
      }
      else {
        $prop = $prop_value;
      }
      self::$table->add_header(array("id" => "prop_header_$prop_key","class" => "prop_header$class"), $prop);
      self::$nb_resp[$prop_value] = 0;
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        self::$nb_resp["$prop_value:if_needed"] = 0;
      }
    }
    if (o::get_env("action") == ACT_MODIFY_ALL) {
      self::$table->add_header(array("class" => "last_col"), Localization::g("Delete"));
    }
    else {
      self::$table->add_header(array("class" => "last_col"), "");
    }
  }
  /**
   * Génération de l'entête du tableau pour un sondage de type date
   */
  private static function view_type_date_complex_headers() {
    // Ajoute les propositions en header
    $month_list = array();
    $day_list = array();
    $no_hour = true;
    // Trie les propositions
    asort(self::$proposals);
    // Ajoute la first col des headers
    self::$table->add_header(array("class" => "first_col hidden"), "");
    foreach (self::$proposals as $prop_key => $prop_value) {
      // AJoute les headers hidden
      self::$table->add_header(array("id" => "prop_header_$prop_key","class" => "prop_header hidden"), $prop_value);
      $values = explode(' - ', $prop_value);
      $time = strtotime($values[0]);
      $month = Localization::g(date("F", $time));
      $day = Localization::g(date("l", $time));
      $d = date("d", $time);
      $year = date("Y", $time);
      if (isset($values[1])) {
        $time1 = strtotime($values[1]);
        $month1 = Localization::g(date("F", $time1));
        $day1 = Localization::g(date("l", $time1));
        $d1 = date("d", $time1);
        $year1 = date("Y", $time1);
        if ($month != $month1 || $year != $year1) {
          if ($month != $month1 && $year == $year1) {
            // Liste les mois des propositions
            if (! isset($month_list["$month - $month1 $year1"]))
              $month_list["$month - $month1 $year1"] = 0;
            $month_list["$month - $month1 $year1"] ++;
          }
          else {
            // Liste les mois des propositions
            if (! isset($month_list["$month $year - $month1 $year1"]))
              $month_list["$month $year - $month1 $year1"] = 0;
            $month_list["$month $year - $month1 $year1"] ++;
          }
        }
        else {
          // Liste les mois des propositions
          if (! isset($month_list["$month $year"]))
            $month_list["$month $year"] = 0;
          $month_list["$month $year"] ++;
        }
        if ("$day $d" != "$day1 $d1") {
          if ($month != $month1 || $year != $year1) {
            if ($year == $year1) {
              // Liste les date des propositions
              if (! isset($day_list["$day $d $month - $day1 $d1 $month1%%$month $year"]))
                $day_list["$day $d $month - $day1 $d1 $month1%%$month $year"] = 0;
              $day_list["$day $d $month - $day1 $d1 $month1%%$month $year"] ++;
            }
            else {
              // Liste les date des propositions
              if (! isset($day_list["$day $d $month $year - $day1 $d1 $month1 $year1%%$month $year"]))
                $day_list["$day $d $month $year - $day1 $d1 $month1 $year1%%$month $year"] = 0;
              $day_list["$day $d $month $year - $day1 $d1 $month1 $year1%%$month $year"] ++;
            }
          }
          else {
            // Liste les date des propositions
            if (! isset($day_list["$day $d - $day1 $d1%%$month $year"]))
              $day_list["$day $d - $day1 $d1%%$month $year"] = 0;
            $day_list["$day $d - $day1 $d1%%$month $year"] ++;
          }
        }
        else {
          // Liste les date des propositions
          if (! isset($day_list["$day $d%%$month $year"]))
            $day_list["$day $d%%$month $year"] = 0;
          $day_list["$day $d%%$month $year"] ++;
        }
        // Est-ce qu'on a un horaire
        if (strlen($values[0]) > 10 && $no_hour)
          $no_hour = false;
      }
      else {
        // Liste les mois des propositions
        if (! isset($month_list["$month $year"]))
          $month_list["$month $year"] = 0;
        $month_list["$month $year"] ++;

        // Liste les date des propositions
        if (! isset($day_list["$day $d%%$month $year"]))
          $day_list["$day $d%%$month $year"] = 0;
        $day_list["$day $d%%$month $year"] ++;
        // Est-ce qu'on a un horaire
        if (strlen($values[0]) > 10 && $no_hour)
          $no_hour = false;
      }
    }
    // AJoute la dernière colonne
    self::$table->add_header(array("class" => "last_col hidden"), "");
    // Ajout de la nouvelle ligne header
    self::$table->add_row(array("class" => "prop_row_header"));
    // Ajout des mois
    self::$table->add(array("class" => "first_col"), "");
    foreach ($month_list as $key => $value) {
      self::$table->add(array("colspan" => "$value","class" => "prop_header"), $key);
    }
    self::$table->add(array("class" => "last_col"), "");
    // Ajout de la nouvelle ligne header
    self::$table->add_row(array("class" => "prop_row_header"));
    // Ajout des jours
    if (! $no_hour) {
      self::$table->add(array("class" => "first_col"), "");
    }
    else {
      self::$table->add(array("class" => "nb_attendees first_col"), count(self::$responses) . " " . (count(self::$responses) > 1 ? Localization::g("attendees") : Localization::g("attendee")));
    }
    foreach ($day_list as $key => $value) {
      $key = explode("%%", $key);
      self::$table->add(array("colspan" => "$value","class" => "prop_header"), $key[0]);
    }
    if (o::get_env("action") == ACT_MODIFY_ALL && $no_hour) {
      self::$table->add(array("class" => "last_col"), Localization::g("Delete"));
    }
    else {
      self::$table->add(array("class" => "last_col"), "");
    }
    // Si on affiche les heures
    if (! $no_hour) {
      // Ajout de la nouvelle ligne header
      self::$table->add_row(array("class" => "prop_row_header"));
      // Ajoute les propositions en header
      self::$table->add(array("class" => "nb_attendees first_col"), count(self::$responses) . " " . (count(self::$responses) > 1 ? Localization::g("attendees") : Localization::g("attendee")));
      foreach (self::$proposals as $prop_key => $prop_value) {
        $class = "";
        if (isset(self::$validate_proposals[$prop_value]) && \Program\Data\Poll::get_current_poll()->locked == 1) {
          $class = "validate_prop_header";
        }
        $values = explode(' - ', $prop_value);
        if (strlen($values[0]) == 10) {
          if (isset($values[1]))
            $prop = "";
          else
            $prop = Localization::g("All day");
        }
        else {
          $prop = date("H:i", strtotime($values[0]));
          if (isset($values[1]))
            $prop = $prop . " - " . date("H:i", strtotime($values[1]));
        }
        self::$table->add(array("id" => "prop_header_$prop_key","class" => "prop_header_time $class"), $prop);
        self::$nb_resp[$prop_value] = 0;
        if (\Program\Data\Poll::get_current_poll()->if_needed) {
          self::$nb_resp["$prop_value:if_needed"] = 0;
        }
      }
      if (o::get_env("action") == ACT_MODIFY_ALL) {
        self::$table->add(array("class" => "last_col"), Localization::g("Delete"));
      }
      else {
        self::$table->add(array("class" => "last_col"), "");
      }
    }
  }
  /**
   * Génération de l'affichage pour l'utilisateur courant qui a répondu quand le sondage n'est pas locké
   *
   * @param \Program\Data\Response $response
   */
  private static function view_current_user_unlock_response($response) {
    self::$user_responded = true;
    $user = \Program\Data\User::get_current_user();
    $name = isset($user->fullname) && $user->fullname != "" ? $user->fullname : $user->username;
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_elements"));
    self::$table->add(array("class" => "user_list_name user_list_name_connected first_col user_authenticate customtooltip_bottom","title" => ($user->auth == 1 ? Localization::g("User authenticate", false) : Localization::g("User not authenticate", false)) . " : $name"), $name);
    // Unserialize les réponses de l'utilisateur
    $resp = unserialize($response->response);
    if (! is_array($resp))
      $resp = array();
    foreach (self::$proposals as $prop_key => $prop_value) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $checkbox = new \Program\Lib\HTML\html_radiobutton(array("id" => "check_$prop_key","name" => "check_$prop_key"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          $value = $prop_value;
          $class = "prop_accepted";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        elseif (isset($resp["$prop_value:if_needed"]) && $resp["$prop_value:if_needed"]) {
          $value = "$prop_value:if_needed";
          $class = "prop_if_needed";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp["$prop_value:if_needed"] ++;
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        else {
          $value = false;
          $class = "prop_refused";
        }
        $br = '';
        self::$table->add(array("title" => $prop_value,"class" => "$class prop_change customtooltip_bottom","align" => "center"), $checkbox->show($value, ['value' => $prop_value,'id' => "id$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "id$prop_value", 'class' => 'radio_label label_yes'], Localization::g('Yes')) . $br . $checkbox->show($value, ['value' => "$prop_value:if_needed",'id' => "idif_needed$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "idif_needed$prop_value", 'class' => 'radio_label label_if_needed'], Localization::g('If needed')) . $br . $checkbox->show($value, ['value' => false,'id' => "iddeclined$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "iddeclined$prop_value", 'class' => 'radio_label label_no'], Localization::g('No')));
      }
      else {
        $checkbox = new \Program\Lib\HTML\html_checkbox(array("id" => "check_$prop_key","name" => "check_$prop_key","value" => "$prop_value"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          self::$table->add(array("title" => $prop_value,"class" => "prop_accepted prop_change customtooltip_bottom","align" => "center"), $checkbox->show($prop_value));
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
        }
        else {
          self::$table->add(array("title" => $prop_value,"class" => "prop_refused prop_change customtooltip_bottom","align" => "center"), $checkbox->show());
        }
      }
    }
    $hidden_field = new \Program\Lib\HTML\html_hiddenfield(array("name" => "hidden_modify","value" => $response->user_id));
    if (! o::get_env("mobile")) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $a = \Program\Lib\HTML\html::a(array("onclick" => o::command("yes_to_all"),"class" => "yes_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all yes radio", false)), Localization::g('Yes')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("if_needed_to_all"),"class" => "if_needed_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all if needed radio", false)), Localization::g('If needed')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("no_to_all"),"class" => "no_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all no radio", false)), Localization::g('No'));
      }
      else {
        $a = \Program\Lib\HTML\html::a(array("onclick" => o::command("check_all"),"class" => "check_all_button customtooltip_bottom","title" => Localization::g("Clic to check all checkboxes", false)), Localization::g('Check all')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("uncheck_all"),"class" => "uncheck_all_button customtooltip_bottom","title" => Localization::g("Clic to uncheck all checkboxes", false)), Localization::g('Uncheck all'));
      }
      self::$table->add(array("class" => "prop_cell_nobackground last_col two_buttons"), \Program\Lib\HTML\html::tag("input", array("class" => "pure-button pure-button-save customtooltip_bottom","title" => Localization::g("Clic to save your responses", false),"type" => "submit","value" => Localization::g("Modify response"))) . $hidden_field->show() . " " . \Program\Lib\HTML\html::a(array("href" => o::url(null, ACT_DELETE_RESPONSE, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid,"t" => Session::getCSRFToken()), false),"id" => "button_delete_response","class" => "pure-button pure-button-delete customtooltip_bottom","title" => Localization::g("Clic to delete your response", false)), \Program\Lib\HTML\html::img(array("src" => "skins/" . o::get_env("skin") . "/images/1397492211_RecycleBin.png","height" => "15px"))));
      self::$table->add("check_uncheck_all", $a);
    }
    else {
      self::$table->add(array("class" => "prop_cell_nobackground last_col"), $hidden_field->show());
    }
  }
  /**
   * Génération de l'affichage pour l'utilisateur courant non authentifié qui a répondu quand le sondage n'est pas locké
   *
   * @param \Program\Data\Response $response
   */
  private static function view_unauthenticate_current_user_unlock_response($response) {
    self::$user_responded = true;
    // Classe email si un email est présent
    $class = Session::is_set('user_noauth_email') && Session::get('user_noauth_email') != "" ? " user_email" : "";
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_elements customtooltip_bottom"));
    self::$table->add(array("class" => "user_list_name user_list_name_connected first_col customtooltip_bottom$class","title" => Localization::g("User not authenticate", false) . " : " . Session::get("user_noauth_name")), Session::get("user_noauth_name"));
    // Unserialize les réponses de l'utilisateur
    $resp = unserialize($response->response);
    if (! is_array($resp))
      $resp = array();
    foreach (self::$proposals as $prop_key => $prop_value) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $checkbox = new \Program\Lib\HTML\html_radiobutton(array("id" => "check_$prop_key","name" => "check_$prop_key"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          // L'utilisateur à répondu oui
          $value = $prop_value;
          $class = "prop_accepted";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        elseif (isset($resp["$prop_value:if_needed"]) && $resp["$prop_value:if_needed"]) {
          // L'utilisateur à répondu si besoin
          $value = "$prop_value:if_needed";
          $class = "prop_if_needed";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp["$prop_value:if_needed"] ++;
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        else {
          // L'utilisateur à répondu non
          $value = false;
          $class = "prop_refused";
        }
        $br = '';
        // Ajout des boutons radio
        self::$table->add(array("title" => $prop_value,"class" => "$class prop_change customtooltip_bottom","align" => "center"), $checkbox->show($value, ['value' => $prop_value,'id' => "id$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "id$prop_value", 'class' => 'radio_label label_yes'], Localization::g('Yes')) . $br . $checkbox->show($value, ['value' => "$prop_value:if_needed",'id' => "idif_needed$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "idif_needed$prop_value", 'class' => 'radio_label label_if_needed'], Localization::g('If needed')) . $br . $checkbox->show($value, ['value' => false,'id' => "iddeclined$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "iddeclined$prop_value", 'class' => 'radio_label label_no'], Localization::g('No')));
      }
      else {
        $checkbox = new \Program\Lib\HTML\html_checkbox(array("id" => "check_$prop_key","name" => "check_$prop_key","value" => "$prop_value"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          self::$table->add(array("title" => $prop_value,"class" => "prop_accepted prop_change customtooltip_bottom","align" => "center"), $checkbox->show($prop_value));
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
        }
        else {
          self::$table->add(array("title" => $prop_value,"class" => "prop_refused prop_change customtooltip_bottom","align" => "center"), $checkbox->show());
        }
      }
    }
    $hidden_field = new \Program\Lib\HTML\html_hiddenfield(array("name" => "hidden_modify","value" => $response->user_id));
    if (! o::get_env("mobile")) {
      self::$table->add(array("class" => "prop_cell_nobackground last_col two_buttons"), \Program\Lib\HTML\html::tag("input", array("class" => "pure-button pure-button-save customtooltip_bottom","title" => Localization::g("Clic to save your responses", false),"type" => "submit","value" => Localization::g("Modify response"))) . $hidden_field->show() . " " . \Program\Lib\HTML\html::a(array("href" => o::url(null, ACT_DELETE_RESPONSE, array("u" => \Program\Data\Poll::get_current_poll()->poll_uid,"t" => Session::getCSRFToken()), false),"id" => "button_delete_response","class" => "pure-button pure-button-delete customtooltip_bottom","title" => Localization::g("Clic to delete your response", false)), \Program\Lib\HTML\html::img(array("src" => "skins/" . o::get_env("skin") . "/images/1397492211_RecycleBin.png","height" => "15px"))));
    }
    else {
      self::$table->add(array("class" => "prop_cell_nobackground last_col"), $hidden_field->show());
    }
  }
  /**
   * Génération de l'affichage des freebusy pour l'utilisateur courant
   */
  private static function view_user_freebusy() {
    if (! \Program\Data\User::isset_current_user()
    		|| ! \Program\Data\Poll::isset_current_poll()
    		|| \Program\Data\Poll::get_current_poll()->type != "date"
    		|| ! \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GET_FREEBUSY) {
      return;
    }
    try {
      // Ajout de la nouvelle ligne
      self::$table->add_row(array("class" => "prop_row_freebusy"));
      self::$table->add(array("class" => "user_freebusy_first_col user_calendar first_col customtooltip_bottom","title" => Localization::g("Your freebusy title", false)), Localization::g("Your freebusy"));
      // Parcourir les propositions
      foreach (self::$proposals as $prop_key => $prop_value) {
        $status = 'None';
        // Ajoute le champ de status à la table
        self::$table->add(array("title" => Localization::g($status),"class" => "freebusy_" . strtolower($status) . " freebusy_prop_$prop_key customtooltip_bottom","align" => "center"), Localization::g($status));
      }
      if (\Program\Data\Poll::get_current_poll()->locked == 0 && ! o::get_env("mobile")) {
        if (self::$user_responded
        		&& \Program\Data\EventsList::isset_current_eventslist()
        		&& \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_TENTATIVE
        		&& \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
          // Ajout de la derniere colonne
          self::$table->add(array("colspan" => "2","class" => "prop_cell_nobackground"), \Program\Lib\HTML\html::a(array("onclick" => o::command("delete_tentatives"),"id" => "button_delete_tentatives","data-role" => "button","class" => "pure-button pure-button-delete customtooltip_bottom","title" => Localization::g("Clic here to delete tentatives link to this poll", false)), Localization::g("Delete tentatives")));
        }
        else if (! self::$user_responded) {
          // Ajout de la derniere colonne
          self::$table->add(array("colspan" => "2","class" => "prop_cell_nobackground"), \Program\Lib\HTML\html::a(array("onclick" => o::command("save_from_freebusy"),"class" => "check_freebusy_button customtooltip_bottom","title" => Localization::g("Clic here to automaticaly generate your response from your feebusy", false)), Localization::g("Save from freebusy")));
        }
        else {
          // Ajout de la derniere colonne
          self::$table->add(array("class" => "prop_cell_nobackground last_col"), " ");
        }
      }
    }
    catch (\Exception $ex) {
      return;
    }
  }
  /**
   * Génération de l'affichage pour un utilisateur qui a répondu
   *
   * @param \Program\Data\Response $response
   */
  private static function view_user_response($response) {
    if (\Program\Data\User::isset_current_user() && $response->user_id == \Program\Data\User::get_current_user()->user_id) {
      $user = \Program\Data\User::get_current_user();
    }
    else {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
    }
    $name = isset($user->fullname) && $user->fullname != "" ? $user->fullname : $user->username;
    if (\Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      $name = "$name <" . $user->email . ">";
    }
    // Rendre le nom anonyme quand l'utilisateur n'est pas connecté
    elseif (! \Program\Data\User::isset_current_user()) {
      $name = self::AnonymName($name, $user->auth);
    }
    $authenticate_class = $user->auth == 1 ? " user_authenticate" : (! empty($user->email) ? " user_email" : "");
    if (\Program\Data\User::isset_current_user() && $response->user_id == \Program\Data\User::get_current_user()->user_id || Session::get("user_noauth_id") == $response->user_id) {
      // Ajout de la nouvelle ligne
      self::$table->add_row(array("class" => "prop_row_elements prop_current_user_elements"));
      self::$table->add(array("class" => "user_list_name user_list_name_connected first_col customtooltip_bottom$authenticate_class","title" => ($user->auth == 1 ? Localization::g("User authenticate", false) : Localization::g("User not authenticate", false)) . " : $name"), $name);
    }
    else {
      self::$table->add_row(array("class" => "prop_row_elements prop_others_users_elements"));
      self::$table->add(array("class" => "user_list_name first_col customtooltip_bottom$authenticate_class","title" => ($user->auth == 1 ? Localization::g("User authenticate", false) : Localization::g("User not authenticate", false)) . " : $name"), $name);
      self::$nb_others_responses ++;
    }
    // Unserialize les réponses de l'utilisateur
    $resp = unserialize($response->response);
    if (! is_array($resp))
      $resp = array();
    foreach (self::$proposals as $prop_key => $prop_value) {
      $class = "";
      if (isset(self::$validate_proposals[$prop_value]) && \Program\Data\Poll::get_current_poll()->locked == 1) {
        $class = "validate_prop_td";
      }
      if (isset($resp[$prop_value]) && $resp[$prop_value]) {
        self::$table->add(array("class" => "prop_accepted customtooltip_bottom $class","align" => "center","title" => "$prop_value"), Localization::g("Ok"));
        if (! isset(self::$nb_resp[$prop_value]))
          self::$nb_resp[$prop_value] = 0;
        if (! isset(self::$nb_resp["$prop_value:if_needed"]))
          self::$nb_resp["$prop_value:if_needed"] = 0;
        self::$nb_resp[$prop_value] ++;
        if (self::$nb_resp[$prop_value] > self::$max)
          self::$max = self::$nb_resp[$prop_value];
        if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
          self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
      }
      elseif (isset($resp["$prop_value:if_needed"]) && $resp["$prop_value:if_needed"]) {
        self::$table->add(array("class" => "prop_if_needed customtooltip_bottom $class","align" => "center","title" => "$prop_value"), Localization::g("If needed"));
        if (! isset(self::$nb_resp[$prop_value]))
          self::$nb_resp[$prop_value] = 0;
        if (! isset(self::$nb_resp["$prop_value:if_needed"]))
          self::$nb_resp["$prop_value:if_needed"] = 0;
        self::$nb_resp["$prop_value:if_needed"] ++;
        if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
          self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
      }
      else {
        self::$table->add(array("class" => "prop_refused customtooltip_bottom $class","title" => "$prop_value"), "");
      }
    }
    self::$table->add(array("class" => "prop_cell_nobackground last_col"), "");
  }
  /**
   * Génération de l'affichage pour la modification des réponses d'un utilisateur
   *
   * @param \Program\Data\Response $response
   */
  private static function view_modify_user_response($response) {
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_elements"));
    $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
    $name = isset($user->fullname) && $user->fullname != "" ? $user->fullname : $user->username;
    self::$table->add(array("class" => "user_list_name first_col customtooltip_bottom","title" => $name), $name);
    // Unserialize les réponses de l'utilisateur
    $resp = unserialize($response->response);
    if (! is_array($resp))
      $resp = array();
    foreach (self::$proposals as $prop_key => $prop_value) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $checkbox = new \Program\Lib\HTML\html_radiobutton(array("id" => "check--" . $response->user_id . "--$prop_key","name" => "check--" . $response->user_id . "--$prop_key"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          // L'utilisateur à répondu oui
          $value = $prop_value;
          $class = "prop_accepted";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        elseif (isset($resp["$prop_value:if_needed"]) && $resp["$prop_value:if_needed"]) {
          // L'utilisateur à répondu si besoin
          $value = "$prop_value:if_needed";
          $class = "prop_if_needed";
          if (! isset(self::$nb_resp[$prop_value]))
            self::$nb_resp[$prop_value] = 0;
          if (! isset(self::$nb_resp["$prop_value:if_needed"]))
            self::$nb_resp["$prop_value:if_needed"] = 0;
          self::$nb_resp["$prop_value:if_needed"] ++;
          if ((self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]) > self::$max_if_needed)
            self::$max_if_needed = (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value]);
        }
        else {
          // L'utilisateur à répondu non
          $value = false;
          $class = "prop_refused";
        }
        $br = '';
        // Ajout des boutons radio
        self::$table->add(array("title" => $prop_value,"class" => "$class","align" => "center"), $checkbox->show($value, ['value' => $prop_value,'id' => "id" . $response->user_id . "--$prop_key"]) . \Program\Lib\HTML\html::label(['for' => "id" . $response->user_id . "--$prop_key", 'class' => 'radio_label label_yes'], Localization::g('Yes')) . $br . $checkbox->show($value, ['value' => "$prop_value:if_needed",'id' => "idif_needed" . $response->user_id . "--$prop_key"]) . \Program\Lib\HTML\html::label(['for' => "idif_needed" . $response->user_id . "--$prop_key", 'class' => 'radio_label label_if_needed'], Localization::g('If needed')) . $br . $checkbox->show($value, ['value' => false,'id' => "iddeclined" . $response->user_id . "--$prop_key"]) . \Program\Lib\HTML\html::label(['for' => "iddeclined" . $response->user_id . "--$prop_key", 'class' => 'radio_label label_no'], Localization::g('No')));
      }
      else {
        $checkbox = new \Program\Lib\HTML\html_checkbox(array("id" => "check--" . $response->user_id . "--$prop_key","name" => "check--" . $response->user_id . "--$prop_key","value" => "$prop_value"));
        if (isset($resp[$prop_value]) && $resp[$prop_value]) {
          self::$table->add(array("class" => "prop_accepted","align" => "center"), $checkbox->show($prop_value));
          self::$nb_resp[$prop_value] ++;
          if (self::$nb_resp[$prop_value] > self::$max)
            self::$max = self::$nb_resp[$prop_value];
        }
        else {
          self::$table->add(array("class" => "prop_refused","align" => "center"), $checkbox->show());
        }
      }
    }
    $checkbox = new \Program\Lib\HTML\html_checkbox(array("id" => "delete--" . $response->user_id,"name" => "delete--" . $response->user_id,"value" => Localization::g("Delete")));
    self::$table->add(array("align" => "center","class" => "prop_cell_nobackground last_col"), $checkbox->show());
  }
  /**
   * Génération de l'affichage pour l'ajout d'une nouvelle réponse
   * Utilisateur authentifié ou non
   */
  private static function view_new_response() {
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_new_response"));
    if (\Program\Data\User::isset_current_user()) {
      $input = new \Program\Lib\HTML\html_inputfield(array("class" => "username_input customtooltip_bottom","title" => \Program\Data\User::get_current_user()->fullname,"style" => "width: 100%;","type" => "text","id" => "user_username","name" => "user_username","readonly" => "readonly"));
      self::$table->add(array("class" => "first_col"), $input->show(isset(\Program\Data\User::get_current_user()->fullname) && \Program\Data\User::get_current_user()->fullname != "" ? \Program\Data\User::get_current_user()->fullname : \Program\Data\User::get_current_user()->username));
    }
    else {
      $input_name = new \Program\Lib\HTML\html_inputfield(array("style" => "width: 100%;","type" => "text","id" => "user_username","name" => "user_username","placeholder" => Localization::g("Your name"),"required" => "required"));
      $input_email = new \Program\Lib\HTML\html_inputfield(array("style" => "width: 100%;","type" => "text","id" => "user_email","name" => "user_email","placeholder" => Localization::g("Your email")));
      $html = \Program\Lib\HTML\html::div(array("id" => "div_show_more_inputs"), \Program\Lib\HTML\html::span(array(), Localization::g("Put your email if you want to received notifications")) . $input_email->show() . \Program\Lib\HTML\html::a(array("onclick" => o::command("hide_attendees"),"class" => "hide_attendees_button customtooltip_bottom","title" => Localization::g("Clic to hide attendees", false)), Localization::g('hide attendees')) . \Program\Lib\HTML\html::a(array("onclick" => o::command("show_attendees"),"class" => "show_attendees_button customtooltip_bottom","title" => Localization::g("Clic to show attendees", false),"style" => "display: none;"), Localization::g('show attendees') . " (" . self::$nb_others_responses . ")"));
      self::$table->add(array("class" => "first_col"), $input_name->show() . $html);
    }
    foreach (self::$proposals as $prop_key => $prop_value) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $checkbox = new \Program\Lib\HTML\html_radiobutton(array("id" => "check_$prop_key","name" => "check_$prop_key"));
        $br = '';
        // Ajout des boutons radio
        self::$table->add(array("title" => $prop_value,"class" => "prop_not_responded prop_change customtooltip_bottom","align" => "center"), $checkbox->show('', ['value' => $prop_value,'id' => "id$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "id$prop_value", 'class' => 'radio_label label_yes'], Localization::g('Yes')) . $br . $checkbox->show('', ['value' => "$prop_value:if_needed",'id' => "idif_needed$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "idif_needed$prop_value", 'class' => 'radio_label label_if_needed'], Localization::g('If needed')) . $br . $checkbox->show('', ['value' => false,'id' => "iddeclined$prop_value"]) . \Program\Lib\HTML\html::label(['for' => "iddeclined$prop_value", 'class' => 'radio_label label_no'], Localization::g('No')));
      }
      else {
        $checkbox = new \Program\Lib\HTML\html_checkbox(array("id" => "check_$prop_key","name" => "check_$prop_key","value" => "$prop_value"));
        self::$table->add(array("title" => $prop_value,"class" => "prop_not_responded customtooltip_bottom","align" => "center"), $checkbox->show());
      }
    }
    if (! o::get_env("mobile")) {
      if (\Program\Data\Poll::get_current_poll()->if_needed) {
        $a = \Program\Lib\HTML\html::a(array("onclick" => o::command("yes_to_all"),"class" => "yes_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all yes radio", false)), Localization::g('Yes')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("if_needed_to_all"),"class" => "if_needed_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all if needed radio", false)), Localization::g('If needed')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("no_to_all"),"class" => "no_to_all_button customtooltip_bottom","title" => Localization::g("Clic to check all no radio", false)), Localization::g('No'));
      }
      else {
        $a = \Program\Lib\HTML\html::a(array("onclick" => o::command("check_all"),"class" => "check_all_button customtooltip_bottom","title" => Localization::g("Clic to check all checkboxes", false)), Localization::g('Check all')) . ' / ' . \Program\Lib\HTML\html::a(array("onclick" => o::command("uncheck_all"),"class" => "uncheck_all_button customtooltip_bottom","title" => Localization::g("Clic to uncheck all checkboxes", false)), Localization::g('Uncheck all'));
      }

      self::$table->add(array("class" => "prop_cell_nobackground last_col"), \Program\Lib\HTML\html::tag("input", array("class" => "pure-button pure-button-save customtooltip_bottom","title" => Localization::g("Clic to save your responses", false),"type" => "submit","value" => Localization::g("Save"))));
      self::$table->add("check_uncheck_all", $a);
    }
    else {
      self::$table->add(array("class" => "prop_cell_nobackground last_col"), "");
    }
  }
  /**
   * Affiche le nombre de réponses par propositions
   * Surligne celle/celles qui a le plus de réponse
   */
  private static function view_number_responses() {
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_nb_props"));
    // Affichage du nombre de réponse
    if (self::$nb_others_responses > 1) {
      self::$table->add(array("class" => "first_col"), \Program\Lib\HTML\html::a(array("onclick" => o::command("hide_attendees"),"class" => "hide_attendees_button customtooltip_bottom","title" => Localization::g("Clic to hide attendees", false)), Localization::g('hide attendees')) . \Program\Lib\HTML\html::a(array("onclick" => o::command("show_attendees"),"class" => "show_attendees_button customtooltip_bottom","title" => Localization::g("Clic to show attendees", false),"style" => "display: none;"), Localization::g('show attendees') . " (" . self::$nb_others_responses . ")"));
    }
    else if (o::get_env("action") == ACT_MODIFY_ALL && \Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      self::$table->add(array("class" => "first_col"), \Program\Lib\HTML\html::a(array("onclick" => o::command("add_attendee"),"class" => "add_attendee_button customtooltip_bottom","title" => Localization::g("Clic to add an attendee", false)), Localization::g('Add attendee')));
    }
    else {
      self::$table->add(array("class" => "first_col"), "");
    }
    $best_proposals = array();
    foreach (self::$proposals as $prop_key => $prop_value) {
      if (! isset(self::$nb_resp[$prop_value]))
        self::$nb_resp[$prop_value] = 0;
      $class = "";
      if (isset(self::$validate_proposals[$prop_value]) && \Program\Data\Poll::get_current_poll()->locked == 1) {
        $class .= "validate_prop_td ";
      }
      if (self::$max == self::$nb_resp[$prop_value] && self::$max != 0 && ! \Program\Data\Poll::get_current_poll()->if_needed || self::$max == self::$nb_resp[$prop_value] && self::$max != 0 && isset(self::$nb_resp["$prop_value:if_needed"]) && self::$max_if_needed == (self::$nb_resp["$prop_value:if_needed"] * 0.1 + self::$nb_resp[$prop_value])) {
        $class .= "prop_best";
        $prop = o::format_prop_poll(\Program\Data\Poll::get_current_poll(), $prop_value);
        if (isset(self::$nb_resp["$prop_value:if_needed"])) {
          $best_proposals[] = '"' . $prop . '" (' . self::$nb_resp[$prop_value] . ' (' . self::$nb_resp["$prop_value:if_needed"] . ') ' . (self::$nb_resp[$prop_value] > 1 ? Localization::g('responses') : Localization::g('response')) . ')';
        }
        else {
          $best_proposals[] = '"' . $prop . '" (' . self::$nb_resp[$prop_value] . ' ' . (self::$nb_resp[$prop_value] > 1 ? Localization::g('responses') : Localization::g('response')) . ')';
        }
      }
      if (isset(self::$nb_resp["$prop_value:if_needed"]) && self::$nb_resp["$prop_value:if_needed"] != 0) {
        self::$table->add(array("class" => "$class customtooltip_bottom  tooltipstered","align" => "center","title" => Localization::g('Ok') . " (" . Localization::g('If needed') . ")"), "" . self::$nb_resp[$prop_value] . " (" . self::$nb_resp["$prop_value:if_needed"] . ")");
      }
      else {
        self::$table->add(array("class" => "$class customtooltip_bottom  tooltipstered","align" => "center","title" => Localization::g('Ok')), "" . self::$nb_resp[$prop_value] . "");
      }
    }
    o::set_env("best_proposals", $best_proposals);
    self::$table->add(array("class" => "last_col"), "");
  }
  /**
   * Génération des boutons de validation pour que l'organizateur puisse valider une (ou plusieurs) proposition
   */
  private static function view_validation_buttons() {
    // Ajout de la nouvelle ligne
    self::$table->add_row(array("class" => "prop_row_buttons_actions"));
    // Affichage du nombre de réponse
    self::$table->add(array("class" => "first_col"), "");
    if (\Program\Data\EventsList::isset_current_eventslist()) {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
    }
    else {
      $events = [];
    }
    foreach (self::$proposals as $prop_key => $prop_value) {
      $class = "";
      $html = "";
      if (isset(self::$validate_proposals[$prop_value])) {
        $class = "validate_prop";
      }
      if (\Program\Data\User::isset_current_user() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
        $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_validate_prop", o::url("ajax", ACT_VALIDATE_PROP, null, false), ACT_VALIDATE_PROP, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken())),"class" => "pure-button pure-button-validate-prop customtooltip_bottom","title" => Localization::g("Clic to validate this proposal", false) . " : " . $prop_value,"style" => (isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), Localization::g("Validate proposal"));
      }

      if (\Program\Data\User::isset_current_user()
      		&& \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
        if (\Program\Data\Poll::get_current_poll()->type == "date"
        		&& \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
          if (! isset(self::$validate_proposals[$prop_value]) || ! \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($prop_value, (isset($events[$prop_value]) ? $events[$prop_value] : null), null, null, \Program\Data\Event::STATUS_CONFIRMED)) {
            $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken())),"class" => "pure-button pure-button-calendar customtooltip_bottom","title" => Localization::g("Clic to add this proposal to your calendar", false),"style" => (! isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), "");
          }
          else {
            $html .= \Program\Lib\HTML\html::a(array("class" => "pure-button pure-button-calendar pure-button-disabled customtooltip_bottom","title" => Localization::g("This proposals is already in your calendar", false),"style" => (! isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), "");
          }
        }
        elseif (\Program\Data\Poll::get_current_poll()->type == "date"
        		&& \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
          $html .= \Program\Lib\HTML\html::a(array("target" => "_blank","href" => o::url(null, ACT_DOWNLOAD_ICS, array("prop" => $prop_key,"u" => \Program\Data\Poll::get_current_poll()->poll_uid,"t" => Session::getCSRFToken()), false),"class" => "pure-button pure-button-calendar customtooltip_bottom","title" => Localization::g("Clic to download ICS of the proposal and add it to your calendar client", false),"style" => (! isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), "");
        }
        $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_validate_prop", o::url("ajax", ACT_UNVALIDATE_PROP, null, false), ACT_UNVALIDATE_PROP, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken())),"class" => "pure-button pure-button-unvalidate-prop customtooltip_bottom","title" => Localization::g("Clic to unvalidate this proposal", false) . " : " . $prop_value,"style" => (! isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), "");
      }
      elseif (\Program\Data\Poll::get_current_poll()->type == "date") {
        if (\Program\Data\User::isset_current_user()
        		&& \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
          if (isset(self::$validate_proposals[$prop_value])) {
            $event_exists = \Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($prop_value, (isset($events[$prop_value]) ? $events[$prop_value] : null));
            if ($event_exists && \Program\Data\EventsList::isset_current_eventslist() && isset(\Program\Data\EventsList::get_current_eventslist()->events_part_status[$prop_value]) && \Program\Data\EventsList::get_current_eventslist()->events_part_status[$prop_value] == \Program\Data\Event::PARTSTAT_ACCEPTED) {
              $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_ACCEPTED)),"class" => "pure-button pure-button-calendar-accept pure-button-disabled customtooltip_bottom","title" => Localization::g("Clic here to participate", false)), "");
            }
            else {
              $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_ACCEPTED)),"class" => "pure-button pure-button-calendar-accept customtooltip_bottom","title" => Localization::g("Clic here to participate", false)), "");
            }

            if ($event_exists && \Program\Data\EventsList::isset_current_eventslist() && isset(\Program\Data\EventsList::get_current_eventslist()->events_part_status[$prop_value]) && \Program\Data\EventsList::get_current_eventslist()->events_part_status[$prop_value] == \Program\Data\Event::PARTSTAT_DECLINED) {
              $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_DECLINED)),"class" => "pure-button pure-button-calendar-decline pure-button-disabled customtooltip_bottom","title" => Localization::g("Clic here to decline participation", false)), "");
            }
            else {
              $html .= \Program\Lib\HTML\html::a(array("onclick" => o::command("show_add_to_calendar", o::url("ajax", ACT_ADD_CALENDAR, null, false), ACT_ADD_CALENDAR, array("prop_key" => $prop_key,"poll_uid" => \Program\Data\Poll::get_current_poll()->poll_uid,"token" => Session::getCSRFToken(),"part_status" => \Program\Data\Event::PARTSTAT_DECLINED)),"class" => "pure-button pure-button-calendar-decline customtooltip_bottom","title" => Localization::g("Clic here to decline participation", false)), "");
            }
          }
        }
        elseif (\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
          $html .= \Program\Lib\HTML\html::a(array("target" => "_blank","href" => o::url(null, ACT_DOWNLOAD_ICS, array("prop" => $prop_key,"u" => \Program\Data\Poll::get_current_poll()->poll_uid,"t" => Session::getCSRFToken()), false),"class" => "pure-button pure-button-calendar customtooltip_bottom","title" => Localization::g("Clic to download ICS of the proposal and add it to your calendar client", false),"style" => (! isset(self::$validate_proposals[$prop_value]) ? "display: none;" : "")), "");
        }
      }
      self::$table->add(array("id" => "validate_prop_$prop_key","class" => "$class","align" => "center"), $html);
    }
    self::$table->add(array("class" => "last_col"), "");
  }

  /**
   * Rend les noms anonyme en les formattant à l'affichage
   *
   * @param string $name le nom a anonymiser
   * @param boolean $is_auth c'est un utilisateur authentifié
   * @return string
   */
  public static function AnonymName($name, $is_auth = true) {
    $ano_name = "";
    // Découpe le nom sur le blanc
    $names = explode(' ', $name);
    if (! $is_auth) {
      // Si c'est un utilisateur non authentifié on essaye de faire quelque chose de propre
      if (count($names) >= 2) {
        $ano_name = strtoupper(substr($names[0], 0, 1)) . '. ' . ucfirst($names[1]);
      }
      else {
        $ano_name = ucfirst($names[0]);
      }
    }
    else {
      $ano_name = "";
      // Si c'est un utilisateur authentifié, on se base sur les norme (majuscule pour le nom, première majuscule pour le prénom)
      foreach ($names as $_name) {
        if ($_name == '-' || strpos($_name, '(') === 0) {
          // Si on arrive au - ou a la ( c'est qu'on n'est plus dans le nom
          break;
        }
        if ($ano_name != "") {
          // Ajout d'un blanc
          $ano_name .= " ";
        }
        if (ctype_upper(preg_replace("/[^a-zA-Z]+/", "", $_name))) {
          // Tout en majuscule c'est un nom
          $ano_name .= substr($_name, 0, 1) . '.';
        }
        else {
          // Sinon c'est un prénom
          $ano_name .= $_name;
        }
      }
    }
    return $ano_name;
  }

  /**
   * ** ACTION COMMANDS *****
   */
  /**
   * Appel l'action modify pour enregistrer une nouvelle réponse
   */
  private static function _action_modify() {
    $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
    if (Session::validateCSRFToken($csrf_token)) {
      if (\Program\Data\Poll::get_current_poll()->auth_only && ! \Program\Data\User::isset_current_user()) {
        o::set_env("error", "Only auth users can respond to this poll");
      }
      else {
        $username = Request::getInputValue("user_username", POLL_INPUT_POST);
        $hidden_modify = Request::getInputValue("hidden_modify", POLL_INPUT_POST);
        if (\Program\Data\User::isset_current_user()) {
          $user_id = \Program\Data\User::get_current_user()->user_id;
          $user_name = isset(\Program\Data\User::get_current_user()->fullname) ? \Program\Data\User::get_current_user()->fullname : \Program\Data\User::get_current_user()->username;
        }
        elseif (Session::is_set("user_noauth_id") && Session::is_set("user_noauth_name") && Session::is_set("user_noauth_poll_id") && Session::get("user_noauth_poll_id") == \Program\Data\Poll::get_current_poll()->poll_id) {
          $user_id = Session::get("user_noauth_id");
          $user_name = Session::get("user_noauth_name");
        }
        elseif (isset($username)) {
          $user_email = Request::getInputValue("user_email", POLL_INPUT_POST);
          $user = new \Program\Data\User(array("username" => $username,"fullname" => "","email" => $user_email,"auth" => 0));
          $user_id = \Program\Drivers\Driver::get_driver()->addUser($user);
          if (! isset($user_id)) {
            o::set_env("error", "Error when creating the user");
            return;
          }
          Session::set("user_noauth_poll_id", \Program\Data\Poll::get_current_poll()->poll_id);
          Session::set("user_noauth_id", $user_id);
          Session::set("user_noauth_name", $username);
          Session::set("user_noauth_email", $user_email);
          $user_name = $username;
        }
        // Parcourir les responses
        $resp = array();
        foreach ($_POST as $key => $post) {
          if ($key != "user_username" && $key != "hidden_modify") {
            $resp[Request::getInputValue($key, POLL_INPUT_POST)] = true;
          }
        }
        $response = \Program\Drivers\Driver::get_driver()->getPollUserResponse($user_id, \Program\Data\Poll::get_current_poll()->poll_id);
        // Cas d'une modification pour un utilisateur authentifié
        if ((\Program\Data\User::isset_current_user() || Session::is_set("user_noauth_id") && Session::is_set("user_noauth_name") && Session::is_set("user_noauth_poll_id")) && isset($hidden_modify) && (\Program\Data\User::isset_current_user() && $hidden_modify == \Program\Data\User::get_current_user()->user_id || Session::is_set("user_noauth_id") && $hidden_modify == Session::is_set("user_noauth_id")) || isset($response->poll_id)) {
          // Enregistrement de la réponse dans bdd
          $response = new \Program\Data\Response(array("user_id" => $user_id,"poll_id" => \Program\Data\Poll::get_current_poll()->poll_id));
          $response->__initialize_haschanged();
          $response->response = serialize($resp);
          if (! \Program\Drivers\Driver::get_driver()->modifyPollUserResponse($response)) {
            o::set_env("error", "Error when changing the response");
            return;
          }
        }
        elseif (isset($user_id)) {
          // Enregistrement de la réponse dans bdd
          $response = new \Program\Data\Response(array("user_id" => $user_id,"poll_id" => \Program\Data\Poll::get_current_poll()->poll_id,"response" => serialize($resp)));
          if (! \Program\Drivers\Driver::get_driver()->addPollUserResponse($response)) {
            o::set_env("error", "Error when saving the response");
            return;
          }
          if (isset(\Config\IHM::$SEND_MAIL) && \Config\IHM::$SEND_MAIL && $user_id != o::get_env("poll_organizer")->user_id) {
            \Program\Lib\Mail\Mail::SendResponseNotificationMail(\Program\Data\Poll::get_current_poll(), $user_name, $response, o::get_env("poll_organizer"));
          }
        }
        o::set_env("message", "Your response has been saved");
      }
    }
    else {
      o::set_env("error", "Invalid request");
    }
  }
  /**
   * Appel l'action modify all pour modifier les réponses des participants
   */
  private static function _action_modify_all() {
    $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
    if (Session::validateCSRFToken($csrf_token)) {
      // Cas de la modification de toutes les réponses
      $deleted_responses = array();
      $modify_responses = array();
      $new_responses = array();
      // Parcourir les données en post
      foreach ($_POST as $key => $value) {
        $keys = explode('--', $key);
        if (! isset($keys[0]))
          continue;
        if ($keys[0] == 'check') {
          if (isset($keys[1])) {
            if (! is_array($modify_responses[intval($keys[1])]))
              $modify_responses[intval($keys[1])] = array();
            $modify_responses[intval($keys[1])][Request::getInputValue($key, POLL_INPUT_POST)] = true;
          }
        }
        elseif ($keys[0] == 'delete') {
          if (isset($keys[1]))
            $deleted_responses[intval($keys[1])] = true;
        }
        elseif ($keys[0] == 'newuser') {
          if (! is_array($new_responses[intval($keys[1])])) {
            $new_responses[intval($keys[1])] = array();
          }
          $new_responses[intval($keys[1])]['username'] = Request::getInputValue($key, POLL_INPUT_POST);
        }
        elseif ($keys[0] == 'newemail') {
          if (! is_array($new_responses[intval($keys[1])])) {
            $new_responses[intval($keys[1])] = array();
          }
          $new_responses[intval($keys[1])]['email'] = Request::getInputValue($key, POLL_INPUT_POST);
        }
        elseif ($keys[0] == 'newcheck') {
          if (! is_array($new_responses[intval($keys[1])])) {
            $new_responses[intval($keys[1])] = array();
          }
          if (! is_array($new_responses[intval($keys[1])]['responses'])) {
            $new_responses[intval($keys[1])]['responses'] = array();
          }
          $new_responses[intval($keys[1])]['responses'][Request::getInputValue($key, POLL_INPUT_POST)] = true;
        }
        elseif ($keys[0] == 'newradio') {
          if (! is_array($new_responses[intval($keys[1])])) {
            $new_responses[intval($keys[1])] = array();
          }
          if (! is_array($new_responses[intval($keys[1])]['responses'])) {
            $new_responses[intval($keys[1])]['responses'] = array();
          }
          $new_responses[intval($keys[1])]['responses'][Request::getInputValue($key, POLL_INPUT_POST)] = true;
        }
      }
      // Récupération des réponses du sondage
      self::$responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      // Parcour les réponses
      foreach (self::$responses as $response) {
        if (isset($deleted_responses[$response->user_id]) && $deleted_responses[$response->user_id]) {
          // Si la réponse doit être supprimée
          \Program\Drivers\Driver::get_driver()->deletePollUserResponse($response->user_id, $response->poll_id);
        }
        elseif (isset($modify_responses[$response->user_id])) {
          // Si la réponse doit être modifiée
          $response->response = serialize($modify_responses[$response->user_id]);
          \Program\Drivers\Driver::get_driver()->modifyPollUserResponse($response);
        }
        else {
          // Si pas de résultat, on doit surement RAZ
          $response->response = '';
          \Program\Drivers\Driver::get_driver()->modifyPollUserResponse($response);
        }
      }
      // Parcour les nouvelles réponses
      if (is_array($new_responses) && count($new_responses) > 0) {
        foreach ($new_responses as $key => $new_response) {
          // Création du nouvel utilisateur
          $user = new \Program\Data\User(array("username" => $new_response['username'],"fullname" => "","email" => isset($new_response['email']) ? $new_response['email'] : "","auth" => 0));
          $user_id = \Program\Drivers\Driver::get_driver()->addUser($user);
          if (! isset($user_id)) {
            o::set_env("error", "Error when creating the user");
            return;
          }
          // Création des réponses
          $response = new \Program\Data\Response(array("user_id" => $user_id,"poll_id" => \Program\Data\Poll::get_current_poll()->poll_id));
          $response->__initialize_haschanged();
          $response->response = serialize($new_response['responses']);
          if (! \Program\Drivers\Driver::get_driver()->addPollUserResponse($response)) {
            o::set_env("error", "Error when changing the response");
            return;
          }
        }
        o::set_env("message", "Responses has been modified");
      }
    }
    else {
      o::set_env("error", "Invalid request");
    }
  }
  /**
   * Appel l'action delete response pour supprimer la réponse d'un participant
   */
  private static function _action_delete_reponse() {
    $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
    if (Session::validateCSRFToken($csrf_token)) {
      if (\Program\Data\User::isset_current_user()) {
        $user_id = \Program\Data\User::get_current_user()->user_id;
        $destroy_session = false;
      }
      elseif (Session::is_set("user_noauth_id") && Session::is_set("user_noauth_name") && Session::is_set("user_noauth_poll_id") && Session::get("user_noauth_poll_id") == \Program\Data\Poll::get_current_poll()->poll_id) {
        $user_id = Session::get("user_noauth_id");
        $destroy_session = true;
      }
      if (isset($user_id)) {
        if (\Program\Drivers\Driver::get_driver()->deletePollUserResponse($user_id, \Program\Data\Poll::get_current_poll()->poll_id)) {
          o::set_env("message", "Your response has been deleted");
          // Doit on détruire la session ?
          if ($destroy_session) {
            Session::un_set("user_noauth_poll_id");
            Session::un_set("user_noauth_id");
            Session::un_set("user_noauth_name");
          }
        }
        else {
          o::set_env("error", "Response not deleted");
        }
      }
      else {
        o::set_env("error", "User not found");
      }
    }
    else {
      o::set_env("error", "Invalid request");
    }
  }
  /**
   * Appel l'action download ics pour le téléchargement du fichier ICS
   */
  private static function _action_download_ics() {
    $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
    $source = trim(strtolower(Request::getInputValue("_s", POLL_INPUT_GET)));
    if ($source == "mail" || Session::validateCSRFToken($csrf_token)) {
      self::$proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
      $prop = Request::getInputValue("_prop", POLL_INPUT_GET);
      if (isset(self::$proposals[$prop])) {
        $ics = \Program\Lib\Event\Drivers\Driver::get_driver()->generate_ics(self::$proposals[$prop]);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=event.ics');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($ics));
        ob_clean();
        flush();
        echo $ics;
        exit();
      }
      else {
        o::set_env("error", "Error while generating the ICS file");
      }
    }
  }
}