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

    protected static function toCamelCase($str)
    {
        return strpos($str, "_") !== false ? lcfirst(str_replace(" ", "", ucwords(str_replace("_", " ", $str)))) : $str;
    }
}

