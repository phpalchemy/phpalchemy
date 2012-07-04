<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Element;
use Alchemy\Component\UI\ElementInterface;

/**
 * Abstract Class Widget
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
abstract class Widget implements WidgetInterface
{
    public $name  = '';
    public $label = '';

    protected $id    = '';
    protected $value = '';
    protected $xtype = '';

    public function __construct(array $attributes = array())
    {
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setXtype($xtype)
    {
        $this->xtype = $xtype;
    }

    public function getXtype()
    {
        return $this->xtype;
    }

    public function getInfo()
    {
        $result = array();
        $refl   = new \ReflectionObject($this);
        $attributes  = $refl->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($attributes as $att) {
            $result['attributes'][$att->getName()] = $att->getValue($this);
        }

        $properties  = $refl->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $pro) {
            $result[$pro->getName()] = $pro->getValue($this);
        }

        return $result;
    }

    public function prepare()
    {
    }
}