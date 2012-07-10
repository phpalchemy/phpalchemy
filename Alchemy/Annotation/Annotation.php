<?php
namespace Alchemy\Annotation;

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
            foreach ($key as $name => $value) {
                $this->data[$name] = $value;
            }
        } else {
            $this->data[$key] = $value;
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

    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }
}

