<?php
namespace Alchemy\Component\UI;

abstract class Reader
{
    /**
     * @var \Alchemy\Component\UI\Element\Element
     */
    protected $element = null;

    /**
     * @return \Alchemy\Component\UI\Element\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    abstract public function parse();
}

