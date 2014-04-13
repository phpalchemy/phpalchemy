<?php
namespace Alchemy\Component\UI\Element;

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
    /**
     * @var string
     */
    public  $name = "";
    /**
     * @var
     */
    protected $class = "";
    /**
     * @var string
     */
    protected $id = "";
    /**
     * @var string
     */
    protected $xtype = "";
    /**
     * @var array
     */
    protected $generated = array();
    /**
     * @var \ReflectionObject
     */
    private $meta;

    protected $matchData = array();
    protected $parent;

    public function __construct(array $attributes = array(), $parent = null)
    {
        $this->meta = new \ReflectionObject($this);
        $this->parent = $parent;

        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getXtype()
    {
        return $this->xtype;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $name
     * @param string $value
     * @return bool|void
     */
    public function setAttribute($name, $value = '')
    {
        if (is_array($name)) {
            $this->setAttributesFromArray($name);
            return true;
        }

        if (is_string($value)) {
            $occCnt = substr_count($value, "%");
            if ($occCnt > 0 && $occCnt % 2 == 0) {
                $value = str_replace(array_keys($this->matchData), array_values($this->matchData), $value);
            }
        }

        if (property_exists($this, $name)) {
            if ($this->isProtected($name)) {
                $fn = "set" . ucfirst($name);
                return $this->$fn($value);
            } else {
                return $this->{$name} = $value;
            }
        }
    }

    /**
     * @param array $attributes
     */
    public function setAttributesFromArray(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        $result = array();
        $result['attributes'] = $this->getAttributes();
        $properties  = $this->meta->getProperties(\ReflectionProperty::IS_PROTECTED);

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

    /**
     * @return array
     */
    public function getAttributes()
    {
        $result = array();
        $attributes  = $this->meta->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($attributes as $att) {
            $value = $att->getValue($this);

            if ($value !== null) {
                $result[$att->getName()] = $att->getValue($this);
            }
        }

        return $result;
    }

    public function getSubElements()
    {
        return array();
    }

    /**
     * @param $name
     * @return bool
     */
    public function isProtected($name)
    {
        $property = $this->meta->getProperty($name);

        return $property->isProtected();
    }

    /**
     * @param $type
     * @param string $content
     */
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

    /**
     * @param string $type
     * @return array
     */
    public function getGenerated($type = '')
    {
        if (empty($type)) {
            return $this->generated;
        } else {
            return $this->generated[$type];
        }
    }

    public function setMatchData($matchData)
    {
        $data = array();
        foreach ($matchData as $k => $v) {
            $data['%'.$k.'%'] = $v;
        }
        $this->matchData = $data;
    }

    /**
     * @param $val
     * @return string
     */
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

