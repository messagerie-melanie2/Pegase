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
 * Class to create an HTML input field
 *
 * @package    Lib
 * @subpackage HTML
 */
class html_inputfield extends html
{
    protected $tagname = 'input';
    protected $type = 'text';
    protected $allowed = array(
        'type','name','value','size','tabindex','autocapitalize','required',
        'autocomplete','checked','onchange','onclick','disabled','readonly',
        'spellcheck','results','maxlength','src','multiple','accept',
        'placeholder','autofocus',
    );

    /**
     * Object constructor
     *
     * @param array $attrib Associative array with tag attributes
     */
    public function __construct($attrib = array())
    {
        if (is_array($attrib)) {
            $this->attrib = $attrib;
        }

        if (isset($attrib['type']) 
                && $attrib['type']) {
            $this->type = $attrib['type'];
        }
    }

    /**
     * Compose input tag
     *
     * @param string $value Field value
     * @param array  $attrib Additional attributes to override
     * @return string HTML output
     */
    public function show($value = null, $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set value attribute
        if ($value !== null) {
            $this->attrib['value'] = $value;
        }
        // set type
        $this->attrib['type'] = $this->type;
        return parent::show();
    }
}
