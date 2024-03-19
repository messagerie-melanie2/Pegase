<?php
/**
 * Template pour la page de login de l'application de sondage
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
// Utilisation des namespaces
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Localization as l;
use Program\Lib\Request\Template as t;
use Program\Lib\Request\Request as r;
use Program\Lib\Request\Session as s;
use GuzzleHttp\Client;

$api = \Config\IHM::$API_PWD['url'] . 'v1/';
$portail_token = isset($_REQUEST['_portail_token']) ? $_REQUEST['_portail_token'] : null;
if(is_null($portail_token)){
  $portail = \Config\IHM::$PORTAIL_UTILISATEUR_URL . '?redirect_uri=' . rawurlencode(r::getCurrentURL() . '?_p=login');
  header('Location: ' . $portail);
  die();
}

$client = new Client([
  'base_uri' => $api,
  'verify' => false,
]);

try{
  $auth = $client->post('auth/client', [
    'auth' => [\Config\IHM::$API_PWD['client_id'],  \Config\IHM::$API_PWD['client_secret']],
  ]);
  $client_token = \GuzzleHttp\json_decode($auth->getBody())->token;
  $portail = $client->post('portail/token', [
    'headers' => [
      'Authorization' => 'Bearer ' . $client_token,
      'Accept' => 'application/json',
    ],
    'form_params' => [
        'portail_token' => $portail_token,
    ],
  ]);
  $user_token = \GuzzleHttp\json_decode($portail->getBody())->token;
  list($head, $payload, $sign) = explode('.', $user_token);
  $payload = json_decode(base64_decode($payload));
  $user_id = $payload->uid;
  $user_pwd = $payload->auth_token;
}catch(\Throwable $t){
  $portail = \Config\IHM::$PORTAIL_UTILISATEUR_URL . '?redirect_uri=' . rawurlencode(r::getCurrentURL() . o::url("login"));
  header('Location: ' . $portail);
  die();
}


?>
<html>
<body>
    		<form action="<?= o::url("login") ?>" method="post" class="pure-form pure-form-aligned" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= s::getCSRFToken() ?>"/>
          <input id="username" type="hidden" name="username" value="<?php echo $user_id; ?>"/>
          <input id="password" type="hidden" name="password" value="<?php echo $user_pwd; ?>"/>
    		</form>
<script src='skins/<?= o::get_env('skin') ?>/js/autologin.js'></script>
</body>
</html>
