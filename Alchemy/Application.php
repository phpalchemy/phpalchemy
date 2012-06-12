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
use Alchemy\Mvc\ControllerResolver;
use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;
use Alchemy\Util\Yaml;

use Symfony\Component\Routing;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

//use Symfony\Component\HttpKernel\HttpCache\HttpCache;
//use Symfony\Component\HttpKernel\HttpCache\Store;

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
class Application
{
    /**
     * Contains application name identifier
     * @var string
     */
    private $appName = '';

    /**
     * Contains application home absolute path
     * @var string
     */
    private $appPath = '';

    /**
     * Config object that contains all applications configuration needed
     * @var Config
     */
    private $config = null;

    /**
     * Construct application object
     * @param Config $config Contains all app configuration
     */
    public function __construct(Config $config)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        // Getting app configuration
        $this->config  = $config;
        $this->appPath = $config->getAppRootDir() . DS;

        // configuring and preparing application env.
        $this->configure();
        $this->prepare();

        // setting app classes to autoloader
        $classLoader = ClassLoader::getInstance();

        if (!$this->config->exists('app.namespace')) {
            throw new \Exception("Configuration Missing: namespace is not set on " . $config->getAppIniFile());
        }

        // registering the aplication namespace
        $classLoader->register(
            $this->appName,
            realpath($this->appPath . 'app') . DS,
            $config->get('app.namespace')
        );
    }

    /**
     * Run de application
     */
    public function run()
    {
        $request = Request::createFromGlobals();

        $context = new Routing\RequestContext();

        // $context->fromRequest($request); this function "fromRequest()" is available only on dev branch
        // Compatibility for v2.0.14 (next 6 lines)
        $context->setBaseUrl($request->getBaseUrl());
        $context->setMethod($request->getMethod());
        $context->setHost($request->getHost());
        $context->setScheme($request->getScheme());
        $context->setHttpPort($request->isSecure() ? $context->httpPort : $request->getPort());
        $context->setHttpsPort($request->isSecure() ? $request->getPort() : $context->getHttpsPort());
        /////////

        $routes     = $this->getRoutes();
        $matcher    = new Routing\Matcher\UrlMatcher($routes, $context);
        $resolver   = new ControllerResolver();
        $dispatcher = new EventDispatcher();

        //$dispatcher->addSubscriber(new Simplex\ContentLengthListener());

        $framework = new Kernel($dispatcher, $matcher, $resolver, $this->config);

        /**
         * The HttpCache class implements a fully-featured reverse proxy,
         * it implements HttpKernelInterface and wraps another HttpKernelInterface instance:
         */
        //$framework = new HttpCache($framework, new Store(__DIR__.'/../cache'));

        $response = $framework->handle($request);

        $response->send();
    }

    private function getRoutes()
    {
        if (file_exists($this->appPath . 'config' . DS . 'routes.php')) {
            $routes = include $this->appPath . 'config' . DS . 'routes.php';

            if (!($routes instanceof RouteCollection)) {
                throw new \InvalidArgumentException("Routes Collection is missing.");
            }
        } else if (file_exists($this->appPath . 'config' . DS . 'routes.yaml')) {
            $yaml   = new Yaml();
            $routes = new RouteCollection();

            $routesList = $yaml->loadFile($this->appPath . 'config' . DS . 'routes.yaml');

            foreach ($routesList as $name => $routeConfig) {
                $this->parseRoute($routes, $name, $routeConfig);
            }
        } else {
            throw new \Exception(
                "Application Error: No routes found for this app.\n" .
                "You need create & configure 'config/routes.yaml'"
            );
        }

        return $routes;
    }

    private function parseRoute(RouteCollection $collection, $name, $config)
    {
        $defaults     = isset($config['defaults'])     ? $config['defaults']     : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options      = isset($config['options'])      ? $config['options']      : array();

        if (!isset($config['pattern'])) {
            throw new \InvalidArgumentException(sprintf('You must define a "pattern" for the "%s" route.', $name));
        }

        $route = new Route($config['pattern'], $defaults, $requirements, $options);

        $collection->add($name, $route);
    }

    private function configure()
    {
        // if (!$this->config->exists('app.cache_dir')) {
        //     $this->config->set('app.cache_dir', $this->appPath . 'cache');
        // }

        // if (!$this->config->exists('templating.templates_dir')) {
        //     $this->config->set('templating.templates_dir', $this->appPath . 'app' . DS . 'views' . DS);
        // }

        // if (!$this->config->exists('tempating.default_engine')) {
        //     $this->config->set('tempating.default_engine', 'smarty');
        // }

        // if (!$this->config->exists('templating.cache_dir')) {
        //     $this->config->set('templating.cache_dir', $this->config->get('app.cache_dir'));
        // }

        // // fix paths
        // $this->config->set('app.cache_dir', rtrim($this->config->get('app.cache_dir'), DS) . DS);
        // $this->config->set('templating.cache_dir', rtrim($this->config->get('templating.cache_dir'), DS) . DS);
    }

    private function prepare()
    {
        // preparing directories

        if (!is_dir($this->config->get('app.cache_dir'))) {
            if (!is_writable(dirname($this->config->get('app.cache_dir')))) {
                throw new \Exception(
                    "Error: System can't create 'Application Cache Dir': " .
                    $this->config->get('app.cache_dir') . "\n" .
                    "Directory '" . dirname($this->config->get('app.cache_dir')) . "' is no writable."
                );
            }

            mkdir($this->config->get('app.cache_dir'));
        }

        if (!is_dir($this->config->get('templating.cache_dir'))) {
            if (!is_writable(dirname($this->config->get('templating.cache_dir')))) {
                throw new \Exception(
                    "Error: System can't create 'Application Cache Dir': " .
                    $this->config->get('app.cache_dir') . "\n" .
                    "Directory: '" . dirname($this->config->get('templating.cache_dir')) . "' is no writable."
                );
            }

            mkdir($this->config->get('templating.cache_dir'));
        }
    }
}
