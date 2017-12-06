<?php
/**
 * Liste des définitions pour l'application de sondage
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
/* Récupération de la version depuis le fichier */
define('VERSION', '1.0.5');
define('BUILD', '1702131754');

// framework constants
define('POLL_CHARSET', 'UTF-8');

/*
 * Variables pour l'environnement
 */
define('FEDERATEUR','af');
define('DEVELOPPEMENT_FEDERATEUR','afida');
define('DEVELOPPEMENT','ida');
define('DEVELOPPEMENT_ROUNDCUBE', 'ida_roundcube');
define('PRODUCTION', 'ac_prod');
define('PREPRODUCTION', 'ac_preprod');

/*
 * Définitions des inputs
 */
define('POLL_INPUT_GET','input_get');
define('POLL_INPUT_POST','input_post');
define('POLL_INPUT_GPC','input_gpc');

/*
 * Liste des tasks accessibles par défaut
 */
define('TASK_MAIN','main');
define('TASK_CREATE','create');

/*
 * Liste des actions possibles
 */
define('ACT_NEW','new');
define('ACT_MODIFY','modify');
define('ACT_MODIFY_ALL','modify_all');
define('ACT_LOCK','lock');
define('ACT_UNLOCK','unlock');
define('ACT_DELETE','delete');
define('ACT_DELETE_RESPONSE','delete_response');
define('ACT_MOBILE','mobile');
define('ACT_DESKTOP','desktop');
define('ACT_DOWNLOAD_ICS','download_ics');
define('ACT_ADD_CALENDAR','add_calendar');
define('ACT_ADD_TENTATIVE_CALENDAR','add_tentative_calendar');
define('ACT_VALIDATE_PROP','validate_prop');
define('ACT_UNVALIDATE_PROP','unvalidate_prop');
define('ACT_GET_VALID_PROPOSALS','get_valid_proposals_text');
define('ACT_GET_USER_EVENTS','get_user_events_json');
define('ACT_GET_USER_FREEBUSY','get_user_freebusy');
define('ACT_DELETE_TENTATIVES','delete_tentatives');
define('ACT_DATE_ACCEPTED','date_accepted');
define('ACT_DATE_DECLINED','date_declined');