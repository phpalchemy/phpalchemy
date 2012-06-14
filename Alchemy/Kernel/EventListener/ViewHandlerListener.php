<?php
namespace Alchemy\Kernel\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\ViewEvent;
use Alchemy\Net\Http\Response;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Util\Annotations;

class ViewHandlerListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public function onViewHandling(ViewEvent $event)
    {
        // create Annotation object to read controller's method annotations
        $annotation = new Annotations();
        $annotation->setDefaultAnnotationNamespace('\Alchemy\Annotation\\');

        // getting information from ViewEvent object
        $controllerMeta = $event->getControllerMeta();
        $config         = $event->getConfig();
        $data           = $event->getData();

        // getting all annotations of controller's method
        $annotationObjects = $annotation->getMethodAnnotationsObjects(
            $controllerMeta['class'],
            $controllerMeta['method']
        );

        // check if a @view definition exists on method's annotations
        if (empty($annotationObjects['View'])) {
            return null; // no @view annotation found, just return to break view handling
        }

        // creating config obj and setting it with all defaults configurations
        $conf = new \StdClass();

        $conf->template     = $annotationObjects['View'][0]->template;
        $conf->engine       = $config->get('templating.default_engine');
        $conf->templateDir  = $config->get('app.views_dir') . DS;
        $conf->cacheDir     = $config->get('templating.cache_dir') . DS;
        $conf->cacheEnabled = $config->get('templating.cache_enabled');
        $conf->extension    = $config->get('templating.extension');
        $conf->charset      = $config->get('templating.charset');
        $conf->debug        = $config->get('templating.debug');

        // Setting template engine
        // Check if template engine param was set on annotation. i.e.: @view(engine=...)
        if (!empty($annotationObjects['View'][0]->engine)) {
            $conf->engine = $annotationObjects['View'][0]->engine; // it exits, use it!
        }

        // check if template filename is empty
        if (empty($conf->template)) { //that means it wasn't set on @view annotation
            // Then we compose a template filename using controller class and method names but
            // removing ...Controller & ..Action sufixes from those names
            $nsSepPos       = strrpos($this->controllerMeta['class'], '\\');
            $conf->template = substr($this->controllerMeta['class'], $nsSepPos + 1, -10) . DS;
            $conf->template .= substr($this->controllerMeta['method'], 0, -6);
        }

        // File extension validation
        // A criteria can be if filename doesn't a period character (.)
        if (strpos($conf->template, '.') === false && !empty($conf->extension)) {
            $conf->template .= '.' . $conf->extension; // concatenate it with default extension from configuration
        }

        // check if template file exists
        if (file_exists($conf->templateDir . $conf->template)) { // if relative path was given
        } elseif (file_exists($conf->template)) { // if absolute path was given
        } else { // file doesn't exist, throw error
            throw new \Exception("Error, File Not Found: template file doesn't exist: '{$conf->template}'");
        }

        // composing the view class string
        $viewClass = '\Alchemy\Adapter\\'.ucfirst($conf->engine).'View';

        // check if view engine class exists
        if (!class_exists($viewClass)) { // does not exist, throw an exception
            throw new Exception("Error Processing: Template Engine is not available: '{$conf->engine}'");
        }

        // create view object
        $view = new $viewClass($conf->template);

        // setup view object
        $view->enableDebug($conf->debug);
        $view->enableCache($conf->cacheEnabled);
        $view->setCacheDir($conf->cacheDir);
        $view->setTemplateDir($conf->templateDir);
        $view->setCharset($conf->charset);

        // setting data to be used by template
        $view->assign($data);

        // the most important part; Set factored view to event property
        $event->setView($view);
    }

    static public function getSubscribedEvents()
    {
        return array(KernelEvents::VIEW => array('onViewHandling'));
    }
}