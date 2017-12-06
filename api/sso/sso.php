<?php
/**
 * Classe abstraite pour la gestion des SSO dans l'application
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
namespace Api\SSO;

/**
 * Classe abstraite pour le sso
 * les sso doivent être implémentée à partir de cette classe
 */
abstract class SSO {
    /**
     * Instance du SSO
     * @var SSO
     */
    private static $sso;
    /**
     * URI utilisée pour l'authentification au SSO
     * @var string
     */
    protected static $SSO_URI;
    
    /**
     * Constructeur par défaut du SSO
     * A surcharger si besoin
     */
    public function __construct() {
    	self::$SSO_URI = \Config\IHM::$HOST;
    }
    /**
     * Récupère l'instance du driver à utiliser
     * @return SSO
     */
    public static function get_sso() {
        if (!isset(self::$sso)) {
            $sso_class = strtolower(\Config\IHM::$SSO_NAME);
            $sso_class = "\\API\\SSO\\$sso_class\\$sso_class";
            self::$sso = new $sso_class();
        }
        return self::$sso;
    }
    
    /**
     * Appel le traitement spécifique au SSO
     * @return boolean
     */
    abstract public function process();
    
    /**
     * Builds a login URL
     * @return string
     */
    abstract public function getLoginUrl();
    
    /**
     * Builds a logout URL
     * @return string
     */
    abstract public function getLogoutUrl();
}