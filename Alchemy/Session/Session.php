<?php
namespace Alchemy\Session;

class Session
{
    protected $data;

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function get($name, $default = "")
    {
        return isset($this->data[$name])? $this->data[$name]: $default;
    }
}