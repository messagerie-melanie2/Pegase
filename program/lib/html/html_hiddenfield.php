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
/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Helper class to create valid XHTML code                             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/
namespace Program\Lib\HTML;

/**
 * Class to create an hidden HTML input field
 *
 * @package    Framework
 * @subpackage View
 */
class html_hiddenfield extends html
{
    protected $tagname = 'input';
    protected $type = 'hidden';
    protected $fields_arr = array();
    protected $allowed = array('type','name','value','onchange','disabled','readonly');

    /**
     * Constructor
     *
     * @param array $attrib Named tag attributes
     */
    public function __construct($attrib = null)
    {
        if (is_array($attrib)) {
            $this->add($attrib);
        }
    }

    /**
     * Add a hidden field to this instance
     *
     * @param array $attrib Named tag attributes
     */
    public function add($attrib)
    {
        $this->fields_arr[] = $attrib;
    }

    /**
     * Create HTML code for the hidden fields
     *
     * @return string Final HTML code
     */
    public function show()
    {
        $out = '';
        foreach ($this->fields_arr as $attrib) {
            $out .= self::tag($this->tagname, array('type' => $this->type) + $attrib);
        }
        return $out;
    }
}
