<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Widget\WidgetInterface;

class Textbox extends Widget
{
    public function __construct(array $attributes = array())
    {
        // defining default attributes
        $this->attributes['size'] = 20;
        $this->attributes['max'] = null;
        $this->attributes['multiline'] = false;

        // call parent constructor
        parent::__construct($attributes);
    }
}
