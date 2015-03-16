<?php
/**
 * Classe abstraite pour le driver de l'application
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
namespace Program\Drivers\Sqlite;

/**
 * Classe abstraite pour le driver
 * les drivers doivent être implémentée à partir de cette classe
 */
class Sqlite extends \Program\Drivers\Driver {
    /**
     * Authentification de l'utilisateur
     * Set le current user, charge les données dans le current user
     * si l'utilisateur n'existe pas, le créer
     * @param string $username
     * @param string $password
     * @return bool true si auth ok, false sinon
     */
    function authenticate($username, $password) {
        // Récupère l'utilisateur enregistré dans la base de données en fonction du username
        if (\Program\Data\User::isset_current_user()) {
            $user = \Program\Data\User::get_current_user();
            if ($user->username != $username) {
                unset($user);
            }
        }
        if (!isset($user)) {
            $user = $this->getAuthUser($username);
        }
        if (isset($user)) {
            // Vérify de le hash dans la base de données
            if (password_verify($password, $user->password)) {
                if (isset($user->user_id)) {
                    $user->last_login = date("Y-m-d H:i:s");
                    if (!\Program\Lib\Request\Session::is_setUsername())
                        $this->modifyUser($user);
                    \Program\Data\User::set_current_user($user);
                    return true;
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
        $query = "SELECT *, (SELECT count(*) FROM responses r WHERE poll_id = p.poll_id) as count_responses FROM polls p WHERE organizer_id = :user_id ORDER BY poll_id DESC;";
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
        $query = "SELECT p.*, (SELECT count(*) FROM responses r WHERE poll_id = p.poll_id) as count_responses FROM polls p INNER JOIN responses r USING (poll_id) WHERE r.user_id = :user_id ORDER BY poll_id DESC;";
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
     * @return \Program\Data\Poll Sondage à retourner
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
     * Création d'un utilisateur
     * @param \Program\Data\User $user
     * @return $user_id si OK, null sinon
    */
    function addUser(\Program\Data\User $user) {
        $query = "INSERT INTO users (username, password, email, fullname, last_login, auth, language) VALUES (:username, :password, :email, :fullname, :last_login, :auth, :language);";
        $params = array(
            "username" => $user->username,
            "password" => password_hash($user->password, PASSWORD_DEFAULT),
            "email" => $user->email,
            "fullname" => $user->fullname,
            "last_login" => $user->last_login,
            "auth" => $user->auth,
            "language" => $user->language,
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
        // Génération du hash pour le mot de passe
        if (isset($params['password'])) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }

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
     * @param \Program\Data\Poll $poll
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
     * @param \Program\Data\Poll $poll
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
     * @param string $poll_id Identifiant du sondage
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
        $query = "INSERT INTO responses (user_id, poll_id, response) VALUES (:user_id, :poll_id, :response);";
        $params = array(
            "user_id" => $response->user_id,
            "poll_id" => $response->poll_id,
            "response" => $response->response,
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
}