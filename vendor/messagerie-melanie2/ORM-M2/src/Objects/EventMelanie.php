<?php
/**
 * Ce fichier est développé pour la gestion de la librairie Mélanie2
 * Cette Librairie permet d'accèder aux données sans avoir à implémenter de couche SQL
 * Des objets génériques vont permettre d'accèder et de mettre à jour les données
 *
 * ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace LibMelanie\Objects;

use LibMelanie\Lib\MagicObject;
use LibMelanie\Lib\HistoryMelanie;
use LibMelanie\Interfaces\IObjectMelanie;
use LibMelanie\Sql;
use LibMelanie\Config\ConfigMelanie;
use LibMelanie\Config\ConfigSQL;
use LibMelanie\Config\MappingMelanie;
use LibMelanie\Log\M2Log;


/**
 * Classe de gestion d'un évènement Melanie2
 * Penser à configurer le MappingMelanie pour les clés et le mapping
 *
 * @author PNE Messagerie/Apitech
 * @package Librairie Mélanie2
 * @subpackage ORM
 *
 */
class EventMelanie extends MagicObject implements IObjectMelanie {
	/**
	 * UID de l'organisateur de l'évènement
	 * @var string $organizer_uid
	 */
	public $organizer_uid;
	/**
	 * UID du calendrier où est organisé l'évènement
	 * @var string $organizer_uid
	 */
	public $organizer_calendar;
	/**
	 * Participants de l'évènement
	 * @var string $organizer_attendees
	 */
	public $organizer_attendees;

	/**
	 * Constructeur par défaut, appelé par PDO
	 */
	function __construct() {
	    // Défini la classe courante
	    $this->get_class = get_class($this);

		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->__construct()");
	    // Initialisation du backend SQL
		Sql\DBMelanie::Initialize(ConfigSQL::$CURRENT_BACKEND);

		// Récupération du type d'objet en fonction de la class
		$this->objectType = explode('\\',$this->get_class);
		$this->objectType = $this->objectType[count($this->objectType)-1];

		if (isset(MappingMelanie::$Primary_Keys[$this->objectType])) {
			if (is_array(MappingMelanie::$Primary_Keys[$this->objectType])) $this->primaryKeys = MappingMelanie::$Primary_Keys[$this->objectType];
			else $this->primaryKeys = [MappingMelanie::$Primary_Keys[$this->objectType]];
		}
	}

