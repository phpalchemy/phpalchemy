<?php
namespace Alchemy\Component\UI\Element;

/**
 * Class Datatable
 *
 * @version 1.0
 * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link https://github.com/phpalchemy/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package Alchemy\Component\UI\Element
 */
class Datatable extends Element
{
    protected $xtype = 'datatable';
    protected $title = "";
    protected $dataSourceUri = "";
    protected $dataSourceType = "array";
    protected $serverSide = false;
    protected $columns = array();
    protected $data = array();

    /**
     * @param string $xtype
     */
    public function setXtype($xtype)
    {
        $this->xtype = $xtype;
    }

    /**
     * @return string
     */
    public function getXtype()
    {
        return $this->xtype;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $dataSourceUri
     */
    public function setDataSourceUri($dataSourceUri)
    {
        $this->dataSourceUri = $dataSourceUri;
    }

    /**
     * @return string
     */
    public function getDataSourceUri()
    {
        return $this->dataSourceUri;
    }

    /**
     * @param string $dataSourceType
     */
    public function setDataSourceType($dataSourceType)
    {
        $this->dataSourceType = $dataSourceType;
    }

    /**
     * @return string
     */
    public function getDataSourceType()
    {
        return $this->dataSourceType;
    }

    /**
     * @param boolean $serverSide
     */
    public function setServerSide($serverSide)
    {
        $this->serverSide = $serverSide;
    }

    /**
     * @return boolean
     */
    public function getServerSide()
    {
        return $this->serverSide;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}