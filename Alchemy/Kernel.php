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
            $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $requestParams['_controller'])));

            $this->controllerMeta['class'] = sprintf(
                '\\%s\\Controller\\%sController',
                $this->config->get('app.namespace'),
                $controllerName
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

            // if action() returns array data for template
            if (is_array($response)) {
                // returns view data
                $viewData = $response;
            }
            // if action() had not returned any value
            else if ($response === null) {
                // if the response object was built in action
                if (isset($controllerObject->response)) {
                    $response = $controllerObject->response;
                }
            }

            //  Response instance
            if (!($response instanceof Response)) {
                $response = new Response();
            }

        } catch (ResourceNotFoundException $e) {
            $response = new Response($e->getMessage(), 404);
        } catch (\Exception $e) {
            $response = new Response('<pre>'.$e->getMessage().'<br/>'.$e->getTraceAsString(), 500);
        }

        $view = $this->handleView(array_merge($viewData, (array) $controllerObject->view));

        if (!empty($view)) {
            $response->setContent($view->getOutput());
        }
        // dispatch a response event
        //$this->dispatcher->dispatch('response', new ResponseEvent($response, $request));


        return $response;
    }

    private function handleView($data)
    {
        $annotation  = new Annotations();
        $annotation->setDefaultAnnotationNamespace('\Alchemy\Annotation\\');
        $view = null;

        $annotationObjects = $annotation->getMethodAnnotationsObjects(
            $this->controllerMeta['class'],
            $this->controllerMeta['method']
        );

        if (isset($annotationObjects['View'])) {
            $template = $annotationObjects['View'][0]->template;

            if (empty($annotationObjects['View'][0]->engine)) {
                $engine = $this->config->get('templating.default_engine');
            } else {
                $engine = $annotationObjects['View'][0]->engine;
            }

            $viewClass = sprintf('\Alchemy\Adapter\\%sView', $engine);

            $view = new $viewClass($template);

            $view->setCacheDir($this->config->get('templating.cache_dir'));
            $view->setTemplateDir($this->config->get('templating.templates_dir'));
            $view->enableCache(false);

            foreach ($data as $key => $value) {
                $view->assign($key, $value);
            }
        }

        return $view;
    }
}
