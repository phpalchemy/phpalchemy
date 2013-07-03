<?php
namespace Alchemy\Mvc\Adapter;

use \Alchemy\Mvc\View;

class SmartyView extends View
{
    protected $smarty;

    public function __construct($tpl = '')
    {
        if (! class_exists('\Smarty')) {
            throw new \Exception(
                "Missing Vendor: Smarty Template Engine library is not installed on project!\n" .
                "You can solve this adding the missing vendor to composer.json and executing 'composer.phar update'"
            );
        }

        parent::__construct($tpl);

        $this->smarty = new \Smarty();

        $this->smarty->registerPlugin("function","form_widget", array($this, "formWidget"));
    }

    //Wrapped
    public function setTemplateDir($dir)
    {
        parent::setTemplateDir($dir);
        $this->smarty->template_dir = $this->getTemplateDir();
    }

    public function setCacheDir($path)
    {
        $this->smarty->compile_dir = $path . 'compiled' . DS;
        $this->smarty->cache_dir   = $path . 'cache' . DS;

        if (!is_dir($this->smarty->compile_dir)) {
            $this->createDir($this->smarty->compile_dir);
        }

        if (!is_dir($this->smarty->cache_dir)) {
            $this->createDir($this->smarty->cache_dir);
        }
    }

    public function getCacheDir()
    {
        return $this->smarty->cache_dir;
    }

    public function enableCache($value)
    {
        parent::enableCache($value);
        $this->smarty->caching = $this->cache;

        if ($this->cache) {
            $this->smarty->cache_lifetime = 120;
        }
    }

    public function enableDebug($value)
    {
        parent::enableDebug($value);
        $this->smarty->debugging = $this->debug;
    }

    public function setCharset($charset)
    {
        parent::setCharset($charset);
        \Smarty::$_CHARSET = $this->charset;
    }


    public function assign($name, $value = null)
    {
        parent::assign($name, $value);

        if (is_string($name)) {
            return $this->smarty->assign($name, $value);
        }

        if (is_array($name)) {
            return $this->assignFromArray($name);
        }

        throw new \InvalidArgumentException("Invalid data type for key, '" .gettype($name) . "' given.");
    }

    public function render()
    {
        $this->smarty->display($this->getTpl());
    }

    public function formWidget($params, $smarty)
    {
        return $this->uiElements[$params['id']];
    }
}

