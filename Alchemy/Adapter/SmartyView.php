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
    public function setTemplateDir($dir)
    {
        parent::setTemplateDir($dir);
        $this->smarty->template_dir = $dir;
    }

    public function assign($key, $value)
    {
        parent::assign($key, $value);

        if (is_string($key)) {
            $this->smarty->assign($key, $this->getVar($key));
        }
    }

    public function setCacheDir($path)
    {
        $this->smarty->compile_dir = $path . 'compiled' . DS;
        $this->smarty->cache_dir   = $path . 'cache' . DS;

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

    public function getCacheDir()
    {
        return $this->smarty->cache_dir;
    }

    public function enableCache($value)
    {
        $this->smarty->caching = $value;

        if ($this->smarty->caching) {
            $this->smarty->cache_lifetime = 120;
        }
    }

    public function render()
    {
        $this->smarty->display($this->getTpl());
    }
}


