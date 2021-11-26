<?php

namespace Program\Lib\Api;

use DateInterval;
use DateTime;
use Program\Lib\Request\Session as s;
use Program\Drivers\Driver as d;
use Program\Lib\Utils\Utils as u;
use Program\Lib\Request\Request as r;

class Main
{

    public function __construct()
    {
    }

    /**
     * Créé un nouveau token si l'utilisateur est authentifié
     * @return Program\Data\User si ok, sinon null
     */
    public static function login()
    {
        $data = r::getJsonValue();

        $username = $data->username;
        $password = $data->password;
        if (d::get_driver()->authenticate($username, $password)) {
            $user = d::get_driver()->getAuthUser($username);
            $userToken = json_decode($user->token, true);

            $userToken = self::removeOldToken($user->username, $userToken);

            //Création d'un nouveau Token
            $expire_token = new DateTime();
            $interval = new DateInterval('P1D');
            $expire_token->add($interval);

            $token = array(
                'token' => u::random_string(32),
                'expire_token' => $expire_token
            );

            if ($userToken == null) {
                $userToken = array();
            }

            array_push($userToken, $token);
            self::responseMessage(d::get_driver()->addTokenUser($user->username, json_encode($userToken)), $user);
        } else {
            self::responseMessage(null, null);
        }
    }

    /**
     * Retourne l'état de la requête
     * @param string $value Fonction retournant true si ok, null sinon. Certaines fonctions retournent directement des valeurs
     * @param string $data Si la fonction $value ne retourne pas de valeur initialement
     * @return string
     */
    private function responseMessage($value, $data = null)
    {
        //Si la fonction renvoie null = echec
        if ($value == null) {
            $response = array(
                'message' => 'Echec',
                'status' => '0',
            );
        } else {
            //Si les valeurs sont dans $data
            if ($data != null) {
                $response = array(
                    'message' => 'OK',
                    'status' => '1',
                    'data' => json_encode($data),
                );
            } 
            //Si les valeurs sont dans $value
            else {
                $response = array(
                    'message' => 'OK',
                    'status' => '1',
                    'data' => json_encode($value),
                );
            }
        }

        echo json_encode($response);
    }

    /**
     * Supprime les token expirés de l'utilisateur
     * @param string $username Username de l'utilisateur
     * @param string[] $userToken Tableau de token de l'utilisateur
     * @return token[]
     */
    private function removeOldToken($username, $userToken)
    {
        $today = new DateTime();
        foreach ($userToken as $key => $value) {
            $expire = new DateTime($value['expire_token']['date']);
            if ($expire <= $today) {
                \array_splice($userToken, $key);
            }
        }
        d::get_driver()->addTokenUser($username, json_encode($userToken));

        return $userToken;
    }


    /**
     * Vérifie si le token est valide
     * @return Program\Data\User si ok, sinon null
     */
    private static function verifyToken()
    {
        //On récupère les headers de la requête
        $headers = r::getFilteredHeader();

        $user = d::get_driver()->getAuthUser($headers['Username']);
        $userToken = json_decode($user->token, true);

        $userToken = self::removeOldToken($headers['Username'], $userToken);
        foreach ($userToken as $value) {
            if ($value['token'] == $headers['Token']) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Récupération de la liste des sondage de l'utilisateur
     * @return Program\Data\Poll[]
     */
    public static function getPolls()
    {
        $user = self::verifyToken();

        if ($user != null) {
            $polls = d::get_driver()->listUserPolls($user->user_id);
            self::responseMessage($polls);
        } else {
            self::responseMessage(null);
        }
    }

    /**
     * Récupération de la liste des sondage auquel l'utilisateur à répondu
     * @return Program\Data\Poll[]
     */
    public static function getRespondedPolls()
    {
        $user = self::verifyToken();

        if ($user != null) {
            $polls = d::get_driver()->listUserRespondedPolls($user->user_id);
            self::responseMessage($polls);
        } else {
            self::responseMessage(null);
        }
    }

    /**
     * Retourne le sondage dont l'id est passé en paramètre
     * @param string $uid
     * @return Program\Data\Poll
     */
    public static function getPoll($uid)
    {
        
        $user = self::verifyToken();

        if ($user != null) {
            $poll = d::get_driver()->getPollByUid($uid);
            $orgnaizer = d::get_driver()->getUser($poll->organizer_id);
            $poll->organizer_username = $orgnaizer->username;
            $poll->organizer_email = $orgnaizer->email;

            $poll_response = d::get_driver()->getPollResponses($poll->poll_id);

            foreach ($poll_response as $response) {
                $user = d::get_driver()->getUser($response->user_id);
                $response->user_username = $user->username;
                $response->user_email = $user->email;
            }

            $poll->response = $poll_response;

            if ($poll->poll_uid != null) {
                self::responseMessage($poll);
            } else {
                self::responseMessage(null);
            }
        } else {
            self::responseMessage(null);
        }
    }

    /**
     * Retourne les infos de l'utilisateur
     * @return Program\Data\User
     */
    public static function getUser()
    {
        $user = self::verifyToken();

        if ($user != null) {
            $user = d::get_driver()->getUser($user->user_id);
            self::responseMessage($user);
        } else {
            self::responseMessage(null);
        }
    }

    /**
     * Créé un sondage
     * @return OK si valide, null sinon
     */
    public static function createPoll()
    {
        
        $data = r::getJsonValue();

        $user = self::verifyToken();

        $poll = new \Program\Data\Poll(array(
            "poll_uid" => $data->poll_uid,
            "title" => $data->title,
            "location" => $data->location,
            "description" => $data->description,
            "organizer_id" => $user->user_id,
            "type" => $data->type,
            "settings" => $data->settings,
        ));

        if ($poll->poll_uid == null) {
            $poll->poll_uid = \Program\Data\Poll::generation_uid();
            $response = d::get_driver()->createPoll($poll);
            if ($response != null) {
                self::responseMessage('OK');
            } else {
                self::responseMessage(null);
            }
        } else {
            $pollByUid = d::get_driver()->getPollByUid($poll->poll_uid);
            $poll->poll_id = $pollByUid->poll_id;

            $response = null;

            if ($pollByUid->organizer_id === $poll->organizer_id) {
                $response = d::get_driver()->modifyPoll($poll);
            }

            if ($response != null) {
                self::responseMessage('OK');
            } else {
                self::responseMessage(null);
            }
        }
    }

    /**
     * Ajoute une réponse à un sondage
     * @return OK si valide, null sinon
     */
    public static function responsePoll()
    {
        $data = r::getJsonValue();

        $user = self::verifyToken();

        $pollByUid = d::get_driver()->getPollByUid($data->poll_uid);

        $response = new \Program\Data\Response(array(
            "user_id" => $user->user_id,
            "poll_id" => $pollByUid->poll_id,
            "response" => $data->response,
        ));
        $ok = d::get_driver()->addPollUserResponse($response);
        if ($ok) {
            self::responseMessage('OK');
        } else {
            self::responseMessage(null);
        }
    }
}
