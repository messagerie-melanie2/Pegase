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

namespace Program\Lib\Mail;

// Utilisation des namespaces
use Program\Lib\Log\Log as Log, Program\Lib\Request\Output as Output, Program\Lib\Request\Request as Request, Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion des mails
 *
 * @package Lib
 * @subpackage Mail
 */
class Mail
{
  /**
   * Constructeur privé pour ne pas instancier la classe
   */
  private function __construct()
  {
  }

  /**
   * Méthode pour envoyer un message en text brut ou en html
   *
   * @param string $from adresse utilisée pour envoyer le message
   * @param string $to destinataire du message
   * @param string $subject sujet du message
   * @param string $bcc [optionnel] liste des destinataires en copie cachée
   * @param string $body corp du message
   * @param string $body_html [optionnel] corp du message en html
   * @param string $message [optionnel] message complet (sans les entêtes) en text/html
   * @param string $message_id [optionnel] identifiant du message à envoyer
   * @param string $in_reply_to [optionnel] réponse au message précédent
   * @return boolean
   */
  public static function SendMail($from, $to, $subject, $bcc = null, $body = null, $body_html = null, $message = null, $message_id = null, $in_reply_to = null, $as_attachment = false)
  {
    if (!\Config\IHM::$SEND_MAIL) {
      return true;
    }
    Log::l(Log::DEBUG, "Mail::SendMail($from, $to, $subject)");
    // Mail HTML
    if (isset($body_html)) {
      // Génération de la boundary
      $boundary = '-----=' . md5(uniqid(mt_rand()));

      // Mail html headers
      $headers = array();
      $headers[] = "MIME-Version: 1.0";
      $headers[] = "Content-Transfer-Encoding: 8BIT";
      $headers[] = "From: " . quoted_printable_encode($from);
      $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
      if (isset($bcc)) {
        $headers[] = "Bcc: " . $bcc;
      }
      if (isset($message_id)) {
        $headers[] = "Message-ID: <$message_id>";
      }
      if (isset($in_reply_to)) {
        $headers[] = "In-Reply-To: <$in_reply_to>";
        $headers[] = "References: <$in_reply_to>";
      }
      $headers[] = "X-Mailer: " . quoted_printable_encode(\Config\IHM::$TITLE . "/" . VERSION . '-' . BUILD);
      $envelopefrom = "-f $from";

      // Message texte
      $message = 'This is a multi-part message in MIME format.' . "\n\n";

      $message .= '--' . $boundary . "\n";
      $message .= 'Content-Type: text/plain; charset=UTF-8"' . "\n";
      $message .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
      $message .= $body . "\n\n";

      // Message HTML
      $message .= '--' . $boundary . "\n";
      $message .= 'Content-Type: text/html; charset=UTF-8"' . "\n";
      $message .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
      $message .= $body_html . "\n\n";

      $message .= '--' . $boundary . "\n";

      return mail($to, mb_encode_mimeheader($subject), $message, implode("\r\n", $headers), $envelopefrom);
    } else if (isset($message) && !$as_attachment) {
      // Génération de la boundary
      $boundary = '-----=' . md5(uniqid(mt_rand()));

      // Mail html headers
      $headers = array();
      $headers[] = "MIME-Version: 1.0";
      $headers[] = "Content-Transfer-Encoding: 8BIT";
      $headers[] = "From: " . quoted_printable_encode($from);
      $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
      if (isset($bcc)) {
        $headers[] = "Bcc: " . $bcc;
      }
      if (isset($message_id)) {
        $headers[] = "Message-ID: <$message_id>";
      }
      if (isset($in_reply_to)) {
        $headers[] = "In-Reply-To: <$in_reply_to>";
        $headers[] = "References: <$in_reply_to>";
      }
      $headers[] = "X-Mailer: " . quoted_printable_encode(\Config\IHM::$TITLE . "/" . VERSION . '-' . BUILD);
      $envelopefrom = "-f $from";

      // Set boundary
      $message = str_replace("%%boundary%%", $boundary, $message);

      return mail($to, mb_encode_mimeheader($subject), $message, implode("\r\n", $headers), $envelopefrom);
    } else if (isset($message) && $as_attachment) {
      // Génération de la boundary
      $boundary = '-----=' . md5(uniqid(mt_rand()));

      // Mail html headers
      $headers = array();
      $headers[] = "MIME-Version: 1.0";
      $headers[] = "Content-Transfer-Encoding: 8BIT";
      $headers[] = "From: " . quoted_printable_encode($from);
      $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
      if (isset($bcc)) {
        $headers[] = "Bcc: " . $bcc;
      }
      if (isset($message_id)) {
        $headers[] = "Message-ID: <$message_id>";
      }
      if (isset($in_reply_to)) {
        $headers[] = "In-Reply-To: <$in_reply_to>";
        $headers[] = "References: <$in_reply_to>";
      }
      $headers[] = "X-Mailer: " . quoted_printable_encode(\Config\IHM::$TITLE . "/" . VERSION . '-' . BUILD);
      $envelopefrom = "-f $from";

      // Set boundary
      $message = str_replace("%%boundary%%", $boundary, $message);

      return mail($to, mb_encode_mimeheader($subject), $message, implode("\r\n", $headers), $envelopefrom);
    } else {
      // Mail text headers
      $headers = array();
      $headers[] = "MIME-Version: 1.0";
      $headers[] = "Content-type: text/plain; charset=UTF-8";
      $headers[] = "Content-Transfer-Encoding: 8BIT";
      $headers[] = "From: " . quoted_printable_encode($from);
      if (isset($bcc)) {
        $headers[] = "Bcc: " . $bcc;
      }
      if (isset($message_id)) {
        $headers[] = "Message-ID: <$message_id>";
      }
      if (isset($in_reply_to)) {
        $headers[] = "In-Reply-To: <$in_reply_to>";
        $headers[] = "References: <$in_reply_to>";
      }
      $headers[] = "X-Mailer: " . quoted_printable_encode(\Config\IHM::$TITLE . "/" . VERSION . '-' . BUILD);
      $envelopefrom = "-f $from";

      return mail($to, mb_encode_mimeheader($subject), $body, implode("\r\n", $headers), $envelopefrom);
    }
  }
  /**
   * Méthode d'envoi du message notification à la création du sondage
   *
   * @param \Program\Data\Poll $poll sondage créé
   * @param \Program\Data\User $user utilisateur qui vient de créer le sondage
   * @return boolean
   */
  public static function SendCreatePollMail(\Program\Data\Poll $poll, \Program\Data\User $user)
  {
    Log::l(Log::DEBUG, "Mail::SendCreatePollMail()");
    $subject = Localization::g("Create poll mail subject", false);
    $message_id = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = '=?UTF-8?B?' . base64_encode('"' . $user->fullname . '"') . '?=' . "\r\n <" . $user->email . ">";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/created_poll.html');
    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
    $body = str_replace("%%user_fullname%%", $user->fullname, $body);

    return self::SendMail($from, $to, $subject, null, null, null, $body, $message_id);
  }
  /**
   * Méthode d'envoi du message de notification quand une nouvelle réponse est ajouté au sondage
   *
   * @param \Program\Data\Poll $poll sondage répondu
   * @param string $user_name nom de l'utilisateur qui vient de répondre
   * @param \Program\Data\Response $response réponse de l'utilisateur
   * @param \Program\Data\User $organizer organisateur du sondage à qui la notification est envoyé
   * @return boolean
   */
  public static function SendResponseNotificationMail(\Program\Data\Poll $poll, $user_name, \Program\Data\Response $response, \Program\Data\User $organizer)
  {
    Log::l(Log::DEBUG, "Mail::SendResponseNotificationMail()");
    $subject = Localization::g("Response notification mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendResponseNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = '=?UTF-8?B?' . base64_encode('"' . $organizer->fullname . '"') . '?=' . "\r\n <" . $organizer->email . ">";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/response_notification.html');
    if (\Program\Data\Poll::get_current_poll()->type == 'rdv') {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/response_notification_rdv.html');
    }

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $subject = str_replace("%%user_shortname%%", self::UserShortName($user_name), $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
    $body = str_replace("%%user_fullname%%", $user_name, $body);

    if (\Program\Data\Poll::get_current_poll()->type == 'rdv') {
      $array_response = unserialize($response->response);
      foreach ($array_response as $key => $value) {
        $user_response = $key;
        break;
      }
      $body = str_replace("%%user_response%%", $user_response, $body);
    }

    return self::SendMail($from, $to, $subject, null, null, null, $body, $message_id, $in_reply_to);
  }
  /**
   * Méthode d'envoi du message de notification quand une proposition du sondage est modifiée par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @param \Program\Data\User $user participant du sondage
   * @return boolean
   */
  public static function SendModifyResponseNotificationMail(\Program\Data\Poll $poll, \Program\Data\User $user, $modify_response)
  {
    Log::l(Log::DEBUG, "Mail::SendModifyResponseNotificationMail()");
    $subject = Localization::g("Modify response mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendModifyResponseNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/modify_response.html');

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);

    // Gestion des propositions
    $new_response_text = "\r\n\r\n" . Localization::g('New response', false) . ' :';
    $new_response_html = "<br><br><b>" . Localization::g('New response', false) . " :</b>";

    $prop = Output::format_prop_poll($poll, $modify_response);
    $new_response_text .= "\r\n$prop";
    $new_response_html .= "<br>$prop";


    $body = str_replace("%%new_response%%", $new_response_text, $body);
    $body = str_replace("%%html_new_response%%", $new_response_html, $body);

    if (!empty($user->email)) {
      $name = $user->auth ? $user->fullname : $user->username;
      if ($bcc != "") {
        $bcc .= "\r\n ";
      }
      $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";

      if ($bcc == "") {
        return false;
      }
    }

    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to);
  }
 /**
   * Méthode d'envoi du message de notification quand un participant du sondage est supprimé par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @param \Program\Data\User $user participant du sondage
   * @return boolean
   */
  public static function SendDeletedResponseNotificationMail(\Program\Data\Poll $poll, \Program\Data\User $user)
  {
    Log::l(Log::DEBUG, "Mail::SendDeleteResponseNotificationMail()");
    $subject = Localization::g("Deleted response mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendDeleteResponseNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/deleted_response.html');

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);

    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);

    if (!empty($user->email)) {
      $name = $user->auth ? $user->fullname : $user->username;
      if ($bcc != "") {
        $bcc .= "\r\n ";
      }
      $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";

      if ($bcc == "") {
        return false;
      }
    }

    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to);
  }

  /**
   * Méthode d'envoi du message de notification quand une proposition est validée par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @param string $prop_key identifiant de la proposition validée par l'organisateur
   * @return boolean
   */
  public static function SendValidateProposalNotificationMail(\Program\Data\Poll $poll, $prop_key)
  {
    Log::l(Log::DEBUG, "Mail::SendValidateProposalNotificationMail()");
    $subject = Localization::g("Validate proposal mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendValidateProposalNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $proposals = unserialize($poll->proposals);
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $unauth_bcc = "";
    if ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal_date.html');
      $as_attachment = false;
    } elseif ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal_date_ics.html');
      $as_attachment = true;
    } else {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal.html');
      $as_attachment = false;
    }

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $subject = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key], false), $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    $body = str_replace("%%organizer_shortname%%", self::UserShortName(\Program\Data\User::get_current_user()->fullname), $body);
    $body = str_replace("%%organizer_fullname%%", self::UserShortName(\Program\Data\User::get_current_user()->fullname), $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
    if ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR) {
      $body = str_replace("%%poll_add_calendar_url_accepted%%", Output::get_add_calendar_url($poll, $prop_key, \Program\Data\Event::PARTSTAT_ACCEPTED), $body);
      $body = str_replace("%%poll_add_calendar_url_declined%%", Output::get_add_calendar_url($poll, $prop_key, \Program\Data\Event::PARTSTAT_DECLINED), $body);
    } elseif ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
      $ics = \Program\Lib\Event\Drivers\Driver::get_driver()->generate_ics($proposals[$prop_key], null, null, null, null, true);
      $body = str_replace("%%ics%%", $ics, $body);
      $body = str_replace("%%poll_download_ics_url%%", Output::get_download_ics_url($poll, $prop_key), $body);
    }
    $body = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key]), $body);

    // Récupération des réponses du sondage
    $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
    // Parcour les réponses pour récupérer les adresses mails des participants
    foreach ($responses as $response) {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
      if (empty($user->email) || $user->user_id == $poll->organizer_id) {
        continue;
      }
      if ($user->auth == 1) {
        $name = $user->fullname;
        if ($bcc != "") {
          $bcc .= "\r\n ";
        }
        $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";
      } else {
        $name = $user->username;
        if ($unauth_bcc != "") {
          $unauth_bcc .= "\r\n ";
        }
        $unauth_bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";
      }
    }
    if ($bcc == "") {
      return false;
    }
    if ($unauth_bcc != "") {
      self::send_unauthentified_notification_mail($poll, $prop_key, $unauth_bcc);
    }
    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to, $as_attachment);
  }

  /**
   * Envoi du message de notification pour les utilisateurs non authentifiés
   *
   * @param \Program\Data\Poll $poll
   * @param string $prop_key
   * @param string $bcc
   * @return boolean
   */
  private static function send_unauthentified_notification_mail(\Program\Data\Poll $poll, $prop_key, $bcc)
  {
    Log::l(Log::DEBUG, "Mail::send_unauthentified_notification_mail()");
    $subject = Localization::g("Validate proposal mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "send_unauthentified_notification_mail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $proposals = unserialize($poll->proposals);
    $to = "undisclosed-recipients:;";
    if ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal_date_ics.html');
      $as_attachment = true;
    } else {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal.html');
      $as_attachment = false;
    }

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $subject = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key], false), $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    $body = str_replace("%%organizer_shortname%%", self::UserShortName(\Program\Data\User::get_current_user()->fullname), $body);
    $body = str_replace("%%organizer_fullname%%", self::UserShortName(\Program\Data\User::get_current_user()->fullname), $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
    if ($poll->type == "date" && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS) {
      $ics = \Program\Lib\Event\Drivers\Driver::get_driver()->generate_ics($proposals[$prop_key], null, null, null, null, true);
      $body = str_replace("%%ics%%", $ics, $body);
      $body = str_replace("%%poll_download_ics_url%%", Output::get_download_ics_url($poll, $prop_key), $body);
    }
    $body = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key]), $body);

    if ($bcc == "") {
      return false;
    }
    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to, $as_attachment);
  }

  /**
   * Méthode d'envoi du message de notification quand le sondage est modifié par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @return boolean
   */
  public static function SendModifyPollNotificationMail(\Program\Data\Poll $poll, $old_title = null, $old_location = null, $old_description = null)
  {
    Log::l(Log::DEBUG, "Mail::SendModifyPollNotificationMail()");
    $subject = Localization::g("Modify poll mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendModifyPollNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/modify_poll.html');

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", isset($old_title) ? $old_title : $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", isset($old_title) ? $old_title : $poll->title, $body);
    // Gestion du nouveau titre
    if (isset($old_title)) {
      $new_title = "\r\n" . Localization::g('New title', false) . ": " . $poll->title;
      $html_new_title = "<b>" . Localization::g('New title', false) . ": </b>" . $poll->title;
    } else {
      $new_title = '';
      $html_new_title = '';
    }
    $body = str_replace("%%new_title%%", $new_title, $body);
    $body = str_replace("%%html_new_title%%", $html_new_title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      if (isset($old_location)) {
        $location = "\r\n\r\n" . Localization::g('New location', false) . ": " . $poll->location . "\r\n" . Localization::g('Old location', false) . ": " . $old_location;
        $html_location = "<br><div><b>" . Localization::g('New location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div><div>" . Localization::g('Old location', false) . " : " . str_replace("\r\n", "<br>", htmlentities($old_location)) . "</div>";
      } else {
        $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
        $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
      }
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      if (isset($old_description)) {
        $description = "\r\n\r\n" . Localization::g('New description', false) . ":\r\n" . $poll->description;
        $html_description = "<br><div><b>" . Localization::g('New description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
      } else {
        $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
        $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
      }
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);

    // Récupération des réponses du sondage
    $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
    // Parcour les réponses pour récupérer les adresses mails des participants
    foreach ($responses as $response) {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
      if (empty($user->email)) {
        continue;
      }
      $name = $user->auth ? $user->fullname : $user->username;
      if ($bcc != "") {
        $bcc .= "\r\n ";
      }
      $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";
    }
    if ($bcc == "") {
      return false;
    }
    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to);
  }

  /**
   * Méthode d'envoi du message de notification quand une proposition du sondage est modifiée par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @return boolean
   */
  public static function SendModifyProposalsNotificationMail(\Program\Data\Poll $poll, $new_proposals = [], $deleted_proposals = [])
  {
    Log::l(Log::DEBUG, "Mail::SendModifyProposalsNotificationMail()");
    $subject = Localization::g("Modify proposals mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendModifyProposalsNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/modify_proposals.html');

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);

    // Gestion des propositions
    if (count($new_proposals) > 0) {
      if (count($new_proposals) == 1) {
        $new_proposals_text = "\r\n\r\n" . Localization::g('New proposal', false) . ' :';
        $new_proposals_html = "<br><br><b>" . Localization::g('New proposal', false) . " :</b>";
      } else {
        $new_proposals_text = "\r\n\r\n" . Localization::g('New proposals', false) . ' :';
        $new_proposals_html = "<br><br><b>" . Localization::g('New proposals', false) . " :</b>";
      }
      foreach ($new_proposals as $prop_value) {
        $prop = Output::format_prop_poll($poll, $prop_value);
        $new_proposals_text .= "\r\n$prop";
        $new_proposals_html .= "<br>$prop";
      }
    } else {
      $new_proposals_text = '';
      $new_proposals_html = '';
    }
    $body = str_replace("%%new_proposals%%", $new_proposals_text, $body);
    $body = str_replace("%%html_new_proposals%%", $new_proposals_html, $body);

    // Gestion des propositions
    if (count($deleted_proposals) > 0) {
      if (count($deleted_proposals) == 1) {
        $deleted_proposals_text = "\r\n\r\n" . Localization::g('Deleted proposal', false) . ' :';
        $deleted_proposals_html = "<br><br><b>" . Localization::g('Deleted proposal', false) . " :</b>";
      } else {
        $deleted_proposals_text = "\r\n\r\n" . Localization::g('Deleted proposals', false) . ' :';
        $deleted_proposals_html = "<br><br><b>" . Localization::g('Deleted proposals', false) . " :</b>";
      }
      foreach ($deleted_proposals as $prop_value) {
        $prop = Output::format_prop_poll($poll, $prop_value);
        $deleted_proposals_text .= "\r\n$prop";
        $deleted_proposals_html .= "<br>$prop";
      }
    } else {
      $deleted_proposals_text = '';
      $deleted_proposals_html = '';
    }
    $body = str_replace("%%deleted_proposals%%", $deleted_proposals_text, $body);
    $body = str_replace("%%html_delete_proposals%%", $deleted_proposals_html, $body);

    // Récupération des réponses du sondage
    $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
    // Parcour les réponses pour récupérer les adresses mails des participants
    foreach ($responses as $response) {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
      if (empty($user->email)) {
        continue;
      }
      $name = $user->auth ? $user->fullname : $user->username;
      if ($bcc != "") {
        $bcc .= "\r\n ";
      }
      $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";
    }
    if ($bcc == "") {
      return false;
    }
    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to);
  }

  /**
   * Méthode d'envoi du message de notification quand le sondage est supprimé par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage supprimé
   * @param array $responses tableau des réponses du sondage pour trouver les participants
   * @return boolean
   */
  public static function SendDeletedPollNotificationMail(\Program\Data\Poll $poll, $responses)
  {
    Log::l(Log::DEBUG, "Mail::SendDeletedPollNotificationMail()");
    $subject = Localization::g("Deleted poll mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendDeletedPollNotificationMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $to = "undisclosed-recipients:;";
    $bcc = "";
    $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/deleted_poll.html');

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);

    // Parcour les réponses pour récupérer les adresses mails des participants
    foreach ($responses as $response) {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
      if (empty($user->email)) {
        continue;
      }
      $name = $user->auth ? $user->fullname : $user->username;
      if ($bcc != "") {
        $bcc .= "\r\n ";
      }
      $bcc .= '=?UTF-8?B?' . base64_encode('"' . $name . '"') . '?=' . "\r\n <" . $user->email . ">";
    }
    if ($bcc == "") {
      return false;
    }
    return self::SendMail($from, $to, $subject, $bcc, null, null, $body, $message_id, $in_reply_to);
  }

  /**
   * Méthode d'envoi du message de notification quand une proposition est validée par l'organisateur
   *
   * @param \Program\Data\Poll $poll sondage validé
   * @param string $prop_key identifiant de la proposition validée par l'organisateur
   * @param boolean $notification_sent Défini si la notification a bien été envoyé aux participants
   * @return boolean
   */
  public static function SendValidateProposalOrganizerMail(\Program\Data\Poll $poll, $prop_key, $notification_sent = true)
  {
    Log::l(Log::DEBUG, "Mail::SendValidateProposalOrganizerMail()");
    $subject = Localization::g("Validate proposal organizer mail subject", false);
    $message_id = md5($poll->organizer_id . time() . "SendValidateProposalOrganizerMail") . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $in_reply_to = md5($poll->organizer_id) . $poll->poll_uid . '-' . strtotime($poll->created) . "@" . \Config\IHM::$TITLE;
    $from = \Config\IHM::$FROM_MAIL;
    $proposals = unserialize($poll->proposals);
    $organizer = \Program\Data\User::get_current_user();
    $to = '=?UTF-8?B?' . base64_encode('"' . $organizer->fullname . '"') . '?=' . "\r\n <" . $organizer->email . ">";
    if (
      $poll->type == "date"
      && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS
      && !\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR
    ) {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal_organizer_ics.html');
      $as_attachment = true;
    } else {
      $body = file_get_contents(__DIR__ . '/templates/' . \Config\IHM::$DEFAULT_LOCALIZATION . '/validate_proposal_organizer.html');
      $as_attachment = false;
    }

    // Replace elements
    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
    $subject = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key], false), $subject);
    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
    $body = str_replace("%%app_url%%", Output::get_main_url(), $body);
    $body = str_replace("%%poll_title%%", $poll->title, $body);
    // Gestion de l'emplacement
    if (!empty($poll->location)) {
      $location = "\r\n\r\n" . Localization::g('Edit location', false) . ": " . $poll->location;
      $html_location = "<br><div><b>" . Localization::g('Edit location', false) . " : </b>" . str_replace("\r\n", "<br>", htmlentities($poll->location)) . "</div>";
    } else {
      $location = '';
      $html_location = '';
    }
    $body = str_replace("%%poll_location%%", $location, $body);
    $body = str_replace("%%html_poll_location%%", $html_location, $body);
    // Gestion de la description
    if (!empty($poll->description)) {
      $description = "\r\n\r\n" . Localization::g('Edit description', false) . ":\r\n" . $poll->description;
      $html_description = "<br><div><b>" . Localization::g('Edit description', false) . " : </b></div><div>" . str_replace("\r\n", "<br>", htmlentities($poll->description)) . "</div>";
    } else {
      $description = '';
      $html_description = '';
    }
    $body = str_replace("%%poll_description%%", $description, $body);
    $body = str_replace("%%html_poll_description%%", $html_description, $body);
    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
    $body = str_replace("%%validate_proposal%%", Output::format_prop_poll($poll, $proposals[$prop_key]), $body);
    if (
      $poll->type == "date"
      && \Program\Lib\Event\Drivers\Driver::get_driver()->CAN_GENERATE_ICS
      && !\Program\Lib\Event\Drivers\Driver::get_driver()->CAN_WRITE_CALENDAR
    ) {
      $ics = \Program\Lib\Event\Drivers\Driver::get_driver()->generate_ics($proposals[$prop_key]);
      $body = str_replace("%%ics%%", $ics, $body);
      $body = str_replace("%%poll_download_ics_url%%", Output::get_download_ics_url($poll, $prop_key, \Program\Data\Event::PARTSTAT_ACCEPTED), $body);
    }

    // Récupération des réponses du sondage
    $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
    $email_attendees = [];
    $no_email_attendees = [];
    // Parcour les réponses pour récupérer les adresses mails des participants
    foreach ($responses as $response) {
      $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
      if ($user->user_id == $poll->organizer_id) {
        continue;
      }
      $name = $user->auth ? $user->fullname : $user->username;
      if (empty($user->email)) {
        $no_email_attendees[] = '"' . $name . '"';
      } else {
        $email_attendees[] = '"' . $name . '" <' . $user->email . '>';
      }
    }
    $attendees = "";
    if ($notification_sent) {
      // Si les participants ont été notifiés
      // Ajoute les participant notifiés
      if (count($email_attendees) > 0) {
        $attendees .= "\r\n\r\n" . Localization::g('Notified attendees list', false) . "\r\n" . implode("\r\n", $email_attendees);
      }
      // Ajout les participants non notifiés
      if (count($no_email_attendees) > 0) {
        $attendees .= "\r\n\r\n" . Localization::g('Unnotified attendees list', false) . "\r\n" . implode("\r\n", $no_email_attendees);
      }
    } else {
      // Si les participants n'ont pas été notifiés
      $attendees .= "\r\n\r\n" . Localization::g('Attendees were not notified', false);
      // Ajout les participants avec une adresse mail
      if (count($email_attendees) > 0) {
        $attendees .= "\r\n\r\n" . Localization::g('Attendees with email address', false) . "\r\n" . implode("\r\n", $email_attendees);
      }
      // Ajout les participants sans adresse mail
      if (count($no_email_attendees) > 0) {
        $attendees .= "\r\n\r\n" . Localization::g('Attendees without email address', false) . "\r\n" . implode("\r\n", $no_email_attendees);
      }
    }
    // Ajoute la liste des participants
    $body = str_replace("%%attendees_list%%", $attendees, $body);
    $body = str_replace("%%html_attendees_list%%", str_replace("\r\n", "<br>", htmlentities($attendees)), $body);
    return self::SendMail($from, $to, $subject, null, null, null, $body, $message_id, $in_reply_to, $as_attachment);
  }

  /**
   * Diminue la taille du nom de l'utilisateur
   *
   * @param string $username
   * @return string
   */
  private static function UserShortName($username)
  {
    if (strpos($username, " - ") !== false) {
      $username = explode(" - ", $username);
      $username = $username[0];
    }
    if (strpos($username, " (") !== false) {
      $username = explode(" (", $username);
      $username = $username[0];
    }
    return $username;
  }
}
