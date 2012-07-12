<?php
namespace Alchemy\Mvc\Adapter;

use \Alchemy\Mvc\View;

class TwigView extends View
{
    protected $twig   = null;
    protected $loader = null;

    public function __construct($tpl = '')
    {
        parent::__construct($tpl);

        require_once 'twig/twig/lib/Twig/Autoloader.php';
        \Twig_Autoloader::register();
    }

    //Wrapped

    public function render()
    {
        $this->loader = new \Twig_Loader_Filesystem($this->templateDir);

        if ($this->cache) {
            $cache = $this->getCacheDir();
        } else {
            $cache = false;
        }

        /*
         * Twig.cache
         *
         * The following options are available:
         *
         * debug: When set to true, the generated templates have a __toString()
         *        method that you can use to display the generated nodes (default to false).
         * charset: The charset used by the templates (default to utf-8).
         *          base_template_class: The base template class to use for generated
         *          templates (default to Twig_Template).
         * cache: An absolute path where to store the compiled templates, or false
         *        to disable caching (which is the default).
         * auto_reload: When developing with Twig, it's useful to recompile the
         *              template whenever the source code changes. If you don't provide a value
         *              for the auto_reload option, it will be determined automatically based
         *              on the debug value.
         * strict_variables: If set to false, Twig will silently ignore invalid
         *                   variables (variables and or attributes/methods that do not exist) and replace them
         *                   with a null value. When set to true, Twig throws an exception instead (default to false).
         * autoescape: If set to true, auto-escaping will be enabled by default for all
         *             templates (default to true). As of Twig 1.8, you can set the escaping strategy
         *             to use (html, js, false to disable, or a PHP callback that takes the template "filename"
         *             and must return the escaping strategy to use).
         * optimizations: A flag that indicates which optimizations to apply
         *                (default to -1 -- all optimizations are enabled; set it to 0 to disable).
         */

        $this->twig = new \Twig_Environment($this->loader, array(
            'debug' => $this->debug,
            'charset' => $this->charset,
            'cache' => $cache,
            'strict_variables' => $this->debug
        ));

        $template = $this->twig->loadTemplate($this->getTpl());

        echo $template->render($this->data);
    }
}

