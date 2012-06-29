<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Widget\WidgetInterface;

class WidgetCollection
{
    protected $items = array();

    public function add(WidgetInterface $w)
    {
        $this->items[] = $w;
    }

    public function all()
    {
        return $this->items;
    }
}