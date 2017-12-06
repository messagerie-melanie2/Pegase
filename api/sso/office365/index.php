<?php
/**
 * Programme de sondage
 * 
 * Gestion du SSO Office 365 avec authentification unique
 *
 * @author Thomas Payen
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

// Utilisation des namespaces
use
	Program\Lib\Request\Template as t,
	Program\Lib\Request\Request as r,
	Program\Lib\Request\Output as o,
	Program\Lib\Request\Session as s,
	Program\Lib\Request\Localization as l;

// Inclusion
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/../../..');
include 'program/include/includes.php';

// Initialisation de l'output
o::init();

// Get the 'code' and 'session_state' parameters from
// the GET request
$code = r::getInputValue('code', POLL_INPUT_GET);
$session_state = r::getInputValue('session_state', POLL_INPUT_GET);

// Récupération de l'url
$url = Config\IHM::$HOST;

// Page d'erreur
$errorPage = $url."?_p=error"; 

if (is_null($code)) {
 	$username = \Program\Lib\Request\Session::getUsername();
    // Destruction de la session
	\Program\Lib\Request\Session::destroy();
	// Redirection vers la connexion
	header('Location: ' . (isset(\Config\IHM::$LOGIN_URL) ? \Config\IHM::$LOGIN_URL : '?_p=login'));
	\Program\Lib\Log\Log::l(\Program\Lib\Log\Log::INFO, "Logout::Process() Logout for user $username");
	exit();
}
else {
  error_log("authorize.php called with code: ".$code);
  $redirectUri = $url."api/sso/office365/index.php"; 
  
  error_log("Calling getTokenFromAuthCode");
  // Use the code supplied by Azure to request an access token.
  $tokens = Api\SSO\Office365\Office365Service::getTokenFromAuthCode($code, $redirectUri);
  if (isset($tokens['access_token'])) {
    error_log("getTokenFromAuthCode returned:");
    error_log("  access_token: ".$tokens['access_token']);
    error_log("  refresh_token: ".$tokens['refresh_token']);
    
    // Save the access token and refresh token to the session.
    s::set('accessToken', $tokens['access_token']);
    s::set('refreshToken', $tokens['refresh_token']);
    s::set('expireToken', $tokens['expires_on']);
    s::set('pwdExp', $tokens['pwd_exp']);
    s::set('pwdUrl', $tokens['pwd_url']);
    
    // Parse the id token returned in the response to get the user
    $user_token = Api\SSO\Office365\Office365Service::getDecodedToken($tokens['id_token']);
    $user = Program\Drivers\Driver::get_driver()->getAuthUser($user_token['upn']);
    if (isset($user)
    		&& isset($user->user_id)) {
   		$user->last_login = date("Y-m-d H:i:s");
    	if ($user->fullname != $user_token['name']) $user->fullname = $user_token['name'];
    	if ($user->email != $user_token['upn']) $user->email = $user_token['upn'];
    	if (!\Program\Lib\Request\Session::is_setUsername())
    		Program\Drivers\Driver::get_driver()->modifyUser($user);
    	\Program\Data\User::set_current_user($user);
    	s::setUsername($user_token['upn']);
    	s::setPassword('Office365');
    	s::setToken();
    }
    else {
    	$user = new \Program\Data\User(
    		[
    			"username" => $user_token['upn'],
    			"fullname" => $user_token['name'],
    			"email" => $user_token['upn'],
    			"last_login" => date("Y-m-d H:i:s"),
    			"language" => "fr_FR",
    			"auth" => 1,
    		]
    	);
    	// Création de l'utilisateur dans la base de données
    	$user_id = Program\Drivers\Driver::get_driver()->addUser($user);
    	if (!is_null($user_id)) {
    		// Si l'utilisateur est bien créé
    		$user = Program\Drivers\Driver::get_driver()->getUser($user_id);
    		if (isset($user)
    				&& isset($user->user_id)) {
    			\Program\Data\User::set_current_user($user);
    			s::setUsername($user_token['upn']);
    			s::setPassword('Office365');
    			s::setToken();
    		}
    	}
    }

    // Si une page de redirection est présente en session
    if (s::is_set('redirectUri')) {
    	$url = s::get('redirectUri');
    	s::un_set('redirectUri');
    }
    // Redirect back to the homepage.
    header("Location: ".$url);
    exit;
  }
  else {
    $msg = "Error retrieving access token: ".$tokens['error'];
    error_log($msg);
    header("Location: ".$errorPage."&errorMsg=".urlencode($msg));
  }
}

/*
 MIT License: 
 
 Permission is hereby granted, free of charge, to any person obtaining 
 a copy of this software and associated documentation files (the 
 ""Software""), to deal in the Software without restriction, including 
 without limitation the rights to use, copy, modify, merge, publish, 
 distribute, sublicense, and/or sell copies of the Software, and to 
 permit persons to whom the Software is furnished to do so, subject to 
 the following conditions: 
 
 The above copyright notice and this permission notice shall be 
 included in all copies or substantial portions of the Software. 
 
 THE SOFTWARE IS PROVIDED ""AS IS"", WITHOUT WARRANTY OF ANY KIND, 
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
?>