	/**
	 * Charge l'objet
	 * @return bool isExist
	 */
	function load() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load()");
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist)) {
		  return $this->isExist;
		}
		// Paramètres de la requête
		$params = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
			if (!isset($this->$key)) return false;
			// Récupèration des données de mapping
			if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
						&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
		}
		// Liste les calendriers de l'utilisateur
		$this->isExist = Sql\DBMelanie::ExecuteQueryToObject(Sql\SqlCalendarRequests::getEvent, $params, $this);
		if ($this->isExist) $this->initializeHasChanged();
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->load() isExist: ".$this->isExist);
		return $this->isExist;
	}

	/**
	 * Sauvegarde l'objet
	 * @return boolean True si c'est une command Insert, False si c'est un Update
	 */
	function save() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->save()");
		$insert = false;
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return null;

		// Ne rien sauvegarder si rien n'a changé
		$haschanged = false;
		foreach ($this->haschanged as $value) {
			$haschanged = $haschanged || $value;
			if ($haschanged) break;
		}
		if (!$haschanged) return null;
		// Si isExist est à null c'est qu'on n'a pas encore testé
		if (!is_bool($this->isExist)) {
		  $this->isExist = $this->exists();
		}
		// Si l'objet existe on fait un UPDATE
		if ($this->isExist) {
			// Modification
			if (!isset($this->haschanged[MappingMelanie::$Data_Mapping[$this->objectType]['modified'][MappingMelanie::name]])
					|| !$this->haschanged[MappingMelanie::$Data_Mapping[$this->objectType]['modified'][MappingMelanie::name]]) $this->modified = time();

			// Paramètres de la requête
			$params = [];
			// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) return null;
				// Récupèration des données de mapping
				if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
							&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
					$mapKey = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
				} else {
					$mapKey = $key;
				}
				$params[$mapKey] = $this->$key;
			}

			// Liste les modification à faire
			$update = "";
			foreach ($this->haschanged as $key => $value) {
				if ($value && !isset($params[$key])) {
					if ($update != "") $update .= ", ";
					$update .= "$key = :$key";
					$params[$key] = $this->$key;
				}
			}
			// Pas d'update
			if ($update == "") return null;

			// Replace
			$query = str_replace("{event_set}", $update, Sql\SqlCalendarRequests::updateEvent);

			// Execute
			$this->isExist = Sql\DBMelanie::ExecuteQuery($query, $params);
		} else {
			// C'est une Insertion
			$insert = true;
			// Test si les clés primaires sont bien instanciées
			foreach ($this->primaryKeys as $key) {
				if (!isset($this->$key)) return null;
			}
			// Gestion de l'event_id
			if (!isset($this->id)) $this->id = md5($this->uid . $this->calendar);
			if (!isset($this->modified)) $this->modified = time();

			// Si l'objet n'existe pas, on fait un INSERT
			// Liste les insertion à faire
			$data_fields = "";
			$data_values = "";
			$params = [];
			foreach ($this->haschanged as $key => $value) {
				if ($value) {
					if ($data_fields != "") $data_fields .= ", ";
					if ($data_values != "") $data_values .= ", ";
					$data_fields .= $key;
					$data_values .= ":".$key;
					$params[$key] = $this->$key;
				}
			}
			// Pas d'insert
			if ($data_fields == "") return null;

			// Replace
			$query = str_replace("{data_fields}", $data_fields, Sql\SqlCalendarRequests::insertEvent);
			$query = str_replace("{data_values}", $data_values, $query);

			// Execute
			$this->isExist = Sql\DBMelanie::ExecuteQuery($query, $params);
		}
		if ($this->isExist) $this->initializeHasChanged();
		return $insert;
	}

	/**
	 * Supprime l'objet
	 * @return boolean
	 */
	function delete() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->delete()");
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;

		// Paramètres de la requête
		$params = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
			if (!isset($this->$key)) return false;
			// Récupèration des données de mapping
			if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
						&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
		}

		// Supprimer l'évènement
		$ret = (Sql\DBMelanie::ExecuteQuery(Sql\SqlCalendarRequests::deleteEvent, $params) !== false);
		if ($ret) {
		  $this->initializeHasChanged();
		  $this->isExist = false;
		}
		return $ret;
	}

	/**
	 * Si l'objet existe
	 */
	function exists() {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->exists()");
		// Si les clés primaires ne sont pas définis, impossible de charger l'objet
		if (!isset($this->primaryKeys)) return false;
		// Test si l'objet existe, pas besoin de load
		if (is_bool($this->isExist)) {
		  return $this->isExist;
		}
		// Paramètres de la requête
		$params = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($this->primaryKeys as $key) {
			if (!isset($this->$key)) return false;
			M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->exists() $key: " . $this->$key);
			// Récupèration des données de mapping
			if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
						&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
				$mapKey = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
			} else {
				$mapKey = $key;
			}
			$params[$mapKey] = $this->$key;
		}
		// Liste les évènements
		$res = Sql\DBMelanie::ExecuteQuery(Sql\SqlCalendarRequests::getEvent, $params);
		$this->isExist = (count($res) >= 1);
		return $this->isExist;
	}

	/**
	 * Permet de récupérer la liste d'objet en utilisant les données passées
	 * (la clause where s'adapte aux données)
	 * Il faut donc peut être sauvegarder l'objet avant d'appeler cette méthode
	 * pour réinitialiser les données modifiées (propriété haschanged)
	 * @param String[] $fields Liste les champs à récupérer depuis les données
	 * @param String $filter Filtre pour la lecture des données en fonction des valeurs déjà passé, exemple de filtre : "((#description# OR #title#) AND #start#)"
	 * @param String[] $operators Liste les propriétés par operateur (MappingMelanie::like, MappingMelanie::supp, MappingMelanie::inf, MappingMelanie::diff)
	 * @param String $orderby Tri par le champ
	 * @param bool $asc Tri ascendant ou non
	 * @param int $limit Limite le nombre de résultat (utile pour la pagination)
	 * @param int $offset Offset de début pour les résultats (utile pour la pagination)
	 * @param String[] $case_unsensitive_fields Liste des champs pour lesquels on ne sera pas sensible à la casse
	 * @return EventMelanie[] Array
	 */
	function getList($fields = [], $filter = "", $operators = [], $orderby = "", $asc = true, $limit = null, $offset = null, $case_unsensitive_fields = []) {
		M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->getList()");
		// Mapping pour les operateurs
		$opmapping = [];
		// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
		foreach ($operators as $key => $operator) {
			// Récupèration des données de mapping
			if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
					&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
				$key = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
			}
			$opmapping[$key] = $operator;
		}
		// Mapping pour les champs
		$fieldsmapping = [];
		if (is_array($fields)) {
			// Test si les clés primaires sont bien instanciées et les ajoute en paramètres
			foreach ($fields as $key) {
				// Récupèration des données de mapping
				if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
						&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
					$key = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
				}
				$fieldsmapping[] = "k1.".$key;
			}
		}
		// Paramètres de la requête
		$whereClause = "";
		$params = [];
		// Est-ce qu'un filtre est activé
		if ($filter != "") {
			// Recherche toutes les entrées du filtre
			// TODO: Attention la regex ne prend que a-z ce qui correspond au mapping actuel
			preg_match_all("/#([a-z0-9]*)#/",
					strtolower($filter),
					$matches, PREG_PATTERN_ORDER);
			if (isset($matches[1])) {
				foreach ($matches[1] as $key) {
			    // Est-ce que le champ courant est non case sensitive
			    $is_case_unsensitive = in_array($key, $case_unsensitive_fields);
					// Récupèration des données de mapping
					if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
							&& isset(MappingMelanie::$Data_Mapping[$this->objectType][$key])) {
						$mapKey = MappingMelanie::$Data_Mapping[$this->objectType][$key][MappingMelanie::name];
					} else {
						$mapKey = $key;
					}
					if (isset($opmapping[$mapKey])) {
						if (is_array($this->$mapKey)) {
						    if ($opmapping[$mapKey] == MappingMelanie::in) {
						        // Filtre personnalisé, valeur multiple, pas de like, on utilise IN
						        if ($is_case_unsensitive)
							        $clause = "LOWER(k1.$mapKey) IN (";
						        else
						            $clause = "k1.$mapKey IN (";
						        $i = 1;
						        foreach ($this->$mapKey as $val) {
						            if ($i > 1) $clause .= ", ";
						            $clause .= ":$mapKey$i";
						            if ($is_case_unsensitive)
						                $params[$mapKey.$i] = strtolower($val);
						            else
						                $params[$mapKey.$i] = $val;
						            $i++;
						        }
						        $clause .= ")";
						        $filter = str_replace("#$key#", $clause, $filter);
						    } else {
						        // Filtre personnalisé, valeur multiple, avec like
						        $clause = "(";
						        $i = 1;
						        foreach ($this->$mapKey as $val) {
						            if ($i > 1) {
						                if ($opmapping[$mapKey] == MappingMelanie::diff) $clause .= " AND ";
						                else $clause .= " OR ";
						            }
						            if ($is_case_unsensitive) {
						                $clause .= "LOWER(k1.$mapKey) " . $opmapping[$mapKey] . " ";
						                $clause .= ":$mapKey$i";
						                $params[$mapKey.$i] = strtolower($val);
						            } else {
						                $clause .= "k1.$mapKey " . $opmapping[$mapKey] . " ";
						                $clause .= ":$mapKey$i";
						                $params[$mapKey.$i] = $val;
						            }

						            $i++;
						        }
						        $clause .= ")";
						        $filter = str_replace("#$key#", $clause, $filter);
						    }
						} else {
							// Filtre personnalisé, valeur simple avec LIKE
						    if ($is_case_unsensitive) {
						        $clause = "LOWER(k1.$mapKey) " . $opmapping[$mapKey] . " :$mapKey";
						        $params[$mapKey] = strtolower($this->$mapKey);
						    } else {
						        $clause = "k1.$mapKey " . $opmapping[$mapKey] . " :$mapKey";
						        $params[$mapKey] = $this->$mapKey;
						    }
							$filter = str_replace("#$key#", $clause, $filter);
						}
					} else {
					    // Filtre personnalise, on ne met que le nom du champ
					    if ($is_case_unsensitive)
					    	$clause = "LOWER(k1.$mapKey)";
					    else
					        $clause = "k1.$mapKey";
					    $filter = str_replace("#$key#", $clause, $filter);
					}
				}
			}
			$whereClause = $filter;
		} else {
			// Gestion du where clause en fonction du haschanged
			// N'ajoute que les paramètres qui ont changé
			foreach ($this->haschanged as $key => $value) {
				if ($value) {
				    // Est-ce que le champ courant est non case sensitive
				    $is_case_unsensitive = in_array($key, $case_unsensitive_fields);
					if (isset($opmapping[$key])) {
						if (is_array($this->$key)) {
							// On est dans un tableau et il nous faut utiliser LIKE
							if ($whereClause != "") $whereClause .= " AND ";
							$i = 1;
							foreach ($this->$key as $val) {
								if ($i > 1) {
									if ($opmapping[$key] == MappingMelanie::diff) $whereClause .= " AND ";
									else $whereClause .= " OR ";
								} else $whereClause .= "(";
								if ($is_case_unsensitive) {
								    $whereClause .= "LOWER(k1.$key) " . $opmapping[$key] . " ";
								    $whereClause .= ":$key$i";
								    $params[$key.$i] = strtolower($val);
								} else {
								    $whereClause .= "k1.$key " . $opmapping[$key] . " ";
								    $whereClause .= ":$key$i";
								    $params[$key.$i] = $val;
								}
								$i++;
							}
							$whereClause .= ")";
						} else {
							// Valeur simple avec LIKE
							if ($whereClause != "") $whereClause .= " AND ";
							if ($is_case_unsensitive) {
							    $whereClause .= "LOWER(k1.$key) " . $opmapping[$key] . " :$key";
							    $params[$key] = strtolower($this->$key);
							} else {
							    $whereClause .= "k1.$key " . $opmapping[$key] . " :$key";
							    $params[$key] = $this->$key;
							}
						}
					} else {
						if (is_array($this->$key)) {
							// On est dans un tableau, pas de like, on utilise IN
							if ($whereClause != "") $whereClause .= " AND ";
							if ($is_case_unsensitive)
							    $whereClause .= "LOWER(k1.$key) IN (";
							else
								$whereClause .= "k1.$key IN (";
							$i = 1;
							foreach ($this->$key as $val) {
								if ($i > 1) $whereClause .= ", ";
								$whereClause .= ":$key$i";
								if ($is_case_unsensitive)
									$params[$key.$i] = strtolower($val);
								else
								    $params[$key.$i] = $val;
								$i++;
							}
							$whereClause .= ")";
						} else {
							// Valeur simple, pas de like, on utilise l'égalité
							if ($whereClause != "") $whereClause .= " AND ";
							if ($is_case_unsensitive) {
							    $whereClause .= "LOWER(k1.$key) = :$key";
							    $params[$key] = strtolower($this->$key);
							} else {
							    $whereClause .= "k1.$key = :$key";
							    $params[$key] = $this->$key;
							}
						}
					}
				}
			}
		}
		// Tri
		if (!empty($orderby)) {
		    // Récupèration des données de mapping
		    if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
		            && isset(MappingMelanie::$Data_Mapping[$this->objectType][$orderby])) {
		        $orderby = MappingMelanie::$Data_Mapping[$this->objectType][$orderby][MappingMelanie::name];
		    }
		    $whereClause .= " ORDER BY k1.$orderby" . ($asc ? " ASC" : " DESC");
		}
		// Limit
		if (isset($limit)) {
		    $whereClause .= " LIMIT $limit";
		}
		// Offset
		if (isset($offset)) {
		    $whereClause .= " OFFSET $offset";
		}
		// Chargement de la requête
		$query = Sql\SqlCalendarRequests::getListEvents;
		// Liste des champs
		if (!is_array($fields) && strtolower($fields) == 'count') {
		    // On fait un count(*)
		    $query = Sql\SqlCalendarRequests::getCountEvents;
		} elseif (count($fieldsmapping) > 0) {
			$query = str_replace("{fields_list}", implode(", ", $fieldsmapping), $query);
		} else {
			$query = str_replace("{fields_list}", "k1.*", $query);
		}
		// Clause where
		$query = str_replace("{where_clause}", $whereClause, $query);

		// Récupération
		return Sql\DBMelanie::ExecuteQuery($query, $params, $this->get_class);
	}

	/**
	 * Mise à jour de l'évènement pour les participants de la réunion (ainsi que l'organisateur)
	 * Le but est de mettre à jour le etag associé à l'évènement pour que la prochaine actualisation d'agenda
	 * puisse mettre à jour leur statut
	 * @return boolean True si OK, False sinon
	 */
	function updateMeetingEtag() {
		// Si l'uid de l'évènement n'est pas défini, on ne peut pas faire l'update
		if (!isset($this->uid)) return false;

		// Paramètres de la requête
		$params = [];

		// Récupèration des données de mapping
		if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
				&& isset(MappingMelanie::$Data_Mapping[$this->objectType]['uid'])) {
			$mapKey = MappingMelanie::$Data_Mapping[$this->objectType]['uid'][MappingMelanie::name];
		} else {
			$mapKey = 'uid';
		}
		$params[$mapKey] = $this->uid;

		if (isset(MappingMelanie::$Data_Mapping[$this->objectType])
				&& isset(MappingMelanie::$Data_Mapping[$this->objectType]['modified'])) {
			$mapKey = MappingMelanie::$Data_Mapping[$this->objectType]['modified'][MappingMelanie::name];
		} else {
			$mapKey = 'modified';
		}
		$params[$mapKey] = time();

		// Execute
		return Sql\DBMelanie::ExecuteQuery(Sql\SqlCalendarRequests::updateMeetingEtag, $params);
	}

	/**
	 * Fonction appelé après la génération de l'objet par PDO
	 * Cette fonction est normalement auto appelée par le getList
	 * Elle permet de définir les bon paramètres de l'objet
	 * L'appel externe n'est donc pas nécessaire (mais cette méthode doit rester public)
	 * @param bool $isExist si l'objet existe
	 */
	function pdoConstruct($isExist) {
		//M2Log::Log(M2Log::LEVEL_DEBUG, $this->get_class."->pdoConstruct($isExist)");
		$this->initializeHasChanged();
		$this->isExist = $isExist;
	}
}