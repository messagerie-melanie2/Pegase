<?php
/**
 * Classe pour la gestion des Timezone de Office 365
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
namespace Api\SSO\Office365;

class Timezone {
	/**
	 * Tableau de mapping entre les timezones Windows et les timezones PHP
	 * TODO: A remplir http://www.unicode.org/cldr/charts/latest/supplemental/zone_tzid.html
	 * @var array
	 */
	private static $mapping = [
			'UTC' => 'UTC',
			'AUS Central Standard Time' => 'Australia/Darwin',
			'AUS Eastern Standard Time' => 'Australia/Sydney',
			'Afghanistan Standard Time' => 'Asia/Kabul',
			'Alaskan Standard Time' => 'America/Anchorage',
			'Arab Standard Time' => 'Asia/Riyadh',
			'Arabian Standard Time' => 'Asia/Dubai',
			'Atlantic Standard Time' => 'America/Halifax',
			'Romance Standard Time' => 'Europe/Paris',
			'Russian Standard Time' => 'Europe/Moscow',
			'SA Eastern Standard Time' => 'America/Cayenne',
			'SA Western Standard Time' => 'America/La_Paz',
			'SE Asia Standard Time' => 'Asia/Bangkok',
			'W. Europe Standard Time' => 'Europe/Berlin',
	];
	/**
	 * Return the PHP value of the timezone from the Windows timezone
	 * @param string $windows_timezone
	 * @return string
	 */
	public static function GetFromMS($windows_timezone) {
		if (isset(self::$mapping[$windows_timezone])) {
			// Get mapping value
			return self::$mapping[$windows_timezone];
		}
		else {
			// Return default value
			return 'UTC';
		}
	}
	/**
	 * Return the Windows value of the timezone from the PHP timezone
	 * @param string $php_timezone
	 * @return string
	 */
	public static function GetFromPHP($php_timezone) {
		if (in_array($php_timezone, self::$mapping)) {
			// Get mapping value
			return array_search($php_timezone, self::$mapping);
		} 
		else {
			// Return default value
			return 'UTC';
		}
	}
}