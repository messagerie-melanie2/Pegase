<?php
/**
 * Classe pour le driver de l'application
 *
 * @author Thomas Payen
 * @author APITECH
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
namespace Program\Drivers\ENS;

/**
 * Classe pour le driver
 * les drivers doivent être implémentée à partir de cette classe
 */
class ENS extends \Program\Drivers\Driver {
    /**
     * Authentification de l'utilisateur
     * Set le current user, charge les données dans le current user
     * si l'utilisateur n'existe pas, le créer
     * @param string $username
     * @param string $password
     * @return bool true si auth ok, false sinon
     */
    function authenticate($username, $password, $timezone = null) {
        $infos = \Program\Lib\Backend\Ldap\Ldap::GetUserInfos($username);
        if (isset($infos)) {
            if (\Program\Lib\Backend\Ldap\Ldap::GetInstance(\Config\Ldap::$AUTH_LDAP)->authenticate($infos['dn'], $password)) {
                // Récupération de l'utilisateur
                $user = $this->getAuthUser($username);
                if (isset($user)
                        && isset($user->user_id)) {
                    $user->last_login = date("Y-m-d H:i:s");
                    if (isset($timezone)) {
                      $user->timezone = $timezone;
                    }
                    if ($user->fullname != $infos['cn'][0]) $user->fullname = $infos['cn'][0];
                    if (isset($infos['mailroutingaddress'])) {
                        if ($user->email != $infos['mailroutingaddress'][0]) $user->email = $infos['mailroutingaddress'][0];
                    } elseif (isset($infos['mail'])) {
                        if ($user->email != $infos['mail'][0]) $user->email = $infos['mail'][0];
                    }
                    if (!\Program\Lib\Request\Session::is_setUsername())
                        $this->modifyUser($user);
                    \Program\Data\User::set_current_user($user);
                    return true;
                }
                else {
                    $user = new \Program\Data\User(
                            array(
                                "username" => $username,
                                "fullname" => $infos['cn'][0],
                                "email" => isset($infos['mailroutingaddress']) ? $infos['mailroutingaddress'][0] : $infos['mail'][0],
                                "last_login" => date("Y-m-d H:i:s"),
                                "language" => "fr_FR",
                                "auth" => 1,
                            )
                    );
                    if (isset($timezone)) {
                      $user->timezone = $timezone;
                    }
                    // Création de l'utilisateur dans la base de données
                    $user_id = $this->addUser($user);
                    if (!is_null($user_id)) {
                        // Si l'utilisateur est bien créé
                        //$user = $this->getAuthUser($username);
                        $user = $this->getUser($user_id);
                        if (isset($user)
                                && isset($user->user_id)) {
                            \Program\Data\User::set_current_user($user);
                            return true;
                        }
                    }
                }
            }
        }
        //
        return false;
    }

