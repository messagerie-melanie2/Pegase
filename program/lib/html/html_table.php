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
 * Class to build an HTML table
 *
 * @package    Lib
 * @subpackage HTML
 */
class html_table extends html
{
    protected $tagname = 'table';
    protected $allowed = array('id','class','style','width','summary',
        'cellpadding','cellspacing','border');

    private $columns = array();
    private $header = array();
    private $rows = array();
    private $rowindex = 0;
    private $colindex = 0;

    /**
     * Constructor
     *
     * @param array $attrib Named tag attributes
     */
    public function __construct($attrib = array())
    {
        $default_attrib = self::$doctype == 'xhtml' ? array('summary' => '', 'border' => 0) : array();
        $this->attrib = array_merge($attrib, $default_attrib);

        if (!empty($attrib['tagname']) && $attrib['tagname'] != 'table') {
          $this->tagname = $attrib['tagname'];
          $this->allowed = self::$common_attrib;
        }
    }

    /**
     * Add a table cell
     *
     * @param array  $attr Cell attributes
     * @param string $cont Cell content
     */
    public function add($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }

        $cell = new \stdClass;
        $cell->attrib  = $attr;
        $cell->content = $cont;
        
        if (!isset($attr['colspan'])) $attr['colspan'] = 1;

        $this->rows[$this->rowindex]->cells[$this->colindex] = $cell;
        $this->colindex += max(1, intval($attr['colspan']));

        if (isset($this->attrib['cols']) && $this->attrib['cols'] && $this->colindex >= $this->attrib['cols']) {
            $this->add_row();
        }
    }

    /**
     * Add a table header cell
     *
     * @param array  $attr Cell attributes
     * @param string $cont Cell content
     */
    public function add_header($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }

        $cell = new \stdClass;
        $cell->attrib   = $attr;
        $cell->content  = $cont;
        $this->header[] = $cell;
    }
    
    /**
     * Add a table column
     *
     * @param array  $attr Column attributes
     * @param string $cont Column content
     */
    public function add_column($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
    
        $column = new \stdClass;
        $column->attrib   = $attr;
        $column->content  = $cont;
        $this->columns[] = $column;
    }

    /**
     * Remove a column from a table
     * Useful for plugins making alterations
     *
     * @param string $class
     */
    public function remove_column($class)
    {
        // Remove the header
        foreach ($this->header as $index=>$header){
            if ($header->attrib['class'] == $class){
                unset($this->header[$index]);
                break;
            }
        }

        // Remove cells from rows
        foreach ($this->rows as $i=>$row){
            foreach ($row->cells as $j=>$cell){
                if ($cell->attrib['class'] == $class){
                    unset($this->rows[$i]->cells[$j]);
                    break;
                }
            }
        }
    }

    /**
     * Jump to next row
     *
     * @param array $attr Row attributes
     */
    public function add_row($attr = array())
    {
        $this->rowindex++;
        $this->colindex = 0;
        $this->rows[$this->rowindex] = new \stdClass;
        $this->rows[$this->rowindex]->attrib = $attr;
        $this->rows[$this->rowindex]->cells = array();
    }

    /**
     * Set row attributes
     *
     * @param array $attr  Row attributes
     * @param int   $index Optional row index (default current row index)
     */
    public function set_row_attribs($attr = array(), $index = null)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }

        if ($index === null) {
            $index = $this->rowindex;
        }

        // make sure row object exists (#1489094)
        if (!$this->rows[$index]) {
            $this->rows[$index] = new \stdClass;
        }

        $this->rows[$index]->attrib = $attr;
    }

    /**
     * Get row attributes
     *
     * @param int $index Row index
     *
     * @return array Row attributes
     */
    public function get_row_attribs($index = null)
    {
        if ($index === null) {
            $index = $this->rowindex;
        }

        return $this->rows[$index] ? $this->rows[$index]->attrib : null;
    }

    /**
     * Build HTML output of the table data
     *
     * @param array $attrib Table attributes
     * @return string The final table HTML code
     */
    public function show($attrib = null)
    {
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $tcols = $thead = $tbody = "";
        
        // include <col>
        if (!empty($this->columns)) {
            foreach ($this->columns as $c => $col) {
                $tcols .= self::tag('col', $col->attrib, $col->content);
            }
        }

        // include <thead>
        if (!empty($this->header)) {
            $rowcontent = '';
            foreach ($this->header as $c => $col) {
                $rowcontent .= self::tag($this->_col_tagname(), $col->attrib, $col->content);
            }
            $thead = $this->tagname == 'table' ? self::tag('thead', null, self::tag('tr', null, $rowcontent, parent::$common_attrib)) :
                self::tag($this->_row_tagname(), array('class' => 'thead'), $rowcontent, parent::$common_attrib);
        }

        foreach ($this->rows as $r => $row) {
            $rowcontent = '';
            foreach ($row->cells as $c => $col) {
                $rowcontent .= self::tag($this->_col_tagname(), $col->attrib, $col->content);
            }

            if ($r < $this->rowindex || count($row->cells)) {
                $tbody .= self::tag($this->_row_tagname(), isset($row->attrib) ? $row->attrib : null, $rowcontent, parent::$common_attrib);
            }
        }

        if (isset($this->attrib['rowsonly']) && $this->attrib['rowsonly']) {
            return $tbody;
        }

        // add <tbody>
        $this->content = $tcols . $thead . ($this->tagname == 'table' ? self::tag('tbody', null, $tbody) : $tbody);

        unset($this->attrib['cols'], $this->attrib['rowsonly']);
        return parent::show();
    }

    /**
     * Count number of rows
     *
     * @return The number of rows
     */
    public function size()
    {
        return count($this->rows);
    }

    /**
     * Remove table body (all rows)
     */
    public function remove_body()
    {
        $this->rows     = array();
        $this->rowindex = 0;
    }

    /**
     * Getter for the corresponding tag name for table row elements
     */
    private function _row_tagname()
    {
        static $row_tagnames = array('table' => 'tr', 'ul' => 'li', '*' => 'div');
        return $row_tagnames[$this->tagname] ?: $row_tagnames['*'];
    }

    /**
     * Getter for the corresponding tag name for table cell elements
     */
    private function _col_tagname()
    {
        static $col_tagnames = array('table' => 'td', '*' => 'span');
        return $col_tagnames[$this->tagname] ?: $col_tagnames['*'];
    }

}
