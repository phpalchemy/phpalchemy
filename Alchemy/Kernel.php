<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy;

use Alchemy\Component\EventDispatcher\EventDispatcher;
use Alchemy\Mvc\ControllerResolver;

use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;

use Alchemy\Util\Annotations;

use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
class Kernel
{
    protected $matcher = null;
    protected $resolver = null;
    protected $dispatcher = null;
    protected $config = null;
    protected $controllerMeta = null;

    /**
     * Kernel constructor
     *
     * @param EventDispatcher     $dispatcher Event dispatcher object.
     * @param UrlMatcherInterface $matcher    UrlMatcher object.
     * @param ControllerResolver  $resolver   Controller resolver object.
     * @param Config              $config     Configuration object.
     */
    public function __construct(EventDispatcher $dispatcher, UrlMatcherInterface $matcher, ControllerResolver $resolver, Config $config)
    {
        $this->matcher    = $matcher;
        $this->resolver   = $resolver;
        $this->dispatcher = $dispatcher;
        $this->config     = $config;

        $this->controllerMeta = array();
    }

    /**
     * handle the http user request
     * @param  Request $request user hhtp request
     */
    public function handle(Request $request)
    {
        $viewData = array();

        try {
            $requestParams  = $this->matcher->match($request->getPathInfo());
            $requestParams  = array_merge($requestParams, $request->query->all());

            if (strpos($requestParams['_controller'], '_') === false) {
                $requestParams['_controller'] = ucfirst($requestParams['_controller']);
            } else {
                $requestParams['_controller'] = str_replace(
                    ' ', '', ucwords(str_replace('_', ' ', $requestParams['_controller']))
                );
            }

            if (strpos($requestParams['_action'], '_') !== false) {
                $tmp = str_replace(' ', '', ucwords(str_replace('_', ' ', $requestParams['_action'])));
                $requestParams['_action'] = strtolower(substr($tmp, 0, 1)) . substr($tmp, 1);
            }

            $this->controllerMeta['class'] = sprintf(
                '\\%s\\Controller\\%sController',
                $this->config->get('app.namespace'),
                $requestParams['_controller']
            );

            $this->controllerMeta['method'] = $requestParams['_action'] . 'Action';

            $requestParams['_controller'] = sprintf(
                '%s::%s',
                $this->controllerMeta['class'],
                $this->controllerMeta['method']
            );

            $request->attributes->add($requestParams);

            $controller = $this->resolver->getController($request);
            $arguments  = $this->resolver->getArguments($request, $controller);

            // call the controller action
            $response = call_user_func_array($controller, $arguments);
            $controllerObject = $controller[0];

            if (is_array($response)) { // if returns a array with data for template
                $viewData = $response;
            } elseif ($response === null) { // if doesn't return any value
                // if response object was built in action
                if (isset($controllerObject->response)) {
                    $response = $controllerObject->response;
                }
            }

            //  Response instance
            if (!($response instanceof Response)) {
                $response = new Response();
            }

            $view = $this->handleView(array_merge($viewData, (array) $controllerObject->view));

            if (!empty($view)) {
                $response->setContent($view->getOutput());
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

    private function handleView($data)
    {
        $annotation   = new Annotations();
        $templateDir  = $this->config->get('app.views_dir') . DS;
        $cacheDir     = $this->config->get('templating.cache_dir') . DS;
        $cacheEnabled = $this->config->get('templating.cache_enabled');
        $extension    = $this->config->get('templating.extension');
        $view         = null;

        $annotation->setDefaultAnnotationNamespace('\Alchemy\Annotation\\');

        $annotationObjects = $annotation->getMethodAnnotationsObjects(
            $this->controllerMeta['class'],
            $this->controllerMeta['method']
        );

        if (isset($annotationObjects['View'])) {
            $template = $annotationObjects['View'][0]->template;
            $engine   = $this->config->get('templating.default_engine');

            // setting template engine
            if (!empty($annotationObjects['View'][0]->engine)) {
                $engine = $annotationObjects['View'][0]->engine;
            }

            if (empty($template)) { //template filename empty
                // use the controller class and method names to compose the template path
                // removing ...Controller & ..Action from their names
                $nsSepPos  = strrpos($this->controllerMeta['class'], '\\');
                $template  = substr($this->controllerMeta['class'], $nsSepPos + 1, -10) . DS;
                $template .= substr($this->controllerMeta['method'], 0, -6);
            }

            // file extension validation
            if (strpos($template, '.') === false && !empty($extension)) {
                $template .= '.' . $extension;
            }

            // if the view annotation was defined bug the template doesn't exist
            if (file_exists($templateDir . $template)) { // throws an exception

            } elseif (file_exists($template)) {

            } else {
                throw new \Exception("Error, File Not Found: tamplate file doesn't exist: '$template'");
            }

            $viewClass = sprintf('\Alchemy\Adapter\\%sView', ucfirst($engine));

            $view = new $viewClass($template);

            $view->setCacheDir($cacheDir);
            $view->setTemplateDir($templateDir);
            $view->enableCache($cacheEnabled);

            foreach ($data as $key => $value) {
                $view->assign($key, $value);
            }
        }

        return $view;
    }
}
