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

use Alchemy\Adapter\NotojAnnotations;
use Alchemy\Annotation\Reader\Adapter\NotojReader;
use Alchemy\Component\Annotations\Annotations;
use Alchemy\Component\ClassLoader\ClassLoader;
use Alchemy\Component\EventDispatcher\EventDispatcher;
use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Component\Routing\Mapper;
use Alchemy\Component\Routing\Route;
use Alchemy\Component\UI\Parser;
use Alchemy\Component\UI\ReaderFactory;
use Alchemy\Component\UI\Engine;
use Alchemy\Component\Yaml\Yaml;
use Alchemy\Exception\Handler;
use Alchemy\Kernel\EventListener;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Kernel\Kernel;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Mvc\ControllerResolver;

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
class Application extends \DiContainer implements KernelInterface, EventSubscriberInterface
{
    /**
     * Construct application object
     * @param Config $config Contains all app configuration
     */
    public function init($conf = array())
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        $app = $this;

        $this['logger'] = null;

        $this['config'] = $this->share(function () use ($conf){
            $config = new Config();
            $config->load($conf);

            return $config;
        });

        // load configuration ini files
        $this->loadAppConfigurationFiles();

        // apply configurated php settings
        $this->applyPhpSettings();

        $this['autoloader'] = $this->share(function () {
            return new ClassLoader();
        });

        $this['yaml'] = $this->share(function () {
            return new Yaml();
        });

        $this['annotation'] = $this->share(function () use ($app) {
            return new NotojReader();
            //return new Annotations($app['config']);
        });

        $this['mapper'] = $this->share(function () use ($app){
            $mapper = new Mapper();
            $config = $app['config'];

            if (file_exists($config->get('app.root_dir') . DS .'config' . DS . 'routes.php')) {
                $routesInc = include $config->get('app.root_dir') . DS .'config' . DS . 'routes.php';

                if ($routesInc instanceof Mapper) {
                    $routesInc = $mapper;
                } elseif ($routesInc instanceof \Closure) {
                    $addRoutes($mapper);
                } else {
                    throw new \InvalidArgumentException("ERROR: file routes.php does not return valid data.");
                }
            } elseif (file_exists($config->get('app.root_dir') . DS . 'config' . DS . 'routes.yaml')) {
                $routesList = $app['yaml']->load($config->get('app.root_dir') . DS . 'config' . DS . 'routes.yaml');

                foreach ($routesList as $rname => $rconf) {
                    $defaults     = isset($rconf['defaults'])     ? $rconf['defaults']     : array();
                    $requirements = isset($rconf['requirements']) ? $rconf['requirements'] : array();
                    $type         = isset($rconf['type'])         ? $rconf['type']         : '';
                    $resourcePath = isset($rconf['resourcePath']) ? $rconf['resourcePath'] : '';

                    if (! array_key_exists('pattern', $rconf)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Runtime Error: Route pattern for "%s" route is missing.', $rname
                        ));
                    }

                    $mapper->connect(
                        $rname,
                        new Route($rconf['pattern'], $defaults, $requirements, $type, $resourcePath
                    ));
                }
            } else {
                throw new \Exception(
                    "Application Error: No routes found for this app.\n" .
                    "You need create & configure 'config/routes.yaml'"
                );
            }

            $mapper->connect(
                'asset_resource',
                new Route(
                    '/assets/{filename}',
                    array(
                        '_controller' => 'assetsDispatcher',
                        '_action' => 'index',
                        '_type' => 'x-asset-request'
                    ),
                    array('filename' => '(.+)')
                )
            );

