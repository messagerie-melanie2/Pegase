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
 * Classe de gestion de la requête HTTP pour l'application de sondage
 *
 * @package    Lib
 * @subpackage Request
 */
class Request {
	/**
	 *  Constructeur privé pour ne pas instancier la classe
	 */
	private function __construct() { }

	/**
	 * Retourne l'url courante de l'application
	 * @return string
	 */
	public static function getCurrentURL() {
		if (isset(\Config\IHM::$HOST)) {
			  return \Config\IHM::$HOST;
		} else {
		    $s = $_SERVER;
		    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
		    $sp = strtolower($s['SERVER_PROTOCOL']);
		    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		    $port = $s['SERVER_PORT'];
		    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
		    return $protocol . '://' . $host . $port . $s['REQUEST_URI'];
		}
	}
	/**
	 * Récupération du titre de la page courante
	 * @return string
	 */
	public static function getTitle() {
	    if (Output::isset_env("page")) {
	        $title = Localization::g("title ".Output::get_env("page"));
	    } else {
	        $title = Localization::g("title main");
	    }
	    return \Config\IHM::$TITLE . ' :: ' . $title;
	}
	/**
	 * Read input value and convert it for internal use
	 * Performs stripslashes() and charset conversion if necessary
	 *
	 * @param  string   Field name to read
	 * @param  int      Source to get value from (GPC)
	 * @param  boolean  Allow HTML tags in field value
	 * @param  string   Charset to convert into
	 * @return string   Field value or NULL if not available
	 */
	public static function getInputValue($fname, $source, $allow_html=FALSE, $charset=NULL)
	{
	    $value = NULL;

	    if ($source == POLL_INPUT_GET) {
	        if (isset($_GET[$fname]))
	            $value = $_GET[$fname];
	    }
	    else if ($source == POLL_INPUT_POST) {
	        if (isset($_POST[$fname]))
	            $value = $_POST[$fname];
	    }
	    else if ($source == POLL_INPUT_GPC) {
	        if (isset($_POST[$fname]))
	            $value = $_POST[$fname];
	        else if (isset($_GET[$fname]))
	            $value = $_GET[$fname];
	        else if (isset($_COOKIE[$fname]))
	            $value = $_COOKIE[$fname];
	    }

	    return self::parseInputValue($value, $allow_html, $charset);
	}

	/**
	 * Parse/validate input value. See get_input_value()
	 * Performs stripslashes() and charset conversion if necessary
	 *
	 * @param  string   Input value
	 * @param  boolean  Allow HTML tags in field value
	 * @return string   Parsed value
	 */
	private static function parseInputValue($value, $allow_html=FALSE)
	{

	    if (empty($value))
	        return $value;

	    if (is_array($value)) {
	        foreach ($value as $idx => $val)
	            $value[$idx] = self::parseInputValue($val, $allow_html);
	        return $value;
	    }

	    // strip single quotes if magic_quotes_sybase is enabled
	    if (ini_get('magic_quotes_sybase'))
	        $value = str_replace("''", "'", $value);
	    // strip slashes if magic_quotes enabled
	    else if (get_magic_quotes_gpc() || get_magic_quotes_runtime())
	        $value = stripslashes($value);

	    // remove HTML tags if not allowed
	    if (!$allow_html)
	        $value = strip_tags($value);


	    return $value;
	}
	/**
	 * Retourne si on demande une page mobile ou non
	 * @return boolean
	 */
	public static function isMobile() {
	    $useragent = $_SERVER['HTTP_USER_AGENT'];
	    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
	}
	/**
	 * Retourne si on demande une page depuis le courrielleur
	 * @return boolean
	 */
	public static function isCourrielleur() {
	    $useragent = $_SERVER['HTTP_USER_AGENT'];
	    return preg_match('/(Thunderbird|Lightning)/i', $useragent);
	}

	/**
	 * Retourne l'adresse ip
	 * @return string
	 */
	public static function get_ip_address() {
	  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	    $ip = $_SERVER['HTTP_CLIENT_IP'];
	  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	  } else {
	    $ip = $_SERVER['REMOTE_ADDR'];
	  }
	  return $ip;
	}
}