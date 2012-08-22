<?php
namespace Alchemy\Annotation;

/**
 * Annotation abstract class
 * Add Annotations support to classes and methods
 *
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Annotation
 */
abstract class Annotation
{
    protected $data = array();

    public function __construct($args = array())
    {
        $this->data = $args;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            return $this->setFromArray($key);
        }

        $this->data[$key] = $value;
    }

    protected function setFromArray($value)
    {
        foreach ($value as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function get($key, $default = null)
    {
        if (!array_key_exists($key, $this->data)) {
            return $default;
        }

        return $this->data[$key];
    }

    public function all()
    {
        return $this->data;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function prepare()
    {
    }
}

