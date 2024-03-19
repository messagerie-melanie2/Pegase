<?php

/**
 * Ce fichier fait parti de l'application de sondage du MEDDE/METL
 * Cette application est un doodle-like permettant aux utilisateurs
 * d'effectuer des sondages sur des dates ou bien d'autres criteres
 * L'application est écrite en PHP5,HTML et Javascript
 * et utilise une base de données postgresql et un annuaire LDAP pour l'authentification
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 *         This program is free software: you can redistribute it and/or modify
 *         it under the terms of the GNU Affero General Public License as
 *         published by the Free Software Foundation, either version 3 of the
 *         License, or (at your option) any later version.
 *         This program is distributed in the hope that it will be useful,
 *         but WITHOUT ANY WARRANTY; without even the implied warranty of
 *         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *         GNU Affero General Public License for more details.
 *         You should have received a copy of the GNU Affero General Public License
 *         along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Program\Lib\Templates;

// Utilisation des namespaces
use Program\Lib\Request\Localization as l,
  Program\Lib\Request\Request as Request,
  Program\Lib\Request\Session as Session,
  Program\Lib\Request\Output as Output,
  Program\Lib\Request\Cookie as Cookie;

/**
 * Classe de gestion de la page principale de l'application
 *
 * @package Lib
 * @subpackage Request
 */
class Main
{
  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct()
  {
  }

