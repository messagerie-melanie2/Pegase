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
 * Class for HTML code creation
 *
 * @package    Lib
 * @subpackage HTML
 */
class html
{
    protected $tagname;
    protected $attrib = array();
    protected $allowed = array();
    protected $content;

    public static $doctype = 'xhtml';
    public static $lc_tags = true;
    public static $common_attrib = array('id','class','style','title','align','unselectable');
    public static $containers = array('iframe','div','span','p','h1','h2','h3','ul','form','textarea','table','thead','tbody','tr','th','td','style','script');


    /**
     * Constructor
     *
     * @param array $attrib Hash array with tag attributes
     */
    public function __construct($attrib = array())
    {
        if (is_array($attrib)) {
            $this->attrib = $attrib;
        }
    }

    /**
     * Return the tag code
     *
     * @return string The finally composed HTML tag
     */
    public function show()
    {
        return self::tag($this->tagname, $this->attrib, $this->content, array_merge(self::$common_attrib, $this->allowed));
    }

    /****** STATIC METHODS *******/

    /**
     * Generic method to create a HTML tag
     *
     * @param string $tagname Tag name
     * @param array  $attrib  Tag attributes as key/value pairs
     * @param string $content Optinal Tag content (creates a container tag)
     * @param array  $allowed_attrib List with allowed attributes, omit to allow all
     * @return string The XHTML tag
     */
    public static function tag($tagname, $attrib = array(), $content = null, $allowed_attrib = null)
    {
        if (is_string($attrib))
            $attrib = array('class' => $attrib);

        $inline_tags = array('a','span','img');
        $suffix = isset($attrib['nl']) && $attrib['nl'] || ($content && isset($attrib['nl']) && $attrib['nl'] !== false && !in_array($tagname, $inline_tags)) ? "\n" : '';

        $tagname = self::$lc_tags ? strtolower($tagname) : $tagname;
        if (isset($content) || in_array($tagname, self::$containers)) {
            $suffix = isset($attrib['noclose']) && $attrib['noclose'] ? $suffix : '</' . $tagname . '>' . $suffix;
            unset($attrib['noclose'], $attrib['nl']);
            return '<' . $tagname  . self::attrib_string($attrib, $allowed_attrib) . '>' . $content . $suffix;
        }
        else {
            return '<' . $tagname  . self::attrib_string($attrib, $allowed_attrib) . '>' . $suffix;
        }
    }

