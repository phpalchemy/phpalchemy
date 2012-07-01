<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Kernel;

use Alchemy\Component\EventDispatcher\EventDispatcher;

// events
use Alchemy\Kernel\Event\GetResponseEvent;
use Alchemy\Kernel\Event\FilterResponseEvent;
use Alchemy\Kernel\Event\ControllerEvent;
use Alchemy\Kernel\Event\ViewEvent;
use Alchemy\Kernel\Event\ResponseEvent;
use Alchemy\Kernel\Event\FilterControllerEvent;

use Alchemy\Kernel\KernelEvents;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Mvc\ControllerResolver;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Component\Http\JsonResponse;
use Alchemy\Component\Annotations\Annotations;
use Alchemy\Config;

use Alchemy\Component\Routing\Exception\ResourceNotFoundException;
use Alchemy\Component\Routing\Mapper;

use Alchemy\Annotation\ViewAnnotation;

/**
 * Class Kernel
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Kernel implements KernelInterface
{
    protected $matcher    = null;
    protected $resolver   = null;
    protected $dispatcher = null;
    protected $config     = null;
    protected $view       = null;
    protected $controller = null;

    public $request = null;

    /**
     * Kernel constructor
     *
     * @param EventDispatcher     $dispatcher Event dispatcher object.
     * @param UrlMatcherInterface $matcher    UrlMatcher object.
     * @param ControllerResolver  $resolver   Controller resolver object.
     * @param Config              $config     Configuration object.
     */
    public function __construct(
        EventDispatcher $dispatcher, Mapper $matcher, ControllerResolver $resolver,
        Config $config, Annotations $annotation
    )
    {
        $this->matcher    = $matcher;
        $this->resolver   = $resolver;
        $this->dispatcher = $dispatcher;
        $this->config     = $config;
        $this->annotation = $annotation;

        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\'); // NAMESPACE_SEPARATOR
    }

    /**
     * handle the http user request
     * @param  Request $request user hhtp request
     */
    public function handle(Request $request)
    {
        /*
         * "EVENT" KernelEvents::REQUEST using GetResponse Event
         *
         * This event can be used by an application to filter a request before
         * ever all kernel logic being executed, listeners for this event should
         * be registered outside framework logic (should be on application logic).
         */
        if ($this->dispatcher->hasListeners(KernelEvents::REQUEST)) {
            $event = new GetResponseEvent($this, $request);
            $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

            if ($event->hasResponse()) {
                $event = new FilterResponseEvent($this, $request, $event->getResponse());
                $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

                return $event->getResponse();
            }
        }

        $annotatedObjects = array();

        try {
            /*
             * try match the url request with a defined route.
             * it any route match the current url a ResourceNotFoundException
             * will be thrown.
             */
            $params = $this->matcher->match($request->getPathInfo());

            // prepare request params.
            $params = $this->prepareRequestParams(array($params, $request->query->all()));

            // add prepared params to request as attributes.
            $request->attributes->add($params);

            // resolve controller
            try {
                $controller = $this->resolver->getController($request);
            } catch (\Exception $exception) {
                /*
                 * Detailed exception types are only for development environments
                 * if the current is a non dev environment just overrides with
                 * a ResourceNotFoundException exception
                 */
                if (substr($this->config->get('env.type'), 0, 3) !== 'dev') {
                    $exception = new ResourceNotFoundException($request->getPathInfo());
                }

                throw $exception;
            }

            if (!$controller) {
                throw new ResourceNotFoundException($request->getPathInfo());
            }


            // ANNOTATIONS, Getting controller annotations
            $this->annotation->setDefaultAnnotationNamespace(NS.'Alchemy'.NS.'Annotation'.NS);

            // getting all annotations objects of controller's method
            $annotatedObjects = $this->annotation->getMethodAnnotationsObjects(
                $params['_controllerClass'], $params['_controllerMethod']
            );
            // ANNOTATIONS END;

            $arguments = $this->resolver->getArguments($request, $controller);

            //"EVENT" FILTER_CONTROLLER
            if ($this->dispatcher->hasListeners(KernelEvents::FILTER_CONTROLLER)) {
                $event = new FilterControllerEvent($this, $controller, $request);
                $this->dispatcher->dispatch(KernelEvents::FILTER_CONTROLLER, $event);

                // getting controller; this can be the same or other filtered controller
                $controller = $event->getController();
            }

            //"EVENT" BEFORE_CONTROLLER
            if ($this->dispatcher->hasListeners(KernelEvents::BEFORE_CONTROLLER)) {
                $event = new ControllerEvent($this, $controller, $arguments, $request);
                $this->dispatcher->dispatch(KernelEvents::BEFORE_CONTROLLER, $event);
            }

            // Execute controller action
            $response = call_user_func_array($controller, $arguments);

            // check returned value by method
            if (is_array($response)) { // if it returns a array
                // set controller view data object
                foreach ($response as $key => $value) {
                    $controller[0]->view->$key = $value;
                }
            }

            // getting controller's data.
            $controllerData = (array) $controller[0]->view;

            if (! ($response instanceof Response || $response instanceof JsonResponse)) {
                if (isset($annotatedObjects['JsonResponse'])) {
                    $response = new JsonResponse();
                    $response->setData($controllerData);
                } else {
                    $response = new Response();
                }
            }

            //"EVENT" AFTER_CONTROLLER
            if ($this->dispatcher->hasListeners(KernelEvents::AFTER_CONTROLLER)) {
                if (!isset($event) || !($event instanceof ControllerEvent)) {
                    $event = new ControllerEvent($this, $controller, $arguments, $request);
                }
                $event->setResponse($response);

                $this->dispatcher->dispatch(KernelEvents::AFTER_CONTROLLER, $event);
            }

            // gets View instance
            $viewAnnotation = empty($annotatedObjects['View']) ? null : $annotatedObjects['View'];
            $view = $this->handleView(
                $params['_controllerClass'], $params['_controllerMethod'],
                $controllerData, $viewAnnotation
            );

            // if there is a view adapter instance, get its contents and set to response content
            if (!empty($view)) {
                //"EVENT" VIEW dispatch all KernelEvents::VIEW events
                if ($this->dispatcher->hasListeners(KernelEvents::VIEW)) {
                    $event = new ViewEvent();
                    $this->dispatcher->dispatch(KernelEvents::VIEW, $event);

                    $view = $event->getView();
                }

                $response->setContent($view->getOutput());
            }

        } catch (ResourceNotFoundException $e) {
            $response = new Response($e->getMessage(), 404);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage(), 500);
        }

        $responseAnnotation = empty($annotatedObjects['Response']) ? null : $annotatedObjects['Response'];

        // dispatch a response event
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, new FilterResponseEvent($this, $request, $response, $responseAnnotation));

        return $response;
    }

    protected function handleView($class, $method, $data, $annotation)
    {
        // check if a @view definition exists on method's annotations
        if (empty($annotation)) {
            return null; // no @view annotation found, just return to break view handling
        }

        $annotation->resolveTemplateName();

        // creating config obj and setting it with all defaults configurations
        $conf = new \StdClass();

        $conf->template     = $annotation->template;
        $conf->engine       = $this->config->get('templating.default_engine');
        $conf->templateDir  = $this->config->get('app.views_dir') . DS;
        $conf->cacheDir     = $this->config->get('templating.cache_dir') . DS;
        $conf->cacheEnabled = $this->config->get('templating.cache_enabled');
        $conf->extension    = $this->config->get('templating.extension');
        $conf->charset      = $this->config->get('templating.charset');
        $conf->debug        = $this->config->get('templating.debug');

        // Setting template engine
        // Check if template engine param was set on annotation. i.e.: @view(engine=...)
        if (!empty($annotation->engine)) {
            $conf->engine = $annotation->engine; // it exits, use it!
        }

        // check if template filename is empty
        if (empty($conf->template)) { //that means it wasn't set on @view annotation
            // Then we compose a template filename using controller class and method names but
            // removing ...Controller & ..Action sufixes from those names
            $nsSepPos        = strrpos($class, NS);
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
        $viewClass = NS.'Alchemy'.NS.'Adapter'.NS.ucfirst($conf->engine).'View';

        // check if view engine class exists
        if (!class_exists($viewClass)) { // does not exist, throw an exception
            throw new \Exception("Error Processing: Template Engine is not available: '{$conf->engine}'");
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

        return $view;
    }

    protected function prepareRequestParams($data)
    {
        $params    = array();
        $namespace = $this->config->get('app.namespace');

        foreach ($data as $listParams) {
            $params = array_merge($params, $listParams);
        }

        // verify controller's name is underscored or not
        if (strpos($params['_controller'], '_') === false) {
            //just ensure first characted to uppercase
            $params['_controller'] = ucfirst($params['_controller']);
        } else {
            // camelize the controller's name (to camelcase)
            $params['_controller'] = str_replace(
                ' ', '', ucwords(str_replace('_', ' ', $params['_controller']))
            );
        }

        // verify if action's name is underscored or not
        if (strpos($params['_action'], '_') !== false) {
            // camileze the action's name (to camelcase)
            $tmp = str_replace(' ', '', ucwords(str_replace('_', ' ', $params['_action'])));
            // this is to ensure first charcater to lowercase,
            // because by php standard coding says function name should starts with lowercase
            $params['_action'] = strtolower(substr($tmp, 0, 1)) . substr($tmp, 1);
            unset($tmp);
        }

        // composing controller class & method real names
        $params['_controllerClass']  = NS.$namespace.NS.'Controller'.NS.$params['_controller'].'Controller';
        $params['_controllerMethod'] = $params['_action'] . 'Action';
        $params['_controller']       = $params['_controllerClass'] . '::' . $params['_controllerMethod'];

        return $params;
    }
}
