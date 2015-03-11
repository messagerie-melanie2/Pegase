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
use Program\Lib\Log\Log as Log;
use Program\Lib\Request\Output as Output;
use Program\Lib\Request\Request as Request;
use Program\Lib\Request\Localization as Localization;

/**
 * Classe de gestion des mails
 * 
 * @package    Lib
 * @subpackage Mail
 */
class Mail {	
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }
	
	/**
	 * Méthode pour envoyer un message
	 * @param string $from adresse utilisée pour envoyer le message
	 * @param string $to destinataire du message
	 * @param string $subject sujet du message
	 * @param string $body corp du message
	 * @param string $bcc [optionnel] liste des destinataires en copie cachée
	 * @return boolean
	 */
	public static function SendMail($from, $to, $subject, $body, $bcc = null) {
	    Log::l(Log::DEBUG, "Mail::SendMail($from, $to, $subject)");
	    $headers   = array();
	    $headers[] = "MIME-Version: 1.0";
	    $headers[] = "Content-type: text/plain; charset=UTF-8";
	    $headers[] = "Content-Transfer-Encoding: 8BIT";
	    $headers[] = "From: " . quoted_printable_encode($from);
	    if (isset($bcc)) {
	        $headers[] = "Bcc: " . $bcc;
	    }
	    $headers[] = "X-Mailer: ".quoted_printable_encode(\Config\IHM::$TITLE."/".VERSION);
	    $envelopefrom = "-f $from";
	    
	    return mail($to, mb_encode_mimeheader(utf8_decode($subject)), $body, implode("\r\n", $headers), $envelopefrom);
	}
	/**
	 * Méthode d'envoi du message notification à la création du sondage
	 * @param \Program\Data\Poll $poll sondage créé
	 * @param \Program\Data\User $user utilisateur qui vient de créer le sondage
	 * @return boolean
	 */
	public static function SendCreatePollMail(\Program\Data\Poll $poll, \Program\Data\User $user) {
	    Log::l(Log::DEBUG, "Mail::SendCreatePollMail()");
	    $subject = Localization::g("Create poll mail subject", false);
	    $body = Localization::g("Create poll mail body", false);
	    $from = \Config\IHM::$FROM_MAIL;
	    $to = '=?UTF-8?B?'.base64_encode('"'.$user->fullname.'"').'?='."\r\n <" . $user->email . ">";
	    $body .= Localization::g("Mail sent by a robot", false);
        // Replace elements	    
	    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
	    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
	    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
	    $body = str_replace("%%poll_title%%", $poll->title, $body);
	    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
	    $body = str_replace("%%user_fullname%%", $user->fullname, $body);
	    
	    return self::SendMail($from, $to, $subject, $body);
	}
	/**
	 * Méthode d'envoi du message de notification quand une nouvelle réponse est ajouté au sondage
	 * @param \Program\Data\Poll $poll sondage répondu
	 * @param string $user_name nom de l'utilisateur qui vient de répondre
	 * @param \Program\Data\Response $response réponse de l'utilisateur
	 * @param \Program\Data\User $organizer organisateur du sondage à qui la notification est envoyé
	 * @return boolean
	 */
	public static function SendResponseNotificationMail(\Program\Data\Poll $poll, $user_name, \Program\Data\Response $response, \Program\Data\User $organizer) {
	    Log::l(Log::DEBUG, "Mail::SendResponseNotificationMail()");
	    $subject = Localization::g("Response notification mail subject", false);
	    $body = Localization::g("Response notification mail body", false);
	    $from = \Config\IHM::$FROM_MAIL;
	    $to = '=?UTF-8?B?'.base64_encode('"'.$organizer->fullname.'"').'?='."\r\n <" . $organizer->email . ">";
	    $body .= Localization::g("Mail sent by a robot", false);
	    // Replace elements
	    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
	    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
	    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
	    $body = str_replace("%%poll_title%%", $poll->title, $body);
	    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
	    $body = str_replace("%%user_fullname%%", $user_name, $body);
	     
	    return self::SendMail($from, $to, $subject, $body);
	}
	/**
	 * Méthode d'envoi du message de notification quand une proposition est validée par l'organisateur
	 * @param \Program\Data\Poll $poll sondage validé
	 * @param string $prop_key identifiant de la proposition validée par l'organisateur
	 * @return boolean
	 */
	public static function SendValidateProposalNotificationMail(\Program\Data\Poll $poll, $prop_key) {
	    Log::l(Log::DEBUG, "Mail::SendValidateProposalNotificationMail()");
	    $subject = Localization::g("Validate proposal mail subject", false);
	    $body = Localization::g("Validate proposal mail body", false);
	    $from = \Config\IHM::$FROM_MAIL;
	    $proposals = unserialize($poll->proposals);
	    $to = "undisclosed-recipients:;";
	    $bcc = "";
	    $body .= Localization::g("Mail sent by a robot", false);
	    
	    // Replace elements
	    $subject = str_replace("%%app_name%%", \Config\IHM::$TITLE, $subject);
	    $subject = str_replace("%%poll_title%%", $poll->title, $subject);
	    $body = str_replace("%%app_name%%", \Config\IHM::$TITLE, $body);
	    $body = str_replace("%%poll_title%%", $poll->title, $body);
	    $body = str_replace("%%poll_url%%", Output::get_poll_url($poll), $body);
	    $body = str_replace("%%validate_proposal%%", $proposals[$prop_key], $body);
	    
	    // Récupération des réponses du sondage
	    $responses = \Program\Drivers\Driver::get_driver()->getPollResponses($poll->poll_id);
	    // Parcour les réponses pour récupérer les adresses mails des participants
	    foreach ($responses as $response) {	        
	        $user = \Program\Drivers\Driver::get_driver()->getUser($response->user_id);
	        if (empty($user->email) 
	                || $user->user_id == $poll->organizer_id) {
	            continue;
	        }	        
	        $name = $user->auth ? $user->fullname : $user->username;
	        if ($bcc != "") {
	            $bcc .= "\r\n ";
	        }
	        $bcc .= '=?UTF-8?B?'.base64_encode('"'.$name.'"').'?='."\r\n <" . $user->email . ">";
	    }
	    if ($bcc == "") {
	        return false;
	    }
	    return self::SendMail($from, $to, $subject, $body, $bcc);
	}
}