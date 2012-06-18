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
use Alchemy\Kernel\EventListener;
use Alchemy\Kernel\Kernel;
use Alchemy\Mvc\ControllerResolver;
use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;
use Alchemy\Util\Yaml;

use Alchemy\Component\Routing\Mapper;
use Alchemy\Component\Routing\Route;

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
    protected $config = null;


    /**
     * Construct application object
     * @param Config $config Contains all app configuration
     */
    public function __construct(Config $config)
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

    /**
     * Run de application
     */
    public function run(Request $request = null)
    {
        if (empty($request)) {
            $request = Request::createFromGlobals();
        }

        $dispatcher = new EventDispatcher();
        $resolver   = new ControllerResolver();
        $mapper     = $this->loadMapper();

        // subscribing evenyts
        $dispatcher->addSubscriber(new EventListener\ControllerListener());
        $dispatcher->addSubscriber(new EventListener\ViewHandlerListener());
        $dispatcher->addSubscriber(new EventListener\ResponseListener());

        // Create a Kernel instance to manage the application
        $framework = new Kernel($dispatcher, $mapper, $resolver, $this->config);

        // retrieve the response object from kernel's handler
        $response  = $framework->handle($request);

        // send response to the client
        $response->send();
    }

    private function loadMapper()
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
}