    /**
     * Récupération de la liste des sondage pour l'utilisateur
     * @param int $user_id Identifiant de l'utilisateur
     * @return \Program\Data\Poll[] Liste des sondages
     */
    function listUserPolls($user_id) {
        $query = "SELECT *, (SELECT count(*) FROM responses r WHERE poll_id = p.poll_id) as count_responses FROM polls p WHERE organizer_id = :user_id AND deleted = 0 ORDER BY poll_id DESC;";
        $params = array(
            "user_id" => $user_id,
        );
        // Execution de la requête, retourne le résultat un array de Poll
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params, 'Program\Data\Poll');
    }
    
    /**
     * Récupération de la liste des sondage supprimés pour l'utilisateur
     * @param int $user_id Identifiant de l'utilisateur
     * @return Program\Data\Poll[] Liste des sondages
     */
    function listUserDeletedPolls($user_id) {
      $query = "SELECT *, (SELECT count(*) FROM responses r WHERE poll_id = p.poll_id) as count_responses FROM polls p WHERE organizer_id = :user_id AND deleted = 1 ORDER BY modified DESC;";
      $params = array(
          "user_id" => $user_id,
      );
      // Execution de la requête, retourne le résultat un array de Poll
      return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params, 'Program\Data\Poll');
    }

    /**
     * Récupération de la liste des sondage auquel l'utilisateur à répondu
     * @param int $user_id Identifiant de l'utilisateur
     * @return \Program\Data\Poll[] Liste des sondages
     */
    function listUserRespondedPolls($user_id) {
        $query = "SELECT p.*, (SELECT count(*) FROM responses r WHERE poll_id = p.poll_id) as count_responses FROM polls p INNER JOIN responses r USING (poll_id) WHERE r.user_id = :user_id AND deleted = 0 ORDER BY poll_id DESC;";
        $params = array(
            "user_id" => $user_id,
        );
        // Execution de la requête, retourne le résultat un array de Poll
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params, 'Program\Data\Poll');
    }

    /**
     * Récupération du nombre de réponse pour un sondage
     * @param string $poll_id Identifiant du sondage
     * @return int Nombre de réponses
     */
    function countPollResponses($poll_id) {
        $query = "SELECT count(*) FROM responses r WHERE poll_id = :poll_id;";
        $params = array(
            "poll_id" => $poll_id,
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
            return $ret[0]['count'];
        return false;
    }

    /**
     * Récupération des informations du sondage
     * @param int $poll_id Identifiant du sondage
     * @return Program\Data\Poll Sondage à retourner
     */
    function getPoll($poll_id) {
        $query = "SELECT * FROM polls WHERE poll_id = :poll_id;";
        $params = array(
            "poll_id" => $poll_id,
        );
        // Execution de la requête, recupère le résultat dans l'objet poll
        $poll = new \Program\Data\Poll();
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $poll);
        // RAZ des has changed de l'objet
        $poll->__initialize_haschanged();
        return $poll;
    }

    /**
     * Récupération des informations du sondage
     * @param string $poll_uid Identifiant unique du sondage
     * @return \Program\Data\Poll Sondage à retourner
     */
    function getPollByUid($poll_uid) {
        $query = "SELECT * FROM polls WHERE poll_uid = :poll_uid;";
        $params = array(
            "poll_uid" => $poll_uid,
        );
        // Execution de la requête, recupère le résultat dans l'objet poll
        $poll = new \Program\Data\Poll();
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $poll);
        // RAZ des has changed de l'objet
        $poll->__initialize_haschanged();
        return $poll;
    }

    /**
     * Récupération de la liste des réponses pour un sondage
     * @param string $poll_id Identifiant du sondage
     * @return \Program\Data\Response[] Liste des réponses pour le sondage
     */
    function getPollResponses($poll_id) {
        $query = "SELECT * FROM responses WHERE poll_id = :poll_id ORDER BY response_time;";
        $params = array(
            "poll_id" => $poll_id,
        );
        // Execution de la requête, retourne le résultat un array de Poll
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params, 'Program\Data\Response');
    }

    /**
     * Récupère l'utilisateur en fonction du username
     * Il s'agit donc forcément d'un utilisateur authentifié
     * @param string $username
     * @return \Program\Data\User
     */
    function getAuthUser($username) {
        $query = "SELECT * FROM users WHERE auth = :auth AND username = :username;";
        $params = array(
            "username" => $username,
            "auth" => "1",
        );
        // Execution de la requête, recupère le résultat dans l'objet user
        $user = new \Program\Data\User();
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $user);
        // RAZ des has changed de l'objet
        $user->__initialize_haschanged();
        return $user;
    }
    
    /**
     * Récupère l'utilisateur en fonction de son email
     * Il s'agit donc forcément d'un utilisateur authentifié
     * @param string $email
     * @return Program\Data\User
     */
    function getAuthUserByEmail($email) {
      $query = "SELECT * FROM users WHERE auth = :auth AND lower(email) = lower(:email);";
      $params = array(
          "email" => $email,
          "auth" => "1",
      );
      // Execution de la requête, recupère le résultat dans l'objet user
      $user = new \Program\Data\User();
      \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $user);
      // RAZ des has changed de l'objet
      $user->__initialize_haschanged();
      return $user;
    }

    /**
     * Récupère l'utilisateur en fonction du user_id
     * @param int $user_id
     * @return \Program\Data\User
     */
    function getUser($user_id) {
        $query = "SELECT * FROM users WHERE user_id = :user_id;";
        $params = array(
            "user_id" => $user_id,
        );
        // Execution de la requête, recupère le résultat dans l'objet user
        $user = new \Program\Data\User();
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $user);
        // RAZ des has changed de l'objet
        $user->__initialize_haschanged();
        return $user;
    }

    /**
     * Ajout d'un token à un utilisateur
     * @param string $username
     * @param string $token
     * @return true si ok, false sinon
    */
    function addTokenUser($username, $token) {
        $query = "UPDATE users SET token = :token WHERE username = :username;";
        $params = array(
            "username" => $username,
            "token" => $token,
        );
        // Execution de la requête
        if (\Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Création d'un utilisateur
     * @param \Program\Data\User $user
     * @return $user_id si OK, null sinon
     */
    function addUser(\Program\Data\User $user) {
        $query = "INSERT INTO users (username, email, fullname, last_login, auth, language, preferences) VALUES (:username, :email, :fullname, :last_login, :auth, :language, :preferences);";
        $params = array(
            "username" => $user->username,
            "email" => $user->email,
            "fullname" => $user->fullname,
            "last_login" => $user->last_login,
            "auth" => $user->auth,
            "language" => $user->language,
            "preferences" => $user->preferences ?: "",
        );
        // Execution de la requête
        if (\Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params)) {
            return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->lastInsertId("users_seq");
        } else {
            return null;
        }
    }

    /**
     * Modification d'un utilisateur
     * @param \Program\Data\User $user
     * @return bool true si ok, false sinon
     */
    function modifyUser(\Program\Data\User $user) {
        $set = "";
        $params = array();
        foreach($user->__get_haschanged() as $key => $value) {
            if ($value) {
                if ($set != "") $set .= ", ";
                $set .= "$key = :$key";
                $params[$key] =  $user->$key;
            }
        }
        if ($set == "") return false;
        $query = "UPDATE users SET $set WHERE user_id = :user_id;";
        $params["user_id"] = $user->user_id;

        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Suppression de l'utilisateur
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool True si ok, false sinon
     */
    function deleteUser($user_id) {
        $query = "DELETE FROM users WHERE user_id = :user_id;";
        $params = array(
                "user_id" => $user_id,
            );
        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Création d'un nouveau sondage
     * @param Program\Data\Poll $poll
     * @return $poll_id si ok, null sinon
     */
    function createPoll(\Program\Data\Poll $poll) {
        $query = "INSERT INTO polls (poll_uid, title, location, description, organizer_id, type, settings) VALUES (:poll_uid, :title, :location, :description, :organizer_id, :type, :settings);";
        $params = array(
            "poll_uid" => $poll->poll_uid,
            "title" => $poll->title,
            "location" => $poll->location,
            "description" => $poll->description,
            "organizer_id" => $poll->organizer_id,
            "type" => $poll->type,
            "settings" => $poll->settings,
        );
        // Execution de la requête
        if (\Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params)) {
            return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->lastInsertId("polls_seq");
        } else {
            return null;
        }
    }

    /**
     * Modification d'un sondage existant
     * @param Program\Data\Poll $poll
     * @return bool true si ok, false sinon
     */
    function modifyPoll(\Program\Data\Poll $poll) {
        $set = "";
        $params = array();
        $poll->modified = date("Y-m-d H:i:s");
        foreach($poll->__get_haschanged() as $key => $value) {
            if ($value) {
                if ($set != "") $set .= ", ";
                $set .= "$key = :$key";
                $params[$key] =  $poll->$key;
            }
        }
        if ($set == "") return false;
        $query = "UPDATE polls SET $set WHERE poll_id = :poll_id;";
        $params["poll_id"] = $poll->poll_id;

        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }
    
    /**
     * Suppression d'un sondage
     * @param string $poll_id
     * @return bool true si ok, false sinon
     */
    function deletePoll($poll_id) {
        $query = "UPDATE polls SET deleted = 1, modified = :modified WHERE poll_id = :poll_id;";
        $params = array(
            "poll_id"   => $poll_id,
            "modified"  => date("Y-m-d H:i:s"),
        );
        
        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }
    
    /**
     * Restaurer un sondage supprimé
     * @param string $poll_id
     * @return bool true si ok, false sinon
     */
    function restorePoll($poll_id) {
        $query = "UPDATE polls SET deleted = 0, modified = :modified WHERE poll_id = :poll_id;";
        $params = array(
            "poll_id"   => $poll_id,
            "modified"  => date("Y-m-d H:i:s"),
        );
        
        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Supprimer définitivement un sondage supprimé
     * @param string $poll_id
     * @return bool true si ok, false sinon
     */
    function erasePoll($poll_id) {
        // Récupère les réponses pour supprimer les user non authentifié
        $responses = $this->getPollResponses($poll_id);
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->beginTransaction();
        foreach ($responses as $response) {
            $user = $this->getUser($response->user_id);
            if ($user->auth == 0) {
                if (!$this->deleteUser($user->user_id)) {
                    \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->rollBack();
                    return false;
                }
            }
        }
        // Suppression du sondage
        $query = "DELETE FROM polls WHERE poll_id = :poll_id;";
        $params = array(
                "poll_id" => $poll_id,
            );
        // Execution de la requête
        if (!\Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params)) {
            \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->rollBack();
            return false;
        } else {
            \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->commit();
            return true;
        }
    }

    /**
     * Test si l'identifiant unique du sondage existe déjà
     * @param string $poll_uid
     * @return bool true si l'uid existe, false sinon
     */
    function isPollUidExists($poll_uid) {
        $query = "SELECT count(*) FROM polls WHERE poll_uid = :poll_uid;";
        $params = array(
            "poll_uid" => $poll_uid,
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count'])
                && $ret[0]['count'] > 0)
            return true;
        return false;
    }

    /**
     * Récupère la réponse de l'utilisateur sur un sondage
     * @param int $user_id Identifiant de l'utilisateur
     * @param int $poll_id Identifiant du sondage
     * @return \Program\Data\Response
     */
    function getPollUserResponse($user_id, $poll_id) {
        $query = "SELECT * FROM responses WHERE user_id = :user_id AND poll_id = :poll_id;";
        $params = array(
                "user_id" => $user_id,
                "poll_id" => $poll_id,
            );
        // Execution de la requête, recupère le résultat dans l'objet response
        $reponse = new \Program\Data\Response();
        \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $reponse);
        // RAZ des has changed de l'objet
        $reponse->__initialize_haschanged();
        return $reponse;
    }

    /**
     * Ajoute une réponse pour l'utilisateur sur un sondage
     * @param \Program\Data\Response $response
     * @return true si ok, false sinon
    */
    function addPollUserResponse(\Program\Data\Response $response) {
        $query = "INSERT INTO responses (user_id, poll_id, response, settings) VALUES (:user_id, :poll_id, :response, :settings);";
        $params = array(
            "user_id" => $response->user_id,
            "poll_id" => $response->poll_id,
            "response" => $response->response,
            "settings" => isset($response->settings) ? $response->settings : "",
        );
        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Modifie la réponse de l'utilisateur sur un sondage
     * @param \Program\Data\Response $response
     * @return true si ok, false sinon
     */
    function modifyPollUserResponse(\Program\Data\Response $response) {
        $set = "";
        $params = array();
        foreach($response->__get_haschanged() as $key => $value) {
            if ($value) {
                if ($set != "") $set .= ", ";
                $set .= "$key = :$key";
                $params[$key] =  $response->$key;
            }
        }
        if ($set == "") return false;
        $query = "UPDATE responses SET $set WHERE poll_id = :poll_id AND user_id = :user_id;";
        $params["poll_id"] = $response->poll_id;
        $params["user_id"] = $response->user_id;

        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Supprime la réponse de l'utilisateur sur un sondage
     * @param int $user_id Identifiant de l'utilisateur
     * @param string $poll_id Identifiant du sondage
     * @return true si ok, false sinon
     */
    function deletePollUserResponse($user_id, $poll_id) {
        $query = "DELETE FROM responses WHERE poll_id = :poll_id AND user_id = :user_id;";
        $params = array(
                "poll_id" => $poll_id,
                "user_id" => $user_id,
            );

        // Execution de la requête
        return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Récupère la liste des événements de l'utilisateur sur un sondage
     * @param int $user_id Identifiant de l'utilisateur
     * @param int $poll_id Identifiant du sondage
     * @return \Program\Data\EventsList
     */
    function getPollUserEventsList($user_id, $poll_id) {
    	$query = "SELECT * FROM eventslist WHERE user_id = :user_id AND poll_id = :poll_id;";
    	$params = array(
    			"user_id" => $user_id,
    			"poll_id" => $poll_id,
    	);
    	// Execution de la requête, recupère le résultat dans l'objet eventslist
    	$eventslist = new \Program\Data\EventsList();
    	\Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQueryToObject($query, $params, $eventslist);
    	// RAZ des has changed de l'objet
    	$eventslist->__initialize_haschanged();
    	return $eventslist;
    }

    /**
     * Enregistre les events list pour l'utilisateur sur un sondage
     * @param \Program\Data\EventsList $eventslist
     * @return true si ok, false sinon
     */
    function addPollUserEventsList(\Program\Data\EventsList $eventslist) {
    	$query = "INSERT INTO eventslist (user_id, poll_id, events, events_status, settings, modified_time) VALUES (:user_id, :poll_id, :events, :events_status, :settings, :modified_time);";
    	$params = array(
    			"user_id" => $eventslist->user_id,
    			"poll_id" => $eventslist->poll_id,
    			"events" => $eventslist->events,
    			"events_status" => $eventslist->events_status,
    			"settings" => $eventslist->settings,
    			"modified_time" => $eventslist->modified_time,
    	);
    	// Execution de la requête
    	return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Modifie les events list de l'utilisateur sur un sondage
     * @param \Program\Data\EventsList $eventslist
     * @return true si ok, false sinon
     */
    function modifyPollUserEventsList(\Program\Data\EventsList $eventslist) {
    	$set = "";
    	$params = array();
    	foreach($eventslist->__get_haschanged() as $key => $value) {
    		if ($value) {
    			if ($set != "") $set .= ", ";
    			$set .= "$key = :$key";
    			$params[$key] =  $eventslist->$key;
    		}
    	}
    	if ($set == "") return false;
    	$query = "UPDATE eventslist SET $set WHERE poll_id = :poll_id AND user_id = :user_id;";
    	$params["poll_id"] = $eventslist->poll_id;
    	$params["user_id"] = $eventslist->user_id;

    	// Execution de la requête
    	return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }

    /**
     * Supprime les events list de l'utilisateur sur un sondage
     * @param int $user_id Identifiant de l'utilisateur
     * @param string $poll_id Identifiant du sondage
     * @return true si ok, false sinon
     */
    function deletePollUserEventsList($user_id, $poll_id) {
    	$query = "DELETE FROM eventslist WHERE poll_id = :poll_id AND user_id = :user_id;";
    	$params = array(
    			"poll_id" => $poll_id,
    			"user_id" => $user_id,
    	);

    	// Execution de la requête
    	return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$WRITE_SERVER)->executeQuery($query, $params);
    }
    
    /**
     * Permet de récupérer une liste de réponses dans un lapse de temps
     *
     * @param int $user_id
     * @param \DateTime $start
     * @param \DateTime $end
     * @return \Program\Data\Response[]
     */
    function getResponsesByRange($user_id, $start, $end) {
      $query = "SELECT p.title as poll_title, r.* FROM responses r INNER JOIN polls p USING (poll_id) WHERE r.user_id = :user_id AND p.locked = :poll_locked AND p.type = :poll_type AND ((p.date_end >= :date_start AND p.date_end <= :date_end) OR (p.date_start >= :date_start AND p.date_start <= :date_end)) ORDER BY response_time;";
      $params = [
          "user_id" => $user_id,
          "poll_locked" => 0,
          "poll_type" => "date",
          "date_start" => $start->format('Y-m-d H:i:s'),
          "date_end" => $end->format('Y-m-d H:i:s'),
      ];
      // Execution de la requête, retourne le résultat un array de Response
      return \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params, 'Program\Data\Response');
    }

    /**
     * Permet de récupérer la liste des balp pour l'utilisateur courant
     * @return 
     */
    function listBAL(){
        $originaluser = \Program\Data\User::get_original_user();
        $userMel = new \LibMelanie\Api\Mel\User();
        $userMel->email = $originaluser->email;
        $userMel->load(['fullname', 'email_send', 'email_send_list', 'uid']);
        $listbal = $userMel->getObjectsSharedGestionnaire();
        $listuser = [];
        array_push($listuser, array("fullname" => $originaluser->fullname, "uid" => $originaluser->username, "mailboxuid" => $originaluser->username));
        foreach($listbal as $bal){
            $userinfos = array("fullname" => $bal->mailbox->fullname, "uid" => $bal->uid, "mailboxuid" => $bal->mailbox->uid);
            array_push($listuser, $userinfos);
        }
        return $listuser;
    }
    /**
     * permet de changer l'utilisateur pour une de ses boites mails
     * @param string $username
     */
    function changeUser($username){
        //verification que l'utilisateur authentifié a bien accès à la bal souhaitée
        $originaluser = \Program\Data\User::get_original_user();
        $userMel = new \LibMelanie\Api\Mel\User();
        $userMel->email = $originaluser->email;
        $userMel->load(['fullname', 'email_send', 'email_send_list', 'uid']);
        $listbal = $userMel->getObjectsSharedGestionnaire();
        $lock = false;
        foreach($listbal as $bal){
            if($username == $bal->uid)    
            $lock = true;
        }
        if($username == $originaluser->username){
            $user = $this->getAuthUser($originaluser->username);
            \Program\Data\User::set_current_user($user);
            return true;
        }
        if($lock){
            if (\Program\Lib\Request\Session::is_set('SSO')) {
                // Récupération de l'utilisateur
                $user = $this->getAuthUser($listbal[$username]->mailbox->uid);
                if (isset($user)
                    && isset($user->user_id)) {
                  $user->last_login = date("Y-m-d H:i:s");
                  if (!\Program\Lib\Request\Session::is_setUsername())
                    $this->modifyUser($user);
                  \Program\Data\User::set_current_user($user);
                  return true;
                }
                else {
                  return false;
                }
            }
            else {
                $user = $this->getAuthUser($listbal[$username]->mailbox->uid);
                if(isset($user) && isset($user->user_id)){
                    $user->last_login = date("Y-m-d H:i:s");
                    if ($user->fullname != $listbal[$username]->mailbox->fullname)
                        $user->fullname = $listbal[$username]->mailbox->fullname;
                    if ($user->email != $listbal[$username]->mailbox->email)
                        $user->email = $listbal[$username]->mailbox->email;
                    if (!\Program\Lib\Request\Session::is_setUsername()) {
                        $this->modifyUser($user);
                    }
                    \Program\Data\User::set_current_user($user);
                    return true;   
                }
                else{
                    $user = new \Program\Data\User(
                        array(
                            "username" => $listbal[$username]->mailbox->uid,
                            "fullname" => $listbal[$username]->mailbox->fullname,
                            "email" => $listbal[$username]->mailbox->email,
                            "last_login" => date("Y-m-d H:i:s"),
                            "language" => "fr_FR",
                            "auth" => 1,
                        )
                    );
                    if (isset($timezone)) {
                    $user->timezone = $timezone;
                    }
                    // Création de l'utilisateur dans la base de données
                    $user_id = $this->addUser($user);
                    if (!is_null($user_id)) {
                        // Si l'utilisateur est bien créé
                        //$user = $this->getAuthUser($username);
                        $user = $this->getUser($user_id);
                        if (isset($user) && isset($user->user_id)) {
                            \Program\Data\User::set_current_user($user);
                            return true;
                        }
                    }
                }
            }
        }
        else {
            return false;
        }
    }
    

    /***** STATISTIQUES *******/
    /**
     * [STATISTIQUES]
     * Récupération du nombre d'utilisateur authentifié qui se sont connecté entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre d'utilisateurs connectés
     */
    function countAuthUsers($start, $end) {
        $query = "SELECT count(*) FROM users u WHERE last_login >= :start AND last_login <= :end AND auth = 1;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
        return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre d'utilisateur non authentifié qui ont été créé entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre d'utilisateurs non authentifié
     */
    function countNoauthUsers($start, $end) {
        $query = "SELECT count(*) FROM users u WHERE created >= :start AND created <= :end AND auth = 0;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de sondages qui ont été créé entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de sondages créés
     */
    function countPolls($start, $end) {
        $query = "SELECT count(*) FROM polls p WHERE created >= :start AND created <= :end;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de sondages dates qui ont été créé entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de sondages créés
     */
    function countDatePolls($start, $end) {
        $query = "SELECT count(*) FROM polls p WHERE created >= :start AND created <= :end AND type = 'date' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de sondages libres qui ont été créé entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de sondages créés
     */
    function countPropPolls($start, $end) {
        $query = "SELECT count(*) FROM polls p WHERE created >= :start AND created <= :end AND type = 'prop' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de sondages rdv qui ont été créé entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de sondages créés
     */
    function countRdvPolls($start, $end) {
        $query = "SELECT count(*) FROM polls p WHERE created >= :start AND created <= :end AND type = 'rdv' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de réponses qui ont été faites entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de réponses faites
     */
    function countResponses($start, $end) {
        $query = "SELECT count(*) FROM responses r WHERE response_time >= :start AND response_time <= :end;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de réponses à des sondage de date qui ont été faites entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de réponses faites
     */
    function countDateResponses($start, $end) {
        $query = "SELECT count(*) FROM responses r INNER JOIN polls p ON r.poll_id = p.poll_id WHERE r.response_time >= :start AND r.response_time <= :end AND p.type = 'date' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }

    /**
     * [STATISTIQUES]
     * Récupération du nombre de réponses à des sondage libres qui ont été faites entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de réponses faites
     */
    function countPropResponses($start, $end) {
        $query = "SELECT count(*) FROM responses r INNER JOIN polls p ON r.poll_id = p.poll_id WHERE r.response_time >= :start AND r.response_time <= :end AND p.type = 'prop' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }
     
    /**
     * [STATISTIQUES]
     * Récupération du nombre de réponses à des sondage de Rdv qui ont été faites entre start et end
     * @param DateTime $start Début des recherches pour les statistiques
     * @param DateTime $end Fin des recherches pour les statistiques
     * @return int Nombre de réponses faites
     */
    function countRdvResponses($start, $end) {
        $query = "SELECT count(*) FROM responses r INNER JOIN polls p ON r.poll_id = p.poll_id WHERE r.response_time >= :start AND r.response_time <= :end AND p.type = 'rdv' ;";
        $params = array(
                        "start" => $start->format('Y-m-d H:i:s'),
                        "end" => $end->format('Y-m-d H:i:s'),
        );
        // Execution de la requête, retourne le résultat un array de Poll
        $ret = \Program\Lib\Backend\DB\DB::GetInstance(\Config\Sql::$READ_SERVER)->executeQuery($query, $params);
        // Retourne le résultat du count
        if (is_array($ret)
                && count($ret) > 0
                && isset($ret[0]['count']))
                    return $ret[0]['count'];
                return false;
    }
}