            return $mapper;
        });

        //TODO $this['exception_handler'] = $this->share(function () {
        //     return new ExceptionHandler();
        // });

        $this['dispatcher'] = $this->share(function () use ($app) {
            $dispatcher = new EventDispatcher();
            $dispatcher->addSubscriber($app);

            // subscribing events
            $dispatcher->addSubscriber(new EventListener\ResponseListener($app['config']->get('templating.charset')));
            //TODO $dispatcher->addSubscriber(new LocaleListener($app['locale'], $urlMatcher));

            return $dispatcher;
        });

        $this['resolver'] = $this->share(function () use ($app) {
            return new ControllerResolver($app, $app['logger']);
        });

        $this['ui_reader_factory'] = $this->share(function () use ($app) {
            return new ReaderFactory();
        });

        $this['ui_parser'] = $this->share(function () use ($app) {
            return new Parser();
        });

        $this['ui_engine'] = $this->share(function () use ($app) {
            return new Engine(
                $app['ui_reader_factory'],
                $app['ui_parser']
            );
        });

        $this['kernel'] = $this->share(function () use ($app) {
            return new Kernel(
                $app['dispatcher'],
                $app['mapper'],
                $app['resolver'],
                $app['config'],
                $app['annotation'],
                $app['ui_engine']
            );
        });

        // registering the aplication namespace to SPL ClassLoader
        $this['autoloader']->register(
            $this['config']->get('app.name'),
            $this['config']->get('app.root_dir'),
            $this['config']->get('app.namespace')
        );

        // registering the aplication Extend folder to SPL ClassLoader (if folder was created)
        if (is_dir($this['config']->get('app.root_dir') . DIRECTORY_SEPARATOR . 'Extend')) {
            $this['autoloader']->register(
                $this['config']->get('app.name') . '/Extend',
                $this['config']->get('app.root_dir') . DIRECTORY_SEPARATOR . 'Extend'
            );
        }

        $this['exception_handler'] = $this->share(function() use ($app) {
            return new ExceptionHandler;
        });

        $this->protect('logger');
        $this->protect('config');
        $this->protect('autoloader');
        $this->protect('yaml');
        $this->protect('annotation');
        $this->protect('mapper');
        $this->protect('logger');
        $this->protect('dispatcher');
        $this->protect('resolver');
        $this->protect('ui_reader_factory');
        $this->protect('ui_parser');
        $this->protect('ui_engine');
        $this->protect('resolver');
        $this->protect('kernel');
        $this->protect('autoloader');
        $this->protect('exception_handler');
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     */
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        $provider->register($this);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Run de application
     */
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
        $this['request'] = $request;
        $response = $this['kernel']->handle($request);

        return $response;
    }

    public function loadAppConfigurationFiles()
    {
        if (isset($conf['phpalchemy']['root_dir'])) {
            $this['config']->set('phpalchemy.root_dir', $conf['phpalchemy']['root_dir']);
            unset($conf['phpalchemy']['root_dir']);
        }

        if (isset($conf['app']['root_dir'])) {
            $this['config']->set('app.root_dir', $conf['app']['root_dir']);
            unset($conf['app']['root_dir']);
        }

        if (!$this['config']->exists('phpalchemy.root_dir')) {
            throw new \Exception("Configuration Missing: 'phpalchemy.root_dir' is not defined.");
        }

        if (!$this['config']->exists('app.root_dir')) {
            throw new \Exception("Configuration Missing: 'app.root_dir' is not defined.");
        }

        // load defaults configurations
        $this['config']->load($this['config']->get('phpalchemy.root_dir').DS.'config'.DS.'defaults.application.ini');

        //load app. configuration ini file
        $this['config']->load($this->getAppIniFile());


        //load env. configuration ini file
        $this['config']->load($this->getEnvIniFile());
    }

    public function setAppIniFile($filename)
    {
        $this['config']->set('app.app_ini_file', $filename);
    }

    public function getAppIniFile()
    {
        if (!$this['config']->exists('app.app_ini_file')) {
            $this->setAppIniFile($this['config']->get('app.root_dir') . DS . 'application.ini');
        }

        return $this['config']->get('app.app_ini_file');
    }

    public function setEnvIniFile($filename)
    {
        $this['config']->set('app.env_ini_file', $filename);
    }

    public function getEnvIniFile()
    {
        if (!$this['config']->exists('app.env_ini_file')) {
            $this->setEnvIniFile($this['config']->get('app.config_dir') . DS . 'env.ini');
        }

        return $this['config']->get('app.env_ini_file');
    }

    protected function applyPhpSettings()
    {
        $phpIniSettings = $this['config']->getSection('php.ini_set.');

        foreach ($phpIniSettings as $name => $value) {
            ini_set($name, $value);
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

