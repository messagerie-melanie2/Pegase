<?php
/**
 * Programme de sondage
 *
 * Gestion du SSO CAS avec authentification unique
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

// Récupération de l'url
$url = Config\IHM::$HOST;

// Page d'erreur
$errorPage = $url."?_p=error"; 

// // Enable debugging
// phpCAS::setDebug();
// // Enable verbose error messages. Disable in production!
// phpCAS::setVerbose(true);
// Initialize phpCAS
phpCAS::client(SAML_VERSION_1_1, Api\SSO\Cerbere\Config::$HOST, Api\SSO\Cerbere\Config::$PORT, Api\SSO\Cerbere\Config::$CONTEXT);
// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
phpCAS::setCasServerCACert(Api\SSO\Cerbere\Config::$CA_CERT);
// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
// phpCAS::setNoCasServerValidation();
// Handle SAML logout requests that emanate from the CAS host exclusively.
// Failure to restrict SAML logout requests to authorized hosts could
// allow denial of service attacks where at the least the server is
// tied up parsing bogus XML messages.
//phpCAS::handleLogoutRequests(true, $cas_real_hosts);

// Forcer l'URL du service client
if (isset($_GET['logout'])) {
  phpCAS::setFixedServiceURL($url . Api\SSO\Cerbere\Cerbere::SSO_URL . '?logout');
}
elseif (isset($_GET['uri'])) {
  phpCAS::setFixedServiceURL($url . Api\SSO\Cerbere\Cerbere::SSO_URL . '?uri=' . $_GET['uri']);
}
elseif (isset($_GET['poll'])) {
  phpCAS::setFixedServiceURL($url . Api\SSO\Cerbere\Cerbere::SSO_URL . '?poll=' . $_GET['poll']);
}
else {
  phpCAS::setFixedServiceURL($url . Api\SSO\Cerbere\Cerbere::SSO_URL);
}

// Force CAS authentication on any page that includes this file
phpCAS::forceAuthentication();
// Some small code triggered by the logout button
if (isset($_GET['logout'])) {
  phpCAS::logout();
}

$userCas = phpCAS::getUser();

if (isset($userCas)) {
  $userAttributes = phpCAS::getAttributes();
  $user = Program\Drivers\Driver::get_driver()->getAuthUser($userCas);

  if (isset($user)
      && isset($user->user_id)) {
    $user->last_login = date("Y-m-d H:i:s");
    $fullname = $userAttributes['UTILISATEUR.NOM'] . ' ' . $userAttributes['UTILISATEUR.PRENOM'];
    if (isset($userAttributes['UTILISATEUR.LDAP_DN'])) {
      $fullname .= ' - ' . $userAttributes['UTILISATEUR.LDAP_DN'];
    }
    if ($user->fullname != $fullname) $user->fullname = $fullname;
    if ($user->email != $userAttributes['UTILISATEUR.MEL']) $user->email = $userAttributes['UTILISATEUR.MEL'];
    if (!\Program\Lib\Request\Session::is_setUsername())
      Program\Drivers\Driver::get_driver()->modifyUser($user);
    \Program\Data\User::set_current_user($user);
    s::setPassword('CAS');
    s::setToken();
  }
  else {
    // Essaye de récupérer le user par email (pour ceux ayant un compte M2)
    $user = Program\Drivers\Driver::get_driver()->getAuthUserByEmail($userAttributes['UTILISATEUR.MEL']);
    
    if (isset($user)
        && isset($user->user_id)) {
      $user->last_login = date("Y-m-d H:i:s");

      if (!\Program\Lib\Request\Session::is_setUsername())
        Program\Drivers\Driver::get_driver()->modifyUser($user);
      \Program\Data\User::set_current_user($user);
      s::setPassword('CAS');
      s::setToken();
    }
    else {
      $fullname = $userAttributes['UTILISATEUR.NOM'] . ' ' . $userAttributes['UTILISATEUR.PRENOM'];
      if (isset($userAttributes['UTILISATEUR.LDAP_DN'])) {
        $fullname .= ' - ' . $userAttributes['UTILISATEUR.LDAP_DN'];
      }
      $user = new \Program\Data\User(
          [
              "username" => $userCas,
              "fullname" => $fullname,
              "email" => $userAttributes['UTILISATEUR.MEL'],
              "last_login" => date("Y-m-d H:i:s"),
              "language" => "fr_FR",
              "auth" => 1,
          ]
      );
      $user->is_cerbere = true;
      $mail = explode('@', $userAttributes['UTILISATEUR.MEL'], 2);
      if (isset(Api\SSO\Cerbere\Config::$FREEBUSY_URL_MAPPING[$mail[1]])) {
        $user->freebusy_url = str_replace('%%UTILISATEUR.MEL%%', urlencode($userAttributes['UTILISATEUR.MEL']), Api\SSO\Cerbere\Config::$FREEBUSY_URL_MAPPING[$mail[1]]);
      }      
      // Création de l'utilisateur dans la base de données
      $user_id = Program\Drivers\Driver::get_driver()->addUser($user);
      if (!is_null($user_id)) {
        // Si l'utilisateur est bien créé
        $user = Program\Drivers\Driver::get_driver()->getUser($user_id);
        if (isset($user)
            && isset($user->user_id)) {
              \Program\Data\User::set_current_user($user);
              s::setPassword('CAS');
              s::setToken();
            }
      }
    }
  }
  
  // Positionne le SSO pour l'auth
  s::set('SSO', 'Cerbere');
  
  if (isset($_GET['uri'])) {
    $url = urldecode(r::getInputValue('uri', POLL_INPUT_GET));
  }
  elseif (isset($_GET['poll'])) {
    $url = \Config\IHM::$HOST . o::url(null, null, ['u' => r::getInputValue('poll', POLL_INPUT_GET)]);
  }
}
else {
  $msg = "Error retrieving user CAS";
  error_log($msg);
  header("Location: ".$errorPage."&errorMsg=".urlencode($msg));
}

// Redirect back to the homepage.
header("Location: ".$url);
exit;