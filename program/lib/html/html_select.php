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
 * Builder for HTML drop-down menus
 * Syntax:<pre>
 * // create instance. arguments are used to set attributes of select-tag
 * $select = new html_select(array('name' => 'fieldname'));
 *
 * // add one option
 * $select->add('Switzerland', 'CH');
 *
 * // add multiple options
 * $select->add(array('Switzerland','Germany'), array('CH','DE'));
 *
 * // generate pulldown with selection 'Switzerland'  and return html-code
 * // as second argument the same attributes available to instanciate can be used
 * print $select->show('CH');
 * </pre>
 *
 * @package    Framework
 * @subpackage View
 */
class html_select extends html
{
    protected $tagname = 'select';
    protected $options = array();
    protected $allowed = array('name','size','tabindex','autocomplete',
        'multiple','onchange','disabled','rel');

    /**
     * Add a new option to this drop-down
     *
     * @param mixed $names  Option name or array with option names
     * @param mixed $values Option value or array with option values
     * @param array $attrib Additional attributes for the option entry
     */
    public function add($names, $values = null, $attrib = array())
    {
        if (is_array($names)) {
            foreach ($names as $i => $text) {
                $this->options[] = array('text' => $text, 'value' => $values[$i]) + $attrib;
            }
        }
        else {
            $this->options[] = array('text' => $names, 'value' => $values) + $attrib;
        }
    }

    /**
     * Get HTML code for this object
     *
     * @param string $select Value of the selection option
     * @param array  $attrib Additional attributes to override
     * @return string HTML output
     */
    public function show($select = array(), $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $this->content = "\n";
        $select = (array)$select;
        foreach ($this->options as $option) {
            $attr = array(
                'value' => $option['value'],
                'selected' => (in_array($option['value'], $select, true) ||
                  in_array($option['text'], $select, true)) ? 1 : null);

            $option_content = $option['text'];
            if (empty($this->attrib['is_escaped'])) {
                $option_content = self::quote($option_content);
            }

            $this->content .= self::tag('option', $attr + $option, $option_content, array('value','label','class','style','title','disabled','selected'));
        }

        return parent::show();
    }
}
