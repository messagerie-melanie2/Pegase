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
namespace Program\Data;

/**
 * Objet magic pour les getter et setter en fonction des requêtes SQL
 * 
 * @package Data
 */
abstract class Object {
	/**
	 * Stockage des données cachées
	 * @var array
	 */
	protected $data = array();
	/**
	 * Défini si les propriété ont changé pour les requêtes SQL
	 * @var array
	 */
	protected $haschanged = array();
	
	/**
	 * Remet à 0 le haschanged
	 */
	public function __initialize_haschanged() {
		foreach (array_keys($this->haschanged) as $key) $this->haschanged[$key] = false;
	}
	
	/**
	 * Return data array
	 * @return array:
	 */
	public function __get_data() {
	    return $this->data;
	}
	
	/**
	 * Return haschanged array
	 * @return array:
	 */
	public function __get_haschanged() {
	    return $this->haschanged;
	}
	
	/**
	 * Copy l'objet depuis un autre
	 * @param Object $object
	 * @return boolean
	 */
	public function __copy_from($object) {
	    if (method_exists($object, "__get_data")) {
	        $this->data = $object->__get_data();
	        return true;
	    }
	    return false;
	}
	
	/**
	 * PHP magic to set an instance variable
	 *
	 * @access public
	 * @return
	 * @ignore
	*/
	public function __set($name, $value) {
		$lname = strtolower($name);
		
		if (method_exists($this, "__set_$lname")) {
		    return $this->{"__set_$lname"}($value);
		}

		if (isset($this->data[$lname]) && is_scalar($value) && !is_array($value) && $this->data[$lname] === $value)
			return false;
	
		$this->data[$lname] = $value;
		$this->haschanged[$lname] = true;
	}
	
	/**
	 * PHP magic to get an instance variable
	 * if the variable was not set previousely, the value of the
	 * Unsetdata array is returned
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __get($name) {
		$lname = strtolower($name);
		if (method_exists($this, "__get_$lname")) {
		    return $this->{"__get_$lname"}();
		}
		if (isset($this->data[$lname])) return $this->data[$lname];
		return null;
	}

	/**
	 * PHP magic to check if an instance variable is set
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __isset($name) {
		$lname = strtolower($name);
		return isset($this->data[$lname]);;
	}

	/**
	 * PHP magic to remove an instance variable
	 *
	 * @access public
	 * @return
	 * @ignore
	 */
	public function __unset($name) {
		$lname = strtolower($name);
		
		if (isset($this->data[$lname])) {
			unset($this->data[$lname]);
		}
	}

	/**
	 * PHP magic to implement any getter, setter, has and delete operations
	 * on an instance variable.
	 * Methods like e.g. "SetVariableName($x)" and "GetVariableName()" are supported
	 *
	 * @access public
	 * @return mixed
	 * @ignore
	 */
	public function __call($name, $arguments) {
		$name = strtolower($name);
		$operator = substr($name, 0,3);
		$var = substr($name,3);

		if ($operator == "set" && count($arguments) == 1){
			$this->$var = $arguments[0];
			return true;
		}

		if ($operator == "set" && count($arguments) == 2 && $arguments[1] === false){
			$this->data[$var] = $arguments[0];
			return true;
		}

		// getter without argument = return variable, null if not set
		if ($operator == "get" && count($arguments) == 0) {
			return $this->$var;
		}
		// getter with one argument = return variable if set, else the argument
		else if ($operator == "get" && count($arguments) == 1) {
			if (isset($this->$var)) {
				return $this->$var;
			}
			else
				return $arguments[0];
		}

		if ($operator == "has" && count($arguments) == 0)
			return isset($this->$var);

		if ($operator == "del" && count($arguments) == 0) {
			unset($this->$var);
			return true;
		}
	}
}
