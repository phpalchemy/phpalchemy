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
use Alchemy\Component\ClassLoader;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Kernel\EventListener;
use Alchemy\Kernel\Kernel;

use Alchemy\Mvc\ControllerResolver;
use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;
use Alchemy\Util\Yaml;

use Alchemy\Component\Routing\Mapper;
use Alchemy\Component\Routing\Route;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Application
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Application extends \DependencyInjectionContainer implements KernelInterface, EventSubscriberInterface
{
    /**
     * Contains application name identifier
     * @var string
     */
    protected $appName = '';

    /**
     * Contains application root directory
     * @var string
     */
    protected $appRootDir = '';

    /**
     * Application Namespace
     * @var string
     */
    protected $namespace = '';

    /**
     * Config object that contains all applications configuration needed
     * @var Config
     */
    //protected $config = null;


    /**
     * Construct application object
     * @param Config $config Contains all app configuration
     */
    public function __construct2(Config $config)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        // getting app configuration
        $this->config     = $config;
        $this->appRootDir = realpath($config->getAppRootDir()) . DS;

        $this->namespace  = $config->get('app.namespace');

        // setting app classes to autoloader
        $classLoader = ClassLoader::getInstance();

        if (!$this->config->exists('app.namespace')) {
            throw new \Exception("Configuration Missing: namespace is not set on " . $config->getAppIniFile());
        }

        // registering the aplication namespace to SPL ClassLoader
        $classLoader->register(
            $this->appName,
            $config->get('app.app_dir') . DS,
            $config->get('app.namespace')
        );
    }

    public function __construct($conf)
    {
        $app = $this;

        $this['logger'] = null;

        $this['config'] = $this->share(function () use ($conf){
            return new Config($conf);
        });

        $this['autoloader'] = $this->share(function () {
            return new ClassLoader();
        });

        $this['yaml'] = $this->share(function () {
            return new Yaml();
        });

        $this['mapper'] = $this->share(function () {
            return new Mapper();
        });

        // $this['exception_handler'] = $this->share(function () {
        //     return new ExceptionHandler();
        // });

        $this['dispatcher'] = $this->share(function () use ($app) {
            $dispatcher = new EventDispatcher();
            $dispatcher->addSubscriber($app);

            // $urlMatcher = new LazyUrlMatcher(function () use ($app) {
            //     return $app['url_matcher'];
            // });
            // $dispatcher->addSubscriber(new RouterListener($urlMatcher, $app['logger']));
            // $dispatcher->addSubscriber(new LocaleListener($app['locale'], $urlMatcher));

            // subscribing events
            $dispatcher->addSubscriber(new EventListener\ControllerListener());
            $dispatcher->addSubscriber(new EventListener\ViewHandlerListener());
            $dispatcher->addSubscriber(new EventListener\ResponseListener());

            return $dispatcher;
        });

        $this['resolver'] = $this->share(function () use ($app) {
            return new ControllerResolver($app, $app['logger']);
        });

        $this['kernel'] = $this->share(function () use ($app) {
            //return new Kernel($app['dispatcher'], $app['resolver']);
            return new Kernel($app['dispatcher'], $app['mapper'], $app['resolver'], $app['config']);
        });

        // $this['route_before_middlewares_trigger'] = $this->protect(function (GetResponseEvent $event) use ($app) {
        //     $request = $event->getRequest();
        //     $routeName = $request->attributes->get('_route');
        //     if (!$route = $app['routes']->get($routeName)) {
        //         return;
        //     }

        //     foreach ((array) $route->getOption('_before_middlewares') as $callback) {
        //         $ret = call_user_func($callback, $request);
        //         if ($ret instanceof Response) {
        //             $event->setResponse($ret);

        //             return;
        //         } elseif (null !== $ret) {
        //             throw new \RuntimeException(sprintf('A before middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
        //         }
        //     }
        // });

        // $this['route_after_middlewares_trigger'] = $this->protect(function (FilterResponseEvent $event) use ($app) {
        //     $request = $event->getRequest();
        //     $routeName = $request->attributes->get('_route');
        //     if (!$route = $app['routes']->get($routeName)) {
        //         return;
        //     }

        //     foreach ((array) $route->getOption('_after_middlewares') as $callback) {
        //         $response = call_user_func($callback, $request, $event->getResponse());
        //         if ($response instanceof Response) {
        //             $event->setResponse($response);
        //         } elseif (null !== $response) {
        //             throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
        //         }
        //     }
        // });

        // $this['request_error'] = $this->protect(function () {
        //     throw new \RuntimeException('Accessed request service outside of request scope. Try moving that call to a before handler or controller.');
        // });

        // $this['request'] = $this['request_error'];

        // $this['request.http_port'] = 80;
        // $this['request.https_port'] = 443;
        // $this['debug'] = false;
        // $this['charset'] = 'UTF-8';
        // $this['locale'] = 'en';
        //
        // registering the aplication namespace to SPL ClassLoader
        $this['autoloader']->register(
            $this['config']->get('app.name'),
            $this['config']->get('app.app_dir') . DIRECTORY_SEPARATOR,
            $this['config']->get('app.namespace')
        );
    }

    /**
     * Run de application
     */
    public function run2(Request $request = null)
    {
        if (empty($request)) {
            $request = Request::createFromGlobals();
        }

        $dispatcher = new EventDispatcher();
        $resolver   = new ControllerResolver();
        $mapper     = $this->loadMapper();

        // Create a Kernel instance to manage the application
        $framework = new Kernel($dispatcher, $mapper, $resolver, $this->config);

        // retrieve the response object from kernel's handler
        $response  = $framework->handle($request);

        // send response to the client
        $response->send();
    }

    public function run(Request $request = null)
    {
        if (empty($request)) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();
    }

    public function handle(Request $request)
    {
        //$this->beforeDispatched = false;

        //$current = HttpKernelInterface::SUB_REQUEST === $type ? $this['request'] : $this['request_error'];

        $this['request'] = $request;

        $this->loadRoutes();

        $response = $this['kernel']->handle($request);

        //$this['request'] = $current;

        return $response;
    }

    private function loadMapper2()
    {
        if (file_exists($this->appRootDir . 'config' . DS . 'routes.php')) {
            $mapper = include $this->appRootDir . 'config' . DS . 'routes.php';

            if (!($mapper instanceof Mapper)) {
                throw new \InvalidArgumentException("Routing Mapper is missing.");
            }
        } else if (file_exists($this->appRootDir . 'config' . DS . 'routes.yaml')) {
            $yaml   = new Yaml();
            $mapper = new Mapper();

            $routesList = $yaml->loadFile($this->appRootDir . 'config' . DS . 'routes.yaml');

            foreach ($routesList as $rname => $rconf) {
                $defaults     = isset($rconf['defaults'])     ? $rconf['defaults']     : array();
                $requirements = isset($rconf['requirements']) ? $rconf['requirements'] : array();
                $options      = isset($rconf['options'])      ? $rconf['options']      : array();

                if (!isset($rconf['pattern'])) {
                    throw new \InvalidArgumentException(sprintf('You must define a "pattern" for the "%s" route.', $name));
                }

                $mapper->connect(
                    $rname,
                    new Route($rconf['pattern'], $defaults, $requirements, $options)
                );
            }
        } else {
            throw new \Exception(
                "Application Error: No routes found for this app.\n" .
                "You need create & configure 'config/routes.yaml'"
            );
        }

        return $mapper;
    }

    public function loadRoutes()
    {
        $config = $this['config'];

        if (file_exists($config->get('app.root_dir') . '/config' . DS . 'routes.php')) {
            $mapper = include $config->get('app.root_dir') . '/config' . DS . 'routes.php';

            if (!($mapper instanceof Mapper)) {
                throw new \InvalidArgumentException("Routing Mapper is missing.");
            }
        } else if (file_exists($config->get('app.root_dir') . '/config' . DS . 'routes.yaml')) {
            $routesList = $this['yaml']->loadFile($config->get('app.root_dir') . '/config' . DS . 'routes.yaml');

            foreach ($routesList as $rname => $rconf) {
                $defaults     = isset($rconf['defaults'])     ? $rconf['defaults']     : array();
                $requirements = isset($rconf['requirements']) ? $rconf['requirements'] : array();
                $options      = isset($rconf['options'])      ? $rconf['options']      : array();

                if (!isset($rconf['pattern'])) {
                    throw new \InvalidArgumentException(sprintf('You must define a "pattern" for the "%s" route.', $name));
                }

                $this['mapper']->connect(
                    $rname,
                    new Route($rconf['pattern'], $defaults, $requirements, $options)
                );
            }
        } else {
            throw new \Exception(
                "Application Error: No routes found for this app.\n" .
                "You need create & configure 'config/routes.yaml'"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array();
        //
        return array(
            KernelEvents::REQUEST    => array(
                array('onEarlyKernelRequest', 256),
                array('onKernelRequest')
            ),
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE   => 'onKernelResponse',
            KernelEvents::EXCEPTION  => array('onKernelException', -10),
            KernelEvents::TERMINATE  => 'onKernelTerminate',
            KernelEvents::VIEW       => array('onKernelView', -10),
        );
    }
}
