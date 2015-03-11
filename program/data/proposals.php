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
 * Définition des propositions pour un sondage
 * 
 * @package Data
 */
class Proposals {
    /**
     * Liste des propositions du sondage
     * @var array
     */
    public $proposals = array();

    /******* METHODES *******/
    /**
     * Constructeur par défaut de la classe Proposals
     * @param string|array $proposals
     */
    public function __construct($proposals) {
        if (isset($proposals)) {
            $this->set($proposals);
        }
    }
    /**
     * Ajout d'une proposition pour le sondage
     * @param string $value Valeur de proposition à ajouter
     */
    public function add($value) {
        if (!in_array($value, $this->proposals))
            $this->proposals[] = $value;
    }
    /**
     * Modification d'une proposition pour le sondage
     * @param string $oldvalue Ancienne valeur de proposition
     * @param string $newvalue Nouvelle valeur de proposition
     */
    public function modify($oldvalue, $newvalue) {
        $this->delete($oldvalue);
        $this->add($newvalue);
    }
    /**
     * Suppression d'une proposition pour le sondage
     * @param string $value Valeur de proposition à supprimer
     */    
    public function delete($value) {
        $key = array_search($value, $this->proposals);
        if ($key !== false)
            unset($this->proposals[$key]);
    }
    /**
     * Récupération des données sérialisées
     * @return string
     */
    public function get() {
        return serialize($this->proposals);
    }
    /**
     * Définition des données
     * @param string|array $proposals
     */
    public function set($proposals) {
        if (is_array($proposals)) {
            $this->proposals = $proposals;
        } else {
            $this->proposals = unserialize($proposals);
            if ($this->proposals === null)
                $this->proposals = array();
        }
    }
}