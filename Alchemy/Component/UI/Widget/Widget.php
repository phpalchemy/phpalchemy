<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Element;
use Alchemy\Component\UI\ElementInterface;

/**
 * Class Widget
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
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