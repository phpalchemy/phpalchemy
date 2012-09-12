<?php
namespace Alchemy\Mvc\Adapter;

use Alchemy\Mvc\View;
use Alchemy\Component\WebAssets\Bundle;

class TwigView extends View
{
    protected $twig   = null;
    protected $loader = null;

    public function __construct($tpl = '', Bundle $assetsHandler = null)
    {
        parent::__construct($tpl, $assetsHandler);

        require_once 'twig/twig/lib/Twig/Autoloader.php';
        \Twig_Autoloader::register();
    }

    //Wrapped

    public function render()
    {
        $this->prepare();

        $template = $this->twig->loadTemplate($this->getTpl());

        echo $template->render($this->data);
    }

    protected function prepare()
    {
        // configuring twig
        $this->loader = new \Twig_Loader_Filesystem($this->templateDir);
        $cache = $this->cache ? $this->getCacheDir() : false;

        // options doc: http://twig.sensiolabs.org/doc/api.html#environment-options
        $this->twig = new \Twig_Environment($this->loader, array(
            'debug' => $this->debug,
            'charset' => $this->charset,
            'cache' => $cache,
            'strict_variables' => $this->debug
        ));

        // adding additional functionalities
        $this->twig->addExtension(new AssetExtension($this, $this->assetsHandler));
    }
}

class AssetExtension implements \Twig_ExtensionInterface
{
    protected $view;
    protected $assetsHandler;

    public function __construct($view, $assetsHandler)
    {
        $this->view = $view;
        $this->assetsHandler = $assetsHandler;
    }

    public function getName()
    {
        return 'asset';
    }

    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'fn'),
        );
    }


    public function getOperators()
    {
        return array();
    }
    public function getGlobals()
    {
        return array();
    }
    public function initRuntime(\Twig_Environment $environment)
    {

    }
    public function getTokenParsers()
    {
        return array();
    }
    public function getNodeVisitors()
    {
        return array();
    }
    public function getFilters()
    {
        return array();
    }
    public function getTests()
    {
        return array();
    }

    // additional functionalities
    public function fn()
    {
        if (empty($this->assetsHandler)) {
            return $assetPath;
        }

        //$this->assetsHandler->register(func_get_args());

        call_user_func_array(array($this->assetsHandler, 'register'), func_get_args());
        $this->assetsHandler->handle();
        $this->assetsHandler->setForceReset(true);

        $baseurl = $this->view->exists('baseurl') ? $this->view->get('baseurl') : '';

        return $baseurl . $this->assetsHandler->getUrl();
    }
}

