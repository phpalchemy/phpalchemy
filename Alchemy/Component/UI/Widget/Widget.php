<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Element;
use Alchemy\Component\UI\ElementInterface;

class Widget implements WidgetInterface
{
    public $id    = '';
    public $name  = '';
    public $xtype = '';
    public $label = '';
    public $value = '';

    public $attributes = array();

    public function __construct(array $attributes = array())
    {
        empty($attributes) || $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function getProperties()
    {
        $result = array();
        $refl   = new \ReflectionObject($this);
        $props  = $refl->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($props as $pro) {
            $result[$pro->getName()] = $pro->getValue($this);
        }

        return $result;
    }

    public function prepare()
    {
    }
}