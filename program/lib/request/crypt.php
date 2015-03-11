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
namespace Program\Lib\Request;

/**
 * Classe de gestion du cryptage pour le mot de passe
 * Le mot de passe étant conservé en session, on le crypt/decrypt via une clé des
 * 
 * @package    Lib
 * @subpackage Request
 */
class Crypt {
    /**
     * Encrypt using 3DES
     *
     * @param string $clear clear text input
     * @param boolean $base64 whether or not to base64_encode() the result before returning
     *
     * @return string encrypted text
     */
    public static function encrypt($clear, $base64 = true) {
        if (!$clear) {
            return '';
        }
        
        /**
         * Generates encryption initialization vector (IV)
         *
         * @param int Vector size
         *
         * @return string Vector string
         */
        function create_iv($size)
        {
            // mcrypt_create_iv() can be slow when system lacks entrophy
            // we'll generate IV vector manually
            $iv = '';
            for ($i = 0; $i < $size; $i++) {
                $iv .= chr(mt_rand(0, 255));
            }
        
            return $iv;
        }
        
        if (function_exists('mcrypt_module_open') &&
                ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, ""))
        ) {
            /*-
             * Add a single canary byte to the end of the clear text, which
            * will help find out how much of padding will need to be removed
            * upon decryption; see http://php.net/mcrypt_generic#68082
            */
            $clear = pack("a*H2", $clear, "80");
            
            $iv = create_iv(mcrypt_enc_get_iv_size($td));
            mcrypt_generic_init($td, \Config\IHM::$DES_KEY, $iv);
            $cipher = $iv . mcrypt_generic($td, $clear);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        } else {
            $cipher = $clear;
        }
        
        return $base64 ? base64_encode($cipher) : $cipher;
    }
    /**
     * Decrypt 3DES-encrypted string
     *
     * @param string $cipher encrypted text
     * @param boolean $base64 whether or not input is base64-encoded
     *
     * @return string decrypted text
     */
    public static function decrypt($cipher, $base64 = true) {
        if (!$cipher) {
            return '';
        }
        
        $cipher = $base64 ? base64_decode($cipher) : $cipher;
        $clear = "";
        
        if (function_exists('mcrypt_module_open') &&
                ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, ""))
        ) {
            $iv_size = mcrypt_enc_get_iv_size($td);
            $iv = substr($cipher, 0, $iv_size);
        
            // session corruption? (#1485970)
            if (strlen($iv) < $iv_size) {
                return '';
            }
        
            $cipher = substr($cipher, $iv_size);
            mcrypt_generic_init($td, \Config\IHM::$DES_KEY, $iv);
            $clear = mdecrypt_generic($td, $cipher);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            
            /*-
             * Trim PHP's padding and the canary byte; see note in
            * rcube::encrypt() and http://php.net/mcrypt_generic#68082
            */
            $clear = substr(rtrim($clear, "\0"), 0, -1);
        }
        else {
            $clear = $cipher;
        }
        
        return $clear;
    }
}