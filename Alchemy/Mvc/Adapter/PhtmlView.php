<?php
namespace Alchemy\Mvc\Adapter;

use \Alchemy\Mvc\View;

class PhtmlView extends View
{
    public static $blocks = array();

    private static $instance;
    private static $extend = '';

    public function __construct($tpl = '')
    {
        parent::__construct($tpl);
    }

    //Wrapped

    public function render()
    {
        self::$instance = $this;
        $tplFile = $this->getTemplateDir() . DIRECTORY_SEPARATOR . $this->getTpl();

        if (!file_exists($tplFile)) {
            echo 'ERROR: Layout template file '.$tplFile.' does not exist!';
            die;
        }

        extract($this->data);

        include $tplFile;

        if (!empty(self::$extend)) {
            $tplFile = $this->getTemplateDir() . DIRECTORY_SEPARATOR . self::$extend . '.phtml';

            if (!file_exists($tplFile)) {
                echo 'ERROR: Layout template file '.$tplFile.' does not exist!';
                die;
            }

            extract($this->data);

            include $tplFile;
        }
    }

    static public function extend($name)
    {
        self::$extend = $name;
    }

    static public function block($name, $default = '')
    {
        if (is_string($default)) {
            if (array_key_exists($name, self::$blocks)) {
                $callback = self::$blocks[$name];
                $callback();
            } else {
                return $default;
            }
            return;
        } elseif ($default instanceof \Closure) {
            self::$blocks[$name] = $default;
        }
    }
}

