<?php
namespace Alchemy\Component\UI;

abstract class Reader
{
    protected $element = null;

    public function getElement()
    {
        return $this->element;
    }

    abstract public function parse();
}

