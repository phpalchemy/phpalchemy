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

use Alchemy\Kernel\KernelEvents;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Mvc\ControllerResolver;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Lib\Util\Annotations;
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
    protected $matcher = null;
    protected $resolver = null;
    protected $dispatcher = null;
    protected $config = null;

    protected $view = null;
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

        $this->viewMeta['data'] = array();
    }

    /**
     * handle the http user request
     * @param  Request $request user hhtp request
     */
    public function handle(Request $request)
    {
        /*
         * Trigger KernelEvents::REQUEST using GetResponse Event
         *
         * This event can be used by an application to filter a request before
         * ever all kernel logic being executed
         */
        $event = new GetResponseEvent($this, $request);
        $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

        if ($event->hasResponse()) {
            $event = new FilterResponseEvent($this, $request, $event->getResponse());
            $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

            return $event->getResponse();
        }

        $reqParams = $this->matcher->match($request->getPathInfo());
        $reqParams = array_merge($reqParams, $request->query->all());
        $namespace = $this->config->get('app.namespace');

        $viewAnnotations = array();

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

            $ctrlrClass  = '\\'.$namespace.'\\Controller\\'.$reqParams['_controller'].'Controller';
            $ctrlrMethod = $reqParams['_action'] . 'Action';

            $reqParams['_controller'] = $ctrlrClass . '::' . $ctrlrMethod;

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

            // create Annotation object to read controller's method annotations
            $this->annotation->setDefaultAnnotationNamespace('\Alchemy\Annotation\\');

            // getting all annotations of controller's method
            $annotationObjects = $this->annotation->getMethodAnnotationsObjects($ctrlrClass, $ctrlrMethod);

            // check if a @view definition exists on method's annotations
            if (!empty($annotationObjects['View'])) {
                $viewAnnotations = $annotationObjects['View'];
            }

            $arguments = $this->resolver->getArguments($request, $controller);

            // create controllerEvent instance
            $controllerEvent = new ControllerEvent($this, $controller, $arguments, $request);

            // dispatch all KernelEvents::CONTROLLER events
            $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $controllerEvent);

            $controller = $controllerEvent->getController();

            // getting data for voew from ontroller.
            $data = (array) $controller[0]->view;

            // creating viewEvent instance
            $viewEvent = new ViewEvent(
                $this, $ctrlrClass, $ctrlrMethod, $data, $viewAnnotations, $this->config, $request
            );

            // dispatch all KernelEvents::VIEW events
            $this->dispatcher->dispatch(KernelEvents::VIEW, $viewEvent);

            // gets Response instance
            $response = $controller[0]->getResponse();

            // gets View instance
            $view = $viewEvent->getView();

            // if there is a view adapter instance, get its contents and set to response content
            if (!empty($view)) {
                $response->setContent($viewEvent->getView()->getOutput());
            }

        } catch (ResourceNotFoundException $e) {
            $response = new Response($e->getMessage(), 404);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage(), 500);
        }

        // dispatch a response event
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, new FilterResponseEvent($this, $request, $response));

        return $response;
    }
}
