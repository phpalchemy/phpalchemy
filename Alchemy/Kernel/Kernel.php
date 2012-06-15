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

use Alchemy\Kernel\Event\ControllerEvent;
use Alchemy\Kernel\Event\ViewEvent;

use Alchemy\Kernel\KernelEvents;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Mvc\ControllerResolver;
use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;
use Alchemy\Config;

use Alchemy\Component\Routing\Exception\ResourceNotFoundException;
use Alchemy\Component\Routing\Mapper;

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
    protected $matcher = null;
    protected $resolver = null;
    protected $dispatcher = null;
    protected $config = null;

    protected $view = null;
    //protected $viewMeta = array();
    protected $controller = null;
    //protected $controllerMeta = array();

    public $request = null;

    /**
     * Kernel constructor
     *
     * @param EventDispatcher     $dispatcher Event dispatcher object.
     * @param UrlMatcherInterface $matcher    UrlMatcher object.
     * @param ControllerResolver  $resolver   Controller resolver object.
     * @param Config              $config     Configuration object.
     */
    public function __construct(EventDispatcher $dispatcher, Mapper $matcher, ControllerResolver $resolver, Config $config)
    {
        $this->matcher    = $matcher;
        $this->resolver   = $resolver;
        $this->dispatcher = $dispatcher;
        $this->config     = $config;

        $this->viewMeta['data'] = array();
    }

    /**
     * handle the http user request
     * @param  Request $request user hhtp request
     */
    public function handle(Request $request)
    {
        $reqParams = $this->matcher->match($request->getPathInfo());
        $reqParams = array_merge($reqParams, $request->query->all());
        $namespace = $this->config->get('app.namespace');

        $controllerMeta['class']  = '';
        $controllerMeta['method'] = '';

        try {
            if (strpos($reqParams['_controller'], '_') === false) {
                $reqParams['_controller'] = ucfirst($reqParams['_controller']);
            } else {
                $reqParams['_controller'] = str_replace(
                    ' ', '', ucwords(str_replace('_', ' ', $reqParams['_controller']))
                );
            }

            if (strpos($reqParams['_action'], '_') !== false) {
                $tmp = str_replace(' ', '', ucwords(str_replace('_', ' ', $reqParams['_action'])));
                $reqParams['_action'] = strtolower(substr($tmp, 0, 1)) . substr($tmp, 1);
                unset($tmp);
            }

            $controllerMeta['class']  = '\\'.$namespace.'\\Controller\\'.$reqParams['_controller'].'Controller';
            $controllerMeta['method'] = $reqParams['_action'] . 'Action';
            $reqParams['_controller'] = $controllerMeta['class'].'::'.$controllerMeta['method'];

            $request->attributes->add($reqParams);

            // load controller
            $controller = $this->resolver->getController($request);

            if ($controller === false) {
                throw new NotFoundHttpException(sprintf(
                    'Unable to find the controller for path "%s". Maybe you ' .
                    'forgot to add the matching route in your routing configuration?',
                    $request->getPathInfo()
                ));
            }

            $arguments = $this->resolver->getArguments($request, $controller);

            // creating controllerEvent instance
            $controllerEvent = new ControllerEvent($this, $controller, $arguments, $request);

            // dispatch all KernelEvents::CONTROLLER events
            $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $controllerEvent);

            $controller = $controllerEvent->getController();

            $data = (array) $controller[0]->view;
            // creating viewEvent instance
            $viewEvent = new ViewEvent($this, $data, $controllerMeta, $this->config, $request);

            // dispatch all KernelEvents::VIEW events
            $this->dispatcher->dispatch(KernelEvents::VIEW, $viewEvent);

            // getting Response instance
            $response = $controller[0]->getResponse();
            $view = $viewEvent->getView();

            if (!empty($view)) {
                $response->setContent($viewEvent->getView()->getOutput());
            }
        } catch (ResourceNotFoundException $e) {
            $response = new Response($e->getMessage(), 404);
        } catch (\Exception $e) {
            $response = new Response('<pre>'.$e->getMessage().'<br/>'.$e->getTraceAsString(), 500);
        }
        // dispatch a response event
        //$this->dispatcher->dispatch('response', new ResponseEvent($response, $request));


        return $response;
    }

    // protected function handleController()
    // {
    //     $controllerResolved = $this->resolver->getController($this->request);
    //     $arguments  = $this->resolver->getArguments($this->request, $controllerResolved);

    //     // call the controller action
    //     $response = call_user_func_array($controllerResolved, $arguments);
    //     $this->controller = $controllerResolved[0];

    //     if (is_array($response)) { // if returns a array with data for template
    //         $this->viewMeta['data'] = $response;
    //     } elseif ($response === null) { // if doesn't return any value
    //         // if response object was built in action
    //         if (isset($controller->response)) {
    //             $response = $controller->response;
    //         }
    //     }

    //     return $response instanceof Response ? $response : new Response();
    // }

    // protected function handleView($data)
    // {
    //     $annotation = new Annotations();
    //     $annotation->setDefaultAnnotationNamespace('\Alchemy\Annotation\\');

    //     $annotationObjects = $annotation->getMethodAnnotationsObjects(
    //         $this->controllerMeta['class'],
    //         $this->controllerMeta['method']
    //     );

    //     // if there isn't a @view definition controller annotations
    //     if (!isset($annotationObjects['View'])) {
    //         return $this->view; // just return $this->view (empty)
    //     }

    //     // getting 'view' configuration
    //     $conf = new \StdClass();
    //     $conf->template     = $annotationObjects['View'][0]->template;
    //     $conf->engine       = $this->config->get('templating.default_engine');
    //     $conf->templateDir  = $this->config->get('app.views_dir') . DS;
    //     $conf->cacheDir     = $this->config->get('templating.cache_dir') . DS;
    //     $conf->cacheEnabled = $this->config->get('templating.cache_enabled');
    //     $conf->extension    = $this->config->get('templating.extension');
    //     $conf->charset      = $this->config->get('templating.charset');
    //     $conf->debug        = $this->config->get('templating.debug');

    //     // setting template engine
    //     if (!empty($annotationObjects['View'][0]->engine)) {
    //         $conf->engine = $annotationObjects['View'][0]->engine;
    //     }

    //     if (empty($conf->template)) { //template filename empty
    //         // use the controller class and method names to compose the template path
    //         // removing ...Controller & ..Action from their names
    //         $nsSepPos  = strrpos($this->controllerMeta['class'], '\\');
    //         $conf->template  = substr($this->controllerMeta['class'], $nsSepPos + 1, -10) . DS;
    //         $conf->template .= substr($this->controllerMeta['method'], 0, -6);
    //     }

    //     // file extension validation
    //     if (strpos($conf->template, '.') === false && !empty($conf->extension)) {
    //         $conf->template .= '.' . $conf->extension;
    //     }

    //     // if the view annotation was defined bug the template doesn't exist
    //     if (file_exists($conf->templateDir . $conf->template)) { // throws an exception

    //     } elseif (file_exists($conf->template)) {

    //     } else {
    //         throw new \Exception("Error, File Not Found: template file doesn't exist: '{$conf->template}'");
    //     }

    //     $viewClass = '\Alchemy\Adapter\\'.ucfirst($conf->engine).'View';

    //     // building & configuring view object
    //     if (!class_exists($viewClass)) {
    //         throw new Exception("Error Processing: Template Engine is not available: '{$conf->engine}'");
    //     }

    //     $this->view = new $viewClass($conf->template);

    //     $this->view->enableDebug($conf->debug);
    //     $this->view->enableCache($conf->cacheEnabled);
    //     $this->view->setCacheDir($conf->cacheDir);
    //     $this->view->setTemplateDir($conf->templateDir);
    //     $this->view->setCharset($conf->charset);

    //     // setting data to be used by template
    //     $this->view->assign($data);

    //     // unset temporal view configuration object
    //     unset($conf);

    //     return $this->view->getOutput();
    // }
}
