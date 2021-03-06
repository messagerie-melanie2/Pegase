<?php
/**
 * Programme de sondage du MEDDE/METL
 *
 * Permet de générer des sondages par les utilisateurs via une page web
 * La génération se fait authentifiée
 * N'importe quel utilisateur peut ensuite répondre au sondage
 *
 * @author Thomas Payen
 * @author PNE Annuaire et Messagerie
 * @version 1.1-1606031654
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
use Program\Lib\Request\Template as t;
use Program\Lib\Request\Request as r;
use Program\Lib\Request\Output as o;
use Program\Lib\Request\Localization as l;

// Inclusion
include 'program/include/includes.php';

// Initialisation de l'output
o::init();

// Définition de la page par défaut
if (!o::isset_env("page")) {
    if (o::isset_env("poll_uid")) o::set_env("page", "show");
    else o::set_env("page", "main");
}
// Traitement particulier pour le login et le logout
if (o::get_env("page") != "login"
		&& o::get_env("page") != "external_login"
        && o::get_env("page") != "register"
        && o::get_env("page") != "show"
        && o::get_env("page") != "ajax") {
    if (!Program\Lib\Request\Session::validateSession()) {
    	// Utilisation du SSO
    	if (isset(Config\IHM::$USE_SSO)
    	        && Config\IHM::$USE_SSO) {
    		Api\SSO\SSO::get_sso()->process();
    	}
    	else {
    		if (isset(Config\IHM::$LOGIN_URL)) {
    			// Redirection vers la connexion
    			header('Location: ' . \Config\IHM::$LOGIN_URL);
    			exit();
    		} else {
    			o::set_env("page", "login");
    			if (Program\Lib\Request\Session::is_set("user_id"))
    				o::set_env("error", "Auth error, please re-login");
    		}
    	}
    } else {
        if (Program\Data\Poll::isset_current_poll()
                && Program\Data\User::isset_current_user()
                && Program\Data\Poll::get_current_poll()->organizer_id != Program\Data\User::get_current_user()->user_id) {
            o::set_env("page", "error");
            o::set_env("error", "You have no right to access to this resource");
        } else {
            $class = "Program\\Lib\\Templates\\" . ucfirst(o::get_env("page"));
            if (method_exists($class, "Process")) {
                call_user_func_array("$class::Process", array());
            }
        }
    }
} else {
    if (o::get_env("page") == "login") {
        if (Program\Lib\Templates\Login::Process()) {
            o::set_env("page", "main");
        } elseif (isset($_POST['username'])) {
            o::set_env("error", "Auth error, bad login or password");
        }
    } elseif(o::get_env("page") == "register") {
    	if (Program\Lib\Templates\Register::Process()) {
    		o::set_env("page", "main");
    	}
    } elseif(o::get_env("page") == "external_login") {
    	if (!Program\Lib\Templates\External_Login::Process()
                && isset($_POST['username'])) {
    		o::set_env("error", "Auth error, bad login or password");
    	}
    } elseif (o::get_env("page") == "show") {
    	if (!Program\Lib\Request\Session::validateSession() && r::isCourrielleur()) {
          if (isset(Config\IHM::$LOGIN_URL)) {
            // Redirection vers la connexion
            header('Location: ' . \Config\IHM::$LOGIN_URL);
            exit();
          }
        }
        Program\Lib\Templates\Show::Process();
    } elseif (o::get_env("page") == "ajax") {
        Program\Lib\Request\Session::validateSession();
        Program\Lib\Templates\Ajax::Process();
    }
}
// Chargement de la page
if (o::isset_env("page")
        && o::get_env("page") != 'ajax') {
    t::load(o::get_env("page"));
}

// Envoi de la page
o::send();