<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Widget\WidgetInterface;

class WidgetCollection
{
    private $items = array();

    public function add(WidgetInterface $widget)
    {
        array_push($this->items, $widget);
    }

    public function next()
    {
        /**
         * This function 'next()' may return Boolean FALSE, but may also return a non-Boolean
         * value which evaluates to FALSE, such as 0 or ""
         */
        return next($this->items) ? true : false;
    }

    public function get($index=null)
    {
        return current($this->items);
    }

    public function reset()
    {
        reset($this->items);
    }
}