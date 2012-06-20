<?php
namespace Alchemy\Kernel\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\ViewEvent;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Component\Http\Response;

class ViewListener implements EventSubscriberInterface
{
    /**
     * This method hadle the View layer at MVC Patter
     *
     * @param  ViewEvent $event event that contains all information to build
     *                          a adapter of supported template engines
     */
    public function onView(ViewEvent $event)
    {
        // getting information from ViewEvent object
        $annotation = $event->getAnnotation();
        $config     = $event->getConfig();
        $data       = $event->getData();
        $class      = $event->getControllerClass();
        $method     = $event->getControllerMethod();

        // check if a @view definition exists on method's annotations
        if (empty($annotation)) {
            return null; // no @view annotation found, just return to break view handling
        }

        if (count($annotation) > 1) {
            throw new \Exception(sprintf(
                "View Annotations Error: Just can define one @View annotation, (%s) annotations found.",
                count($annotation)
            ));
        }

        $annotation = $annotation[0];

        // creating config obj and setting it with all defaults configurations
        $conf = new \StdClass();

        $conf->template     = $annotation->template;
        $conf->engine       = $config->get('templating.default_engine');
        $conf->templateDir  = $config->get('app.views_dir') . DS;
        $conf->cacheDir     = $config->get('templating.cache_dir') . DS;
        $conf->cacheEnabled = $config->get('templating.cache_enabled');
        $conf->extension    = $config->get('templating.extension');
        $conf->charset      = $config->get('templating.charset');
        $conf->debug        = $config->get('templating.debug');

        // Setting template engine
        // Check if template engine param was set on annotation. i.e.: @view(engine=...)
        if (!empty($annotation->engine)) {
            $conf->engine = $annotation->engine; // it exits, use it!
        }

        // check if template filename is empty
        if (empty($conf->template)) { //that means it wasn't set on @view annotation
            // Then we compose a template filename using controller class and method names but
            // removing ...Controller & ..Action sufixes from those names
            $nsSepPos        = strrpos($class, '\\');
            $conf->template  = substr($class, $nsSepPos + 1, -10) . DS;
            $conf->template .= substr($method, 0, -6);
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

    /**
     * Static method that returns all subscribed events on this listener
     *
     * @return array subscribed events
     */
    static public function getSubscribedEvents()
    {
        return array(KernelEvents::VIEW => array('onView'));
    }
}