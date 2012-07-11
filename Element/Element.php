<?php
namespace Alchemy\Component\UI\Element;

use Alchemy\Component\UI\WidgetCollection;
use Alchemy\Component\UI\Widget\WidgetInterface;

use Alchemy\Component\UI\Engine;

/**
 * Class Element
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
abstract class Element
{
    public  $name = '';
    public  $class;

    protected $id = '';
    protected $generated = array();

    public function __construct(array $attributes = array())
    {
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
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

    public function setAttribute($name, $value = '')
    {
        if (is_array($name)) {
            return $this->setAttributesFromArray($name);
        }

        if (property_exists($this, $name)) {
            return $this->{$name} = $value;
        }
    }

    protected function setAttributesFromArray(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    public function getInfo()
    {
        $result = array();
        $refl   = new \ReflectionObject($this);
        $attributes  = $refl->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($attributes as $att) {
            $value = $att->getValue($this);

            if ($value !== null) {
                $result['attributes'][$att->getName()] = $att->getValue($this);
            }
        }

        $properties  = $refl->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $pro) {
            $pro->setAccessible(true);
            $value = $pro->getValue($this);
            $pro->setAccessible(false);

            if ($value !== null) {
                $result[$pro->getName()] = $value;
            }
        }

        return $result;
    }

    public function setGenerated($type, $content = '')
    {
        if (is_array($type)) {
            foreach ($type as $key => $value) {
                $this->generated[$key] = $value;
            }
        } else {
            $this->generated[$type] = $content;
        }
    }

    public function getGenerated($type = '')
    {
        if (empty($type)) {
            return $this->generated;
        } else {
            return $this->generated[$type];
        }
    }

    private function toString($val)
    {
        if ($val === true) {
            return 'true';
        } elseif ($val === false) {
            return 'false';
        } else {
            return (string) $val;
        }
    }
}

