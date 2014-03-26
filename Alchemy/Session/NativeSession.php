<?php
namespace Alchemy\Session;

class NativeSession extends Session
{
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function get($name, $default = "")
    {
        return array_key_exists($name, $_SESSION)? $_SESSION[$name]: $default;
    }
}