    /**
     *
     */
    public static function doctype($type)
    {
        $doctypes = array(
            'html5'        => '<!DOCTYPE html>',
            'xhtml'        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml-trans'  => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        );

        if ($doctypes[$type]) {
            self::$doctype = preg_replace('/-\w+$/', '', $type);
            return $doctypes[$type];
        }

        return '';
    }

    /**
     * Derrived method for <div> containers
     *
     * @param mixed  $attr Hash array with tag attributes or string with class name
     * @param string $cont Div content
     * @return string HTML code
     * @see html::tag()
     */
    public static function div($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('div', $attr, $cont, array_merge(self::$common_attrib, array('onclick')));
    }

    /**
     * Derrived method for <p> blocks
     *
     * @param mixed  $attr Hash array with tag attributes or string with class name
     * @param string $cont Paragraph content
     * @return string HTML code
     * @see html::tag()
     */
    public static function p($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('p', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derrived method to create <img />
     *
     * @param mixed $attr Hash array with tag attributes or string with image source (src)
     * @return string HTML code
     * @see html::tag()
     */
    public static function img($attr = null)
    {
        if (is_string($attr)) {
            $attr = array('src' => $attr);
        }
        return self::tag('img', $attr + array('alt' => ''), null, array_merge(self::$common_attrib,
            array('src','alt','width','height','border','usemap','onclick')));
    }

    /**
     * Derrived method for link tags
     *
     * @param mixed  $attr Hash array with tag attributes or string with link location (href)
     * @param string $cont Link content
     * @return string HTML code
     * @see html::tag()
     */
    public static function a($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('href' => $attr);
        }
        return self::tag('a', $attr, $cont, array_merge(self::$common_attrib,
            array('href','target','name','rel','onclick','onmouseover','onmouseout','onmousedown','onmouseup')));
    }

    /**
     * Derrived method for inline span tags
     *
     * @param mixed  $attr Hash array with tag attributes or string with class name
     * @param string $cont Tag content
     * @return string HTML code
     * @see html::tag()
     */
    public static function span($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('span', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derrived method for form element labels
     *
     * @param mixed  $attr Hash array with tag attributes or string with 'for' attrib
     * @param string $cont Tag content
     * @return string HTML code
     * @see html::tag()
     */
    public static function label($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('for' => $attr);
        }
        return self::tag('label', $attr, $cont, array_merge(self::$common_attrib, array('for')));
    }

    /**
     * Derrived method to create <iframe></iframe>
     *
     * @param mixed $attr Hash array with tag attributes or string with frame source (src)
     * @return string HTML code
     * @see html::tag()
     */
    public static function iframe($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('src' => $attr);
        }
        return self::tag('iframe', $attr, $cont, array_merge(self::$common_attrib,
            array('src','name','width','height','border','frameborder','onload')));
    }

    /**
     * Derrived method to create <script> tags
     *
     * @param mixed $attr Hash array with tag attributes or string with script source (src)
     * @param string $cont Javascript code to be placed as tag content
     * @return string HTML code
     * @see html::tag()
     */
    public static function script($attr, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('src' => $attr);
        }
        if ($cont) {
            if (self::$doctype == 'xhtml')
                $cont = "\n/* <![CDATA[ */\n" . $cont . "\n/* ]]> */\n";
            else
                $cont = "\n" . $cont . "\n";
        }

        return self::tag('script', $attr + array('type' => 'text/javascript', 'nl' => true),
            $cont, array_merge(self::$common_attrib, array('src','type','charset')));
    }

    /**
     * Derrived method for line breaks
     *
     * @return string HTML code
     * @see html::tag()
     */
    public static function br($attrib = array())
    {
        return self::tag('br', $attrib);
    }

    /**
     * Create string with attributes
     *
     * @param array $attrib Associative arry with tag attributes
     * @param array $allowed List of allowed attributes
     * @return string Valid attribute string
     */
    public static function attrib_string($attrib = array(), $allowed = null)
    {
        if (empty($attrib)) {
            return '';
        }

        $allowed_f = array_flip((array)$allowed);
        $attrib_arr = array();
        foreach ($attrib as $key => $value) {
            // skip size if not numeric
            if ($key == 'size' && !is_numeric($value)) {
                continue;
            }

            // ignore "internal" or not allowed attributes
            if ($key == 'nl' || ($allowed && !isset($allowed_f[$key])) || $value === null) {
                continue;
            }

            // skip empty eventhandlers
            if (preg_match('/^on[a-z]+/', $key) && !$value) {
                continue;
            }

            // attributes with no value
            if (in_array($key, array('checked', 'multiple', 'disabled', 'selected', 'autofocus'))) {
                if ($value) {
                    $attrib_arr[] = $key . '="' . $key . '"';
                }
            }
            else {
                $attrib_arr[] = $key . '="' . self::quote($value) . '"';
            }
        }

        return count($attrib_arr) ? ' '.implode(' ', $attrib_arr) : '';
    }

    /**
     * Convert a HTML attribute string attributes to an associative array (name => value)
     *
     * @param string Input string
     * @return array Key-value pairs of parsed attributes
     */
    public static function parse_attrib_string($str)
    {
        $attrib = array();
        $regexp = '/\s*([-_a-z]+)=(["\'])??(?(2)([^\2]*)\2|(\S+?))/Ui';

        preg_match_all($regexp, stripslashes($str), $regs, PREG_SET_ORDER);

        // convert attributes to an associative array (name => value)
        if ($regs) {
            foreach ($regs as $attr) {
                $attrib[strtolower($attr[1])] = html_entity_decode($attr[3] . $attr[4]);
            }
        }

        return $attrib;
    }

    /**
     * Replacing specials characters in html attribute value
     *
     * @param string $str Input string
     *
     * @return string The quoted string
     */
    public static function quote($str)
    {
        static $flags;

        if (!$flags) {
            $flags = ENT_COMPAT;
            if (defined('ENT_SUBSTITUTE')) {
                $flags |= ENT_SUBSTITUTE;
            }
        }

        return @htmlspecialchars($str, $flags, POLL_CHARSET);
    }
}
