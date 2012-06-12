<?php
namespace Alchemy\Adapter;

class TwigView extends \Alchemy\Mvc\View
{
    private $twig   = null;
    private $loader = null;

    public function __construct($tpl = NULL)
    {
        parent::__construct($tpl);

        require_once 'twig/twig/lib/Twig/Autoloader.php';
        \Twig_Autoloader::register();
    }

    //Wrapped

    public function render()
    {
        $this->loader = new \Twig_Loader_Filesystem($this->templateDir);

        if ($this->cacheEnabled) {
            $cache = $this->getCacheDir();
        } else {
            $cache = false;
        }

        $this->twig = new \Twig_Environment($this->loader, array(
            'cache' => $cache,
        ));

        $template = $this->twig->loadTemplate($this->getTpl());

        echo $template->render($this->data);
    }
}


