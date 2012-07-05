<?php
namespace Alchemy\Component\UI;

abstract class Reader
{
    protected $widgets = null;
    protected $attributes = array();

    abstract public function parse();

    public function getWidgets()
    {
        return $this->widgets;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}