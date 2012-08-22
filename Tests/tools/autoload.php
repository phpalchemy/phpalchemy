<?php
class Loader
{
    protected $includePaths = array();
    protected $namespace    = '';

    static $ds = DIRECTORY_SEPARATOR;

    public function __construct() {
        spl_autoload_register(array($this, 'classLoader'));
    }


    function add($namespace, $path)
    {
        $this->namespace = $namespace;
        $this->includePaths[] = rtrim($path, Loader::$ds) . Loader::$ds;
    }

    function classLoader($className)
    {
        //var_dump($className);
        $filename = '';

        // if ($this->namespace != '') {
        //     $className = str_replace($this->namespace, '', $className);
        // }

        if (false !== strpos($className, '\\')) {
            $filename = str_replace('\\', Loader::$ds, ltrim($className, '\\'));
        }

        $filename = str_replace('_', Loader::$ds, $filename) . '.php';

        foreach ($this->includePaths as $path) {
            //var_dump($path . $filename); //die;
            if (file_exists($path . $filename)) {
                require_once $path . $filename;
            }
        }

        return false;
    }
}

return new Loader();
