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
use Program\Lib\Request\Request as Request, Program\Lib\Request\Session as Session, Program\Lib\Request\Output as o, Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion de l'édition du sondage
 *
 * @package Lib
 * @subpackage Request
 */
class Edit_end
{

    /**
     * Constructeur privé pour ne pas instancier la classe
     */
    private function __construct()
    {}

    /**
     * Execution de la requête
     */
    public static function Process()
    {
        if (! \Program\Data\User::isset_current_user()) {
            o::set_env("page", "error");
            o::set_env("error", "You have to be connected");
            return;
        }
        if (! \Program\Data\Poll::isset_current_poll()) {
            o::set_env("page", "error");
            o::set_env("error", "Current poll is not defined");
            return;
        }
        if (\Program\Data\Poll::get_current_poll()->organizer_id != \Program\Data\User::get_current_user()->user_id) {
            o::set_env("page", "error");
            o::set_env("error", "You are not organizer of the poll");
            return;
        }
        $csrf_token = trim(strtolower(Request::getInputValue("csrf_token", POLL_INPUT_POST)));
        if (Session::validateCSRFToken($csrf_token)) {
            // récupération des données de post
            if (! o::get_env("mobile") || \Program\Data\Poll::get_current_poll()->type == "prop") {
                $post = $_POST;
            } else {
                $post = array();
                foreach ($_POST as $key => $value) {
                    if (strpos($key, "edit_date_start") === 0 && $value != "") {
                        $id = str_replace("edit_date_start", "", $key);
                        $post["edit_date$id"] = Request::getInputValue($key, POLL_INPUT_POST) . (isset($_POST["edit_time_start$id"]) && $_POST["edit_time_start$id"] != "" ? " " . Request::getInputValue("edit_time_start$id", POLL_INPUT_POST) : "") . (isset($_POST["edit_date_end$id"]) && $_POST["edit_date_end$id"] != "" ? " - " . Request::getInputValue("edit_date_end$id", POLL_INPUT_POST) : "") . (isset($_POST["edit_time_end$id"]) && $_POST["edit_time_end$id"] != "" && isset($_POST["edit_date_end$id"]) && $_POST["edit_date_end$id"] != "" ? " " . Request::getInputValue("edit_time_end$id", POLL_INPUT_POST) : "");
                    }
                }
            }
            // Génération des propositions du sondage
            $proposals = array();
            foreach ($post as $key => $value) {
                if (strpos($key, "edit_date") === 0 || strpos($key, "edit_prop") === 0) {
                    $val = Request::getInputValue($key, POLL_INPUT_POST);
                    if (empty($val) && o::get_env("mobile"))
                        $val = $value;
                    if (! empty($val) && ! in_array($val, $proposals))
                        $proposals[strtolower($key)] = $val;
                }
            }
            if (o::get_env("action") != ACT_NEW
            		&& \Program\Data\Poll::get_current_poll()->type == "date") {
            	// Déterminer si on doit notifier
            	$send_notification = \Program\Data\Poll::get_current_poll()->proposals != serialize($proposals);
            	// Récupère les propositions modifiées
            	$old_proposals = unserialize(\Program\Data\Poll::get_current_poll()->proposals);
            	$new_proposals = [];
            	$deleted_proposals = [];
            	if ($old_proposals !== false) {
            		// Recherche les propositions supprimées
            		foreach ($old_proposals as $prop_key => $prop_value) {
            		    if (! in_array($prop_value, $proposals)) {
            		        $deleted_proposals[] = $prop_value;
            		    }
            		}
            		// Recherche les propositions ajoutées
            		foreach ($proposals as $prop_key => $prop_value) {
            		    if (! in_array($prop_value, $old_proposals)) {
            		        $new_proposals[] = $prop_value;
            		    }
            		}
            	}
            }

            // Enregistrement des propositions
            \Program\Data\Poll::get_current_poll()->proposals = serialize($proposals);
            // Réinitialiser les date start et end pour le recalcul automatique en fonction des propositions
            \Program\Data\Poll::get_current_poll()->date_start = null;
            \Program\Data\Poll::get_current_poll()->date_end = null;
            if (! \Program\Drivers\Driver::get_driver()->modifyPoll(\Program\Data\Poll::get_current_poll()) && o::get_env("action") == ACT_NEW) {
                o::set_env("page", "edit_" . \Program\Data\Poll::get_current_poll()->type);
                o::set_env("error", "Error saving proposals");
            } else
                if (o::get_env("action") != ACT_NEW) {
                    // Envoi du message de notification
                    if (isset($send_notification) && $send_notification) {
                        \Program\Lib\Mail\Mail::SendModifyProposalsNotificationMail(\Program\Data\Poll::get_current_poll(), $new_proposals, $deleted_proposals);
                    }
                    // Suppression des tentatives pour un sondage de date
                    if (\Program\Data\Poll::get_current_poll()->type == "date" && count($deleted_proposals) > 0) {
                        // Si des propositions sont supprimées, on supprime aussi les provisoires
                        self::delete_tentatives_calendar($deleted_proposals);
                    }
                }
        } else {
            o::set_env("page", "edit_" . \Program\Data\Poll::get_current_poll()->type);
            o::set_env("error", "Invalid request");
        }
    }

    /**
     * Génére l'url public vers le sondage courant
     *
     * @param boolean $newtab
     *            Ouvrir l'url dans un nouvel onglet ?
     * @return string
     */
    public static function GetPublicUrl($newtab = false)
    {
        $url = o::get_poll_url();
        $params = array(
            "title" => Localization::g("Copy this url to share your poll", false),
            "class" => "public_url_link customtooltip_bottom",
            "href" => $url
        );
        if ($newtab)
            $params['target'] = "_blank";
        return \Program\Lib\HTML\html::a($params, $url);
    }

    /**
     * Suppression des événements provisoires
     *
     * @param array $proposals
     *            Liste des propositions supprimées
     */
    private static function delete_tentatives_calendar($proposals = [])
    {
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
            $responses = \Program\Drivers\Driver::get_driver()->getPollResponses(\Program\Data\Poll::get_current_poll()->poll_id);
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