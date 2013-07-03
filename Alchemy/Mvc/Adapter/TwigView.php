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

        if (! class_exists('\Twig_Autoloader')) {
            throw new \Exception(
                "Missing Vendor: Twig Template Engine library is not installed on project!\n" .
                "You can solve this adding the missing vendor to composer.json and executing 'composer.phar update'"
            );
        }
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
        $this->twig->addExtension(new AssetExtension($this, $this->assetsHandler, $this->uiElements));
    }
}

//[[[ @init class (
if (class_exists('\Twig_Autoloader')) {

class AssetExtension implements \Twig_ExtensionInterface
{
    protected $view;
    protected $assetsHandler;

    public function __construct($view, $assetsHandler, $uiElements)
    {
        $this->view = $view;
        $this->assetsHandler = $assetsHandler;
        $this->uiElements = $uiElements;
    }

    public function getName()
    {
        return 'asset';
    }

    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'assetFn'),
            'form' => new \Twig_Function_Method($this, 'formFn'),
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
    public function assetFn()
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

    public function formFn($formId)
    {
        if (! array_key_exists($formId, $this->uiElements)) {
            throw new \Exception("Error: Form with id: $formId does not exist!");
        }

        return $this->uiElements[$formId];
    }
}

//) @end class ]]]
}

