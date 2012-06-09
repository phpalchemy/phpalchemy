<?php
namespace Alchemy\Adapter;

class SmartyView extends \Alchemy\Mvc\View
{
    private $smarty;

    public function __construct($tpl = NULL)
    {
        require_once '3rd-party/smarty/libs/Smarty.class.php';
        parent::__construct($tpl);

        $this->smarty = new \Smarty();
    }

    //Wrapped
    public function setBaseDir($dir)
    {
        parent::setBaseDir($dir);
        $this->smarty->template_dir = $dir;
    }

    public function assign($key, $value)
    {
        parent::assign($key, $value);

        if (is_string($key)) {
            $this->smarty->assign($key, $this->getVar($key));
        }
    }

    public function setCachePath($path)
    {
        $this->smarty->compile_dir = $path . 'smarty' . DS . 'compiled' . DS;
        $this->smarty->cache_dir   = $path . 'smarty' . DS . 'cache' . DS;

        if  (!is_dir($this->smarty->compile_dir)) {
            if (!@mkdir($this->smarty->compile_dir)) {
                throw new \Exception("Could't create smarty compile directory {$this->smarty->compile_dir}");
            }
        }

        if  (!is_dir($this->smarty->cache_dir)) {
            if (!@mkdir($this->smarty->cache_dir)) {
                throw new \Exception("Could't create smarty cache directory {$this->smarty->cache_dir}");
            }
        }
    }

    public function getCachePath()
    {
        return $this->smarty->cache_dir;
    }

    public function disableCache($value)
    {
        $this->smarty->caching        = $value === true;
        $this->smarty->cache_lifetime = 120;
    }

    public function render()
    {
        $this->smarty->display($this->getBaseDir() . $this->getTpl());
    }
}