  /**
   * Execution de la requête
   */
  public static function Process()
  {
    if (Output::get_env("action") == ACT_DELETE && \Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
      if (Session::validateCSRFToken($csrf_token)) {
        // Récupération des réponses du sondage
        $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
        // Suppression des provisoires
        self::delete_tentatives_calendar($responses);
        if (\Program\Drivers\Driver::get_driver()->deletePoll(\Program\Data\Poll::get_current_poll()->poll_id)) {
          Output::set_env("message", "Poll has been deleted");
          $send_notif = Request::getInputValue("_send_notif", POLL_INPUT_GET);
          if (isset($send_notif) && $send_notif == 1) {
            \Program\Lib\Mail\Mail::SendDeletedPollNotificationMail(\Program\Data\Poll::get_current_poll(), $responses);
          }
          \Program\Data\Poll::set_current_poll(null);
        } else {
          Output::set_env("error", "Error while deleting the poll");
        }
      } else {
        Output::set_env("error", "Invalid request");
      }
    } elseif (Output::get_env("action") == ACT_ERASE && \Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
      if (Session::validateCSRFToken($csrf_token)) {
        if (\Program\Drivers\Driver::get_driver()->erasePoll(\Program\Data\Poll::get_current_poll()->poll_id)) {
          Output::set_env("message", "Poll has been erased");
        } else {
          Output::set_env("error", "Error while erasing the poll");
        }
      } else {
        Output::set_env("error", "Invalid request");
      }
    } elseif (Output::get_env("action") == ACT_RESTORE && \Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id) {
      $csrf_token = trim(strtolower(Request::getInputValue("_t", POLL_INPUT_GET)));
      if (Session::validateCSRFToken($csrf_token)) {
        if (\Program\Drivers\Driver::get_driver()->restorePoll(\Program\Data\Poll::get_current_poll()->poll_id)) {
          Output::set_env("message", "Poll has been restored");
        } else {
          Output::set_env("error", "Error while restoring the poll");
        }
      } else {
        Output::set_env("error", "Invalid request");
      }
    }
    // Ajout des labels
    Output::add_label(array(
      'Are you sure you want to delete the poll ?',
      'Yes',
      'No',
      'Notify attendees',
      'Are you sure you want to restore the poll ?',
      'Are you sure you want to erase the poll ?',
      'Show more...',
      'Your X last polls',
      'Last X polls that you have responded',
      'Your X last deleted polls',
      'All your polls',
      'All your responded polls',
      'Hide polls',
      'All your deleted polls'
    ));
    // Limite d'affichage du nombre de sondages
    Output::set_env('max_own_polls', isset(\Config\IHM::$MAX_SHOW_OWN_POLLS) ? \Config\IHM::$MAX_SHOW_OWN_POLLS : 5);
    Output::set_env('max_deleted_polls', isset(\Config\IHM::$MAX_SHOW_DELETED_POLLS) ? \Config\IHM::$MAX_SHOW_DELETED_POLLS : 2);
    Output::set_env('max_resp_polls', isset(\Config\IHM::$MAX_SHOW_RESP_POLLS) ? \Config\IHM::$MAX_SHOW_RESP_POLLS : 10);
    // Est-ce que l'utilisateur est authentifié
    Output::set_env("user_auth", \Program\Data\User::isset_current_user() && \Program\Data\User::get_current_user()->auth == 1);
    self::MobileVersion();
  }
  /**
   * Méthode pour passer en version mobile ou en version desktop
   * Suivant l'action passée en paramètre
   */
  public static function MobileVersion()
  {
    if (Output::get_env("action") == ACT_MOBILE) {
      // Passage en version mobile
      Cookie::setCookie("mobile_version", "true");
      Cookie::deleteCookie("desktop_version");
      // Redirection pour recharger la skin
      header('Location: ' . (Output::get_env("page") == "show" ? Output::url(null, null, array(
        "u" => \Program\Data\Poll::get_current_poll()->poll_uid
      ), false) : Output::url("main")));
      exit();
    } elseif (Output::get_env("action") == ACT_DESKTOP) {
      // Passage en version mobile
      Cookie::setCookie("desktop_version", "true");
      Cookie::deleteCookie("mobile_version");
      // Redirection pour recharger la skin
      header('Location: ' . (Output::get_env("page") == "show" ? Output::url(null, null, array(
        "u" => \Program\Data\Poll::get_current_poll()->poll_uid
      ), false) : Output::url("main")));
      exit();
    }
  }
  /**
   * Génération de la liste HTML permettant d'afficher les sondages de l'utilisateur
   *
   * @return string
   */
  public static function GetUserPolls($leftPanel = false)
  {
    $html = "";
    $polls = \Program\Drivers\Driver::get_driver()->listUserPolls(\Program\Data\User::get_current_user()->user_id);
    if (count($polls) == 0) {
      $html = \Program\Lib\HTML\HTML::div(array(
        "class" => "nopoll"
      ), l::g('No poll'));
    } else {
      if (!Output::get_env("mobile") && !$leftPanel)
        $table = new \Program\Lib\HTML\html_table(array(
          "id" => "polls_table"
        ));
      // Liste les sondages et génération des liens
      foreach ($polls as $poll) {
        if ($leftPanel) {
          $divContent = "";
          if ($poll->count_responses) {
            $divContent .= \Program\Lib\HTML\html::span('poll_countResponses', $poll->count_responses);
          }
          $divContent .= \Program\Lib\HTML\html::span('poll_title', \Program\Lib\HTML\HTML::a(array(
            "class" => "customtooltip_bottom",
            "title" => $poll->title,
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title));
          $divContent .= \Program\Lib\HTML\html::span('poll_modify', \Program\Lib\HTML\HTML::a(array(
            "class" => "button_edit_poll customtooltip_bottom",
            "title" => l::g('Clic to edit the poll', false),
            "href" => Output::url("edit", ACT_MODIFY, array(
              "u" => $poll->poll_uid
            ), false)
          ), l::g('Modify poll')));
          $divContent .= \Program\Lib\HTML\html::span('poll_delete', \Program\Lib\HTML\HTML::a(array(
            "class" => "button_delete_poll customtooltip_bottom",
            "title" => l::g('Clic to delete the poll', false),
            "href" => Output::url("main", ACT_DELETE, array(
              "u" => $poll->poll_uid,
              "t" => Session::getCSRFToken()
            ), false)
          ), l::g('Delete poll')));
          $divContent .= \Program\Lib\HTML\html::span(($poll->locked == 1 ? "poll_locked" : "poll_unlocked"), ($poll->locked == 1 ? " (" . l::g('Locked') . ")" : ""));
          $select = (\Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->poll_uid == $poll->poll_uid) ? " selected" : "";
          $html .= \Program\Lib\HTML\HTML::div(array(
            "class" => $poll->type . " poll__list_element$select"
          ), $divContent);
        } else if (!Output::get_env("mobile")) {
          $table->add_row(array(
            "class" =>  $poll->type . " poll__list_element"
          ));
          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "customtooltip_bottom",
            "title" => l::g('Clic to view the poll (Number of responses)', false),
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title . " (" . $poll->count_responses . ")"));

          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "pure-button pure-button-modify-poll button_edit_poll customtooltip_bottom",
            "title" => l::g('Clic to edit the poll', false),
            "href" => Output::url("edit", ACT_MODIFY, array(
              "u" => $poll->poll_uid
            ), false)
          ), \Program\Lib\HTML\HTML::img(array(
            "alt" => "Modify",
            "src" => "skins/" . Output::get_env("skin") . "/images/1395932254_gear-01_white.png",
            "height" => "12px"
          )) . " " . l::g('Modify poll')));
          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "pure-button pure-button-modify-poll button_delete_poll customtooltip_bottom",
            "title" => l::g('Clic to delete the poll', false),
            "href" => Output::url("main", ACT_DELETE, array(
              "u" => $poll->poll_uid,
              "t" => Session::getCSRFToken()
            ), false)
          ), \Program\Lib\HTML\HTML::img(array(
            "alt" => "Delete",
            "src" => "skins/" . Output::get_env("skin") . "/images/1395836978_remove-01_white.png",
            "height" => "12px"
          )) . " " . l::g('Delete poll')));
          $table->add(array(), $poll->locked == 1 ? " (" . l::g('Locked') . ")" : "");
        } else {
          $html .= \Program\Lib\HTML\HTML::div(array(
            "class" =>  $poll->type . " poll__list_element"
          ), \Program\Lib\HTML\HTML::a(array(
            "title" => l::g('Clic to view the poll (Number of responses)', false),
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title . " (" . $poll->count_responses . ")") . ($poll->locked == 1 ? " (" . l::g('Locked') . ")" : ""));
        }
      }
      if (isset($table)) {
        $html = $table->show();
      }
    }
    return $html;
  }
  /**
   * Génération de la liste HTML permettant d'afficher les sondages supprimés de l'utilisateur
   *
   * @return string
   */
  public static function GetUserDeletedPolls($leftPanel = false)
  {
    $html = "";
    $polls = \Program\Drivers\Driver::get_driver()->listUserDeletedPolls(\Program\Data\User::get_current_user()->user_id);
    if (count($polls) == 0) {
      $html = \Program\Lib\HTML\HTML::div(array(
        "class" => "nopoll"
      ), l::g('No poll'));
    } else {
      if (!Output::get_env("mobile") && !$leftPanel)
        $table = new \Program\Lib\HTML\html_table(array(
          "id" => "polls_table"
        ));
      // Liste les sondages et génération des liens
      foreach ($polls as $poll) {
        if ($leftPanel) {
          $divContent = "";
          if ($poll->count_responses) {
            $divContent .= \Program\Lib\HTML\html::span('poll_countResponses', $poll->count_responses);
          }
          $divContent .= \Program\Lib\HTML\html::span('poll_title', \Program\Lib\HTML\HTML::a(array(
            "class" => "customtooltip_bottom",
            "title" => $poll->title,
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title));
          $divContent .= \Program\Lib\HTML\html::span('poll_modify', \Program\Lib\HTML\HTML::a(array(
            "class" => "button_edit_poll customtooltip_bottom",
            "title" => l::g('Clic to edit the poll', false),
            "href" => Output::url("edit", ACT_MODIFY, array(
              "u" => $poll->poll_uid
            ), false)
          ), l::g('Modify poll')));
          $divContent .= \Program\Lib\HTML\html::span('poll_delete', \Program\Lib\HTML\HTML::a(array(
            "class" => "button_delete_poll customtooltip_bottom",
            "title" => l::g('Clic to delete the poll', false),
            "href" => Output::url("main", ACT_DELETE, array(
              "u" => $poll->poll_uid,
              "t" => Session::getCSRFToken()
            ), false)
          ), l::g('Delete poll')));
          $divContent .= \Program\Lib\HTML\html::span(($poll->locked == 1 ? "poll_locked" : "poll_unlocked"), ($poll->locked == 1 ? " (" . l::g('Locked') . ")" : ""));
          $select = (\Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->poll_uid == $poll->poll_uid) ? " selected" : "";
          $html .= \Program\Lib\HTML\HTML::div(array(
            "class" =>  $poll->type . " poll__list_element$select"
          ), $divContent);
        } else if (!Output::get_env("mobile")) {
          $table->add_row(array(
            "class" =>  $poll->type . " poll__list_element"
          ));
          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "customtooltip_bottom",
            "title" => l::g('Clic to view the poll (Number of responses)', false),
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title . " (" . $poll->count_responses . ")"));

          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "pure-button pure-button-modify-poll button_restore_poll customtooltip_bottom",
            "title" => l::g('Clic to restore the poll', false),
            "href" => Output::url("main", ACT_RESTORE, array(
              "u" => $poll->poll_uid,
              "t" => Session::getCSRFToken()
            ), false)
          ), \Program\Lib\HTML\HTML::img(array(
            "alt" => "Restore",
            "src" => "skins/" . Output::get_env("skin") . "/images/1395836978_add_new_poll.png",
            "height" => "12px"
          )) . " " . l::g('Restore poll')));
          $table->add(array(
            "style" => "padding-right: 10px;"
          ), \Program\Lib\HTML\HTML::a(array(
            "class" => "pure-button pure-button-modify-poll button_erase_poll customtooltip_bottom",
            "title" => l::g('Clic to erase the poll', false),
            "href" => Output::url("main", ACT_ERASE, array(
              "u" => $poll->poll_uid,
              "t" => Session::getCSRFToken()
            ), false)
          ), \Program\Lib\HTML\HTML::img(array(
            "alt" => "Erase",
            "src" => "skins/" . Output::get_env("skin") . "/images/1397492211_RecycleBin.png",
            "height" => "12px"
          )) . " " . l::g('Erase poll')));
        } else {
          $html .= \Program\Lib\HTML\HTML::div(array(
            "class" =>  $poll->type . " poll__list_element"
          ), \Program\Lib\HTML\HTML::a(array(
            "title" => l::g('Clic to view the poll (Number of responses)', false),
            "href" => Output::url(null, null, array(
              "u" => $poll->poll_uid
            ), false)
          ), $poll->title . " (" . $poll->count_responses . ")") . ($poll->locked == 1 ? " (" . l::g('Locked') . ")" : ""));
        }
      }
      if (isset($table))
        $html = $table->show();
    }
    return $html;
  }
  /**
   * Génération de la liste HTML permettant d'afficher les sondages auquel l'utilisateur à répondu
   *
   * @return string
   */
  public static function GetUserRespondedPolls($leftPanel = false)
  {
    $html = "";
    $polls = \Program\Drivers\Driver::get_driver()->listUserRespondedPolls(\Program\Data\User::get_current_user()->user_id);
    if (count($polls) == 0) {
      $html = \Program\Lib\HTML\HTML::div(array(
        "class" => "nopoll"
      ), l::g('No poll'));
    } else {
      foreach ($polls as $poll) {
        if ($poll->organizer_id != \Program\Data\User::get_current_user()->user_id) {
          if ($leftPanel) {
            $divContent = "";
            if ($poll->count_responses > 0) {
              $divContent .= \Program\Lib\HTML\html::span('poll_countResponses', $poll->count_responses);
            }
            $divContent .= \Program\Lib\HTML\html::span('poll_title', \Program\Lib\HTML\HTML::a(array(
              "class" => "customtooltip_bottom",
              "title" => $poll->title,
              "href" => Output::url(null, null, array(
                "u" => $poll->poll_uid
              ), false)
            ), $poll->title));
            $divContent .= \Program\Lib\HTML\html::span(($poll->locked == 1 ? "poll_locked" : "poll_unlocked"), ($poll->locked == 1 ? " (" . l::g('Locked') . ")" : ""));
            $select = (\Program\Data\Poll::isset_current_poll() && \Program\Data\Poll::get_current_poll()->poll_uid == $poll->poll_uid) ? " selected" : "";
            $html .= \Program\Lib\HTML\HTML::div(array(
              "class" =>  $poll->type . " poll__list_element$select"
            ), $divContent);
          } else {
            $html .= \Program\Lib\HTML\HTML::div(array(
              "class" =>  $poll->type . " poll__list_element"
            ), \Program\Lib\HTML\HTML::a(array(
              "class" => "customtooltip_bottom",
              "title" => l::g('Clic to view the poll (Number of responses)', false),
              "href" => Output::url(null, null, array(
                "u" => $poll->poll_uid
              ), false)
            ), $poll->title . " (" . $poll->count_responses . ")") . ($poll->locked == 1 ? " (" . l::g('Locked') . ')' : ""));
          }
        }
      }
      if ($html == "") {
        $html = l::g('No poll');
      }
    }
    return $html;
  }

  /**
   * Suppression des événements provisoires
   */
  private static function delete_tentatives_calendar($responses = null)
  {
    $proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);

    // Charge le eventslist depuis la base de données
    if (\Program\Data\EventsList::isset_current_eventslist() && \Program\Data\EventsList::get_current_eventslist()->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
      $events = unserialize(\Program\Data\EventsList::get_current_eventslist()->events);
      // Parcours les propositions du sondage
      foreach ($proposals as $prop_key => $proposal) {
        // La proposition n'est pas validée, il faut peut être la supprimer
        if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, null, \Program\Data\Event::STATUS_TENTATIVE)) {
          // L'événement existe, il faut donc le supprimer
          if (\Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, (isset($events[$proposal]) ? $events[$proposal] : null))) {
            // Supprime la date de la liste des events
            if (isset($events[$proposals[$prop_key]])) {
              unset($events[$proposals[$prop_key]]);
            }
          }
        }
      }

      // Enregistre les modifications sur le current eventslist
      \Program\Data\EventsList::get_current_eventslist()->events = serialize($events);
      \Program\Data\EventsList::get_current_eventslist()->modified_time = date('Y-m-d H:i:s');
      \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList(\Program\Data\EventsList::get_current_eventslist());
    }

    if (\Program\Data\Poll::get_current_poll()->organizer_id == \Program\Data\User::get_current_user()->user_id && \Config\IHM::$ORGANIZER_DELETE_TENTATIVES_ATTENDEES) {
      // Supprimer automatiquement les tentatives des participants
      if (!isset($responses)) {
        $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
      }
      foreach ($responses as $response) {
        if ($response->user_id != \Program\Data\Poll::get_current_poll()->organizer_id) {
          // Récupère les événements enregistrés depuis la base de données
          $user_eventslist = \Program\Drivers\Driver::get_driver()->getPollUserEventsList($response->user_id, \Program\Data\Poll::get_current_poll()->poll_id);
          if (isset($user_eventslist) && $user_eventslist->events_status == \Program\Data\Event::STATUS_TENTATIVE) {
            $events = unserialize($user_eventslist->events);
            $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
            if ($user->auth == 1) {
              // Parcours les événéments pour supprimer ceux qui doivent l'être
              // Parcours les propositions du sondage
              foreach ($proposals as $proposal_key => $proposal) {
                // La proposition n'est pas validée, il faut peut être la supprimer
                if (\Program\Lib\Event\Drivers\Driver::get_driver()->event_exists($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, $user, \Program\Data\Event::STATUS_TENTATIVE)) {
                  // L'événement existe et la proposition n'est pas validée, il faut donc le supprimer
                  \Program\Lib\Event\Drivers\Driver::get_driver()->delete_event($proposal, (isset($events[$proposal]) ? $events[$proposal] : null), null, $user);
                  // Supprime la date de la liste des events
                  if (isset($events[$proposal])) {
                    unset($events[$proposal]);
                  }
                }
              }
            }
          }
          // Enregistre les modifications sur le eventslist de l'utilisateur
          $user_eventslist->events = serialize($events);
          $user_eventslist->modified_time = date('Y-m-d H:i:s');
          \Program\Drivers\Driver::get_driver()->modifyPollUserEventsList($user_eventslist);
        }
      }
    }
  }
}
