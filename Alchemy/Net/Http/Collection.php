<?php
namespace Alchemy\Net\Http;

class Collection
{
    public $data = array();

    function __construct($data)
    {
        $this->data = $data;
    }

    function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    function all()
    {
        return $this->data;
    }

    function has($key)
    {
        return isset($this->data[$key]);
    }

    public function add(array $data = array())
    {
        $this->data = array_replace($this->data, $data);
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}