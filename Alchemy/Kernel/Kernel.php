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

use Alchemy\Application;
use Alchemy\Annotation\Reader\Reader;
use Alchemy\Annotation\ViewAnnotation;
use Alchemy\Config;
use Alchemy\Component\EventDispatcher\EventDispatcher;
use Alchemy\Component\Http;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Component\Http\JsonResponse;
use Alchemy\Component\Routing\Exception\ResourceNotFoundException;
use Alchemy\Component\Routing\Mapper;
use Alchemy\Component\UI\Engine;
use Alchemy\Component\WebAssets\Bundle;
use Alchemy\Exception;
use Alchemy\Kernel\Event\GetResponseEvent;
use Alchemy\Kernel\Event\FilterResponseEvent;
use Alchemy\Kernel\Event\ControllerEvent;
use Alchemy\Kernel\Event\ViewEvent;
use Alchemy\Kernel\Event\ResponseEvent;
use Alchemy\Kernel\Event\FilterControllerEvent;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Mvc\ControllerResolver;


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
    /**
     * Routing mapper object
     * @var \Alchemy\Component\Routing\Mapper
     */
    protected $mapper    = null;

    /**
     * Controller resolver object
     * @var \Alchemy\Mvc\ControllerResolver
     */
    protected $resolver   = null;

    /**
     * Event dispatcher object
     * @var \Alchemy\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    /**
     * Configuration object
     * @var \Alchemy\Config
     */
    protected $config     = null;

    /**
     * Annotations Reader object
     * @var \Alchemy\Annotation\Reader\Reader
     */
    protected $annotationReader = null;

    /**
     * UI Generator Engine
     * @var \Alchemy\Component\UI\Engine
     */
    protected $uiEngine   = null;

    /**
     * Request handler object
     * @var \Alchemy\Component\Http\Request
     */
    public $request = null;

    /**
     * Assets handler object
     * @var \Alchemy\Component\WebAssets\Bundle
     */
    public $assetsHandler = null;

    /**
     * Kernel constructor
     *
     * @param \Alchemy\Component\EventDispatcher\EventDispatcher $dispatcher Event dispatcher object.
     * @param \Alchemy\Component\Routing\Mapper $mapper Routing\Mapper object.
     * @param \Alchemy\Mvc\ControllerResolver $resolver Controller resolver object.
     * @param \Alchemy\Config $config Configuration object.
     * @param \Alchemy\Annotation\Reader\Reader $annotationReader
     * @param \Alchemy\Component\UI\Engine $uiEngine UI\Engine object.
     * @param \Alchemy\Component\WebAssets\Bundle $assetsHandler
     */
    public function __construct(
        EventDispatcher $dispatcher,
        Mapper $mapper,
        ControllerResolver $resolver,
        Config $config,
        Reader $annotationReader,
        Engine $uiEngine,
        Bundle $assetsHandler
    ) {
        $this->mapper     = $mapper;
        $this->resolver   = $resolver;
        $this->dispatcher = $dispatcher;
        $this->config     = $config;
        $this->annotationReader = $annotationReader;
        $this->uiEngine   = $uiEngine;
        $this->assetsHandler = $assetsHandler;

        // Some configurations for developments environments
        if ($this->config->get('env.type') !== 'dev') {
            $this->annotationReader->setStrict(false);
        }

        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    }

    /**
     * handle the http user request
     *
     * @param \Alchemy\Application $app
     * @param \Alchemy\Component\Http\Request $request user http request
     * @return \Alchemy\Component\Http\JsonResponse|\Alchemy\Component\Http\Response
     */
    public function handle(Request $request, Application $app = null)
    {
        $this->request = $request;

        try {
            // init services providers
            /** @var \Alchemy\Service\ServiceProviderInterface $providers */
            $providers = $app->getServiceProviders();

            foreach ($providers as $provider) {
                $provider->init($app);
            }


            /*
             * "EVENT" KernelEvents::REQUEST using GetResponse Event
             *
             * This event can be used by an application to filter a request before
             * ever all kernel logic being executed, listeners for this event should
             * be registered outside framework logic (should be on application logic).
             */
            if ($this->dispatcher->hasListeners(KernelEvents::REQUEST)) {
                $event = new GetResponseEvent($this, $request, $app);
                $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

                if ($event->hasResponse()) {
                    //var_dump($event->getResponse()); die("ee");
                    $event = new FilterResponseEvent($this, $request, $event->getResponse());
                    $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

                    return $event->getResponse();
                }
            }

            /*
             * try match the url request with a defined route.
             * it any route match the current url a ResourceNotFoundException
             * will be thrown.
             */
            $params = $this->mapper->match($request);
            $uriParams = $params["params"];

            // prepare request params.
            $params = $this->prepareRequestParams(array($params["mapped"], $request->query->all(), $request->request->all()));

            // add prepared params to request as attributes.
            $request->attributes->add($params);

            // resolve controller
            try {
                $controller = $this->resolver->getController($request);
            } catch (\Exception $exception) {
                /*
                 * Detailed exception types are only for development environments
                 * if the current is a non dev environment just overrides the current exception with
                 * a ResourceNotFoundException exception
                 */
                if ($this->config->get('env.type') !== 'dev') {
                    $exception = new ResourceNotFoundException($request->getPathInfo());
                }

                throw $exception;
            }

            if (! $controller) {
                throw new ResourceNotFoundException($request->getPathInfo());
            }

            // setting default annotations namespace
            $this->annotationReader->setDefaultNamespace('\Alchemy\Annotation\\');
            // seeting annotation reader target
            $this->annotationReader->setTarget($params['_controllerClass'], $params['_controllerMethod']);

            $arguments = $this->resolver->getArguments($app, $request, $controller);

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

            // check returned value by method is a array
            if (is_array($response)) {
                // set controller view data object
                foreach ($response as $key => $value) {
                    $controller[0]->view->$key = $value;
                }
            }

            // getting controller's data.
            $controllerData = (array) $controller[0]->view;

            if (! ($response instanceof Response || $response instanceof JsonResponse)) {
                if ($this->annotationReader->getAnnotation('JsonResponse')) {

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

            // handling view
            $view = $this->handleView(
                $params['_controllerClass'],
                $params['_controllerMethod'],
                $controllerData,
                $this->annotationReader->getAnnotation('View')
            );

            // handling meta ui
            $view = $this->handleMetaUi(
                $controllerData,
                $this->annotationReader->getAnnotation('ServeUi'),
                $view,
                $request
            );

            // if there is a view adapter instance, get its contents and set to response content
            if (! empty($view)) {
                foreach ($uriParams as $keyParam => $valParam) {
                    $view->assign($keyParam, $valParam);
                }

                //"EVENT" VIEW dispatch all KernelEvents::VIEW events
                if ($this->dispatcher->hasListeners(KernelEvents::VIEW)) {
                    $event = new ViewEvent($this, $view, $request, $this->annotationReader, $app);
                    $this->dispatcher->dispatch(KernelEvents::VIEW, $event);
                    $view = $event->getView();
                }

                $response->setContent($view->getOutput());
            }

        } catch (ResourceNotFoundException $e) {
            $exceptionHandler = new Exception\Handler();

            if ($request->isXmlHttpRequest()) {
                $responseContent = $e->getMessage();
            } else {
                $responseContent = $exceptionHandler->getOutput($e);
            }

            $response = new Response($responseContent, 404);
        } catch (\Exception $e) {
//            echo $e->getMessage();
//            echo "<br><pre>";
//            echo $e->getTraceAsString();
//            die;

            $exceptionHandler = new Exception\Handler();

            if ($request->isXmlHttpRequest()) {
                $responseContent = $e->getMessage();
            } else {
                $responseContent = $exceptionHandler->getOutput($e);
            }

            $response = new Response($responseContent, 500);
        }

        if ($this->annotationReader->hasTarget()) {
            $responseAnnotation = $this->annotationReader->getAnnotation('Response');
        } else {
            $responseAnnotation = null;
        }

        // dispatch a response event
        $this->dispatcher->dispatch(
            KernelEvents::RESPONSE,
            new FilterResponseEvent($this, $request, $response, $responseAnnotation)
        );

        return $response;
    }

    /**
     * Handle a asset request
     * @param  array  $params array containg all data
     */
    protected function handleAssetRequest(array $params)
    {
        $response = new Response();

        $assetsConf = $this->config->getSection('asset_resolv');
        $assetsDir  = $this->config->get('app.public_dir') . '/assets';

        if (! empty($assetsConf['current'])) {
            $file = $assetsDir . '/' . $assetsConf['current'] . '/' . $params['filename'];

            if (file_exists($file)) {
                $fileResource = new Http\File\File($file);
                $mtime = filemtime($fileResource->getPathname());

                $response->headers->set('Content-Type', $fileResource->getMimeType());
                $response->setContent(file_get_contents($fileResource->getPathname()));
                $response->setCache(array(
                    'public' => true,
                    'last_modified' => new \DateTime(gmdate("D, d M Y H:i:s", $mtime))
                ));

                $response->setExpires(new \DateTime(gmdate("D, j M Y H:i:s", time() + MONTH)));
                $response->isNotModified($this->request);

                return $response;
            }
        } elseif (! empty($assetsConf['fallback'])) {

        }
    }

    /**
     * This method handle a Meta-UI
     *
     * @param  array $data data for view object
     * @param  \Alchemy\Annotation\Annotation $annotation Annotation object containing the information of @ServeUi annotation
     * @param  \Alchemy\Mvc\View $targetView A existent view object  (is was defined previously)
     * @param  \Alchemy\Component\Http\Request $request Request object containing the current request
     * @return \Alchemy\Mvc\ViewInterface
     */
    protected function handleMetaUi(array $data, $annotation, $targetView, Request $request = null)
    {
        // check if a @ServeUi definition exists on action's annotations
        if (empty($annotation)) {
            return $targetView; // @serveUi annotation not found, just return to break ui handling
        }

        // prepare annotation object
        $annotation->prepare();

        // getting configuration
        $metaPath    = $this->config->get('app.meta_dir');
        $elementName = $annotation->name;
        $metaFile    = $annotation->metaFile;
        $attributes  = $annotation->attributes;
        $layout      = $annotation->layout;

        // setting uiEngine Object
        // if any layout wasn't specified on action's annotation
        if (empty($layout)) {
            // read defaults bundles from configuration for desktop & mobile platform
            if ($request->isMobile()) {
                if ($request->isIpad()) {
                    // for ipad device
                    $layout = $this->config->get('layout.mobile');
                } else {
                    $layout = $this->config->get('layout.mobile');
                }
            } else {
                $layout = $this->config->get('layout.default');
            }
        }
        //var_dump($layout); die;
        //$this->uiEngine->setBundleExtensionDir();
        $this->uiEngine->setTargetBundle($layout);
        $this->uiEngine->setMetaFile($metaPath . DS . $annotation->metaFile);

        // tell to uiEngine object build the ui requested
        $elementData = array();
        if (array_key_exists($elementName, $data) && is_array($data[$elementName])) {
            $elementData = $data[$elementName];
        }

        $element = $this->uiEngine->build($elementData, $attributes);
        //$element->setAttribute($attributes);

        if (! empty($annotation->id)) {
            $element->setId($annotation->id);
        }

        $elementType = $element->getXtype();
        $elementId = $element->getId();
        $elementCollection = new \StdClass();
        $elementCollection->$elementId = $element;

        $targetView->assign($elementType, $elementCollection);

        $template = empty($targetView) ? $element->getXtype() . '_page' : $element->getXtype();

        //var_dump($template, file_exists($template), $this->config->get('templating.default_engine'));
        try {
            // creating a new view object
            $view = $this->createView($template, $data);
            $view->assign($element->getXtype(), $element);

            // if the target view was not defined before return the new view object created.
            if (empty($targetView)) {
                return $view;
            }

            // a target view exists, just set the generated ui to target view
            $targetView->setUiElement($element->getId(), $view->getOutput());
        } catch (\Exception $e) {
            // skip exception for template not found
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }

        return $targetView;
    }

    /**
     * handle the view layer
     *
     * @param  string $class current requested controller class
     * @param  string $method current requested controller method or action
     * @param  array $data array containing all data to be assigned to view
     * @param  \Alchemy\Annotation\Annotation $annotation object containing information about view object
     * @throws \Exception
     * @return \Alchemy\Mvc\ViewInterface $view The create view object
     */
    protected function handleView($class, $method, $data, $annotation)
    {
        // check if a @view definition exists on method's annotations
        if (empty($annotation)) {
            return null; // no @view annotation found, just return to break view handling
        }

        $annotation->prepare();
        $template = $annotation->template;

        // check if template filename is empty
        if (empty($template)) {
            // that means the annotation "@view" was not set
            // Then we compose a template filename using controller class and method names but
            // removing ...Controller & ..Action suffixes from those names
            $nsSepPos  = strrpos($class, '\\');
            $class = preg_replace('/([A-Z])/', '_$1', substr($class, $nsSepPos + 1, -10));
            $class = strtolower(ltrim($class, "_"));
            $method = substr($method, 0, -6);
            $template = $class . DS . $method;
        }

        $view = $this->createView($template, $data, $annotation->engine);
        $registeredVars = array();

        // for javascript annotated
        if ($registerAnnotation = $this->annotationReader->getAnnotation('RegisterJavascript')) {
            $registeredVars = $registerAnnotation->all();

            $publicBasePath = $this->config->get('app.public_dir') . DS;
            $sourceBasePath = $this->config->get('app.view_scripts_javascript_dir') . DS;
            $jsBaseUrl = 'assets/cache/';

            foreach ($registeredVars as  $i => $jsFile) {
                if (! file_exists($sourceBasePath . $jsFile)) {
                    throw new \Exception(sprintf("File Not Found Error: File %s doesn't exist!.", $jsFile));
                }

                $jsPath  = substr($jsFile, 0, strrpos($jsFile, DS));
                $fileSum = md5_file($sourceBasePath . $jsFile);
                $jsCacheFile = rtrim($jsFile, '.js') . '-' . $fileSum . '.js';

                // verify if the source file was modified since last cache generated version
                if (! is_file($publicBasePath . $jsBaseUrl . $jsCacheFile)) {
                    if (! is_dir($publicBasePath . $jsBaseUrl . $jsPath)) {
                        self::createDir($publicBasePath . $jsBaseUrl . $jsPath);
                    }

                    // clean up -! Remove old cache file
                    $oldCacheFiles = glob($publicBasePath . $jsBaseUrl . rtrim($jsFile, '.js') . '-*');
                    if (count($oldCacheFiles) > 0) {
                        foreach ($oldCacheFiles as $oldCacheFile) {
                            @unlink($oldCacheFile);
                        }
                    }

                    // TODO copy a minified version instead a normal copy, it depends if it is a debug env.
                    copy($sourceBasePath . $jsFile, $publicBasePath . $jsBaseUrl . $jsCacheFile);
                }

                // adding the new js file path to assign as variable to view object
                $registeredVars[$i] = $jsBaseUrl . $jsCacheFile;
            }
        }
        // end javascript registration

        // registering variables
        $view->assign('javascript', $registeredVars);

        return $view;
    }

    /**
     * Create a view object for a determinated engine.
     *
     * @param  string $template view template filename
     * @param  array $data array containing view data
     * @param  string $engine view engine name
     * @throws \RuntimeException
     * @throws \Exception
     * @return \Alchemy\Mvc\ViewInterface $view created view object
     */
    protected function createView($template, array $data, $engine = '')
    {
        // creating config obj and setting it with all defaults configurations
        $conf = new \StdClass();

        $conf->template     = $template;
        $conf->engine       = empty($engine) ? $this->config->get('templating.default_engine') : $engine;
        $conf->templateDir  = $this->config->get('app.view_templates_dir') . DS;
        $conf->layoutsDir   = $this->config->get('app.view_layouts_dir') . DS;
        $conf->webDir       = $this->config->get('app.web_dir');
        $conf->vendorDir    = $this->config->get('app.vendor_dir');
        $conf->cacheDir     = $this->config->get('templating.cache_dir') . DS;
        $conf->cacheEnabled = $this->config->get('templating.cache_enabled');
        $conf->extension    = $this->config->get('templating.extension');
        $conf->charset      = $this->config->get('templating.charset');
        $conf->debug        = $this->config->get('templating.debug');

        $conf->assetsLocate = $this->config->getSection('assets_location');
        $conf->assetsPrecedence = explode(' ', $this->config->get('assets.precedence'));

        // File extension validation
        // A criteria can be if filename doesn't a period character (.)
        if (strpos($conf->template, '.') === false) {
            if ($this->config->isEmpty('templating.extension')) {
                switch ($conf->engine) {
                    case 'haanga':
                        $tplExtension = 'haanga';
                        break;
                    case 'twig':
                        $tplExtension = 'twig';
                        break;
                    case 'smarty':
                    default:
                        $tplExtension = 'tpl';
                        break;
                }
            } else {
                $tplExtension = $this->config->get('templating.extension');
            }

            $conf->template .= '.' . $tplExtension; // concatenate it with default extension from configuration
        }

        // read defaults bundles from configuration for desktop & mobile platform
        if ($this->request->isMobile()) {
            if ($this->config->exists('layout.mobile') && ! $this->config->isEmpty('layout.mobile')) {
                if ($this->request->isIpad()) {
                    $layoutsDir = $conf->layoutsDir . $this->config->get('layout.mobile') . DS;
                } else {
                    $layoutsDir = $conf->layoutsDir . $this->config->get('layout.mobile') . DS;
                }
            } else {
                $layoutsDir = $conf->layoutsDir;
            }
        } else {
            if ($this->config->exists('layout.default') && ! $this->config->isEmpty('layout.default')) {
                $layoutsDir = $conf->layoutsDir . $this->config->get('layout.default') . DS;
            } else {
                $layoutsDir = $conf->layoutsDir;
            }
        }

        // check if template file exists
        if (file_exists($conf->templateDir . $conf->template)) {
            // if relative path was given
        } elseif (file_exists($conf->layoutsDir . $conf->template)) {
            // if relative path was given
        } elseif (file_exists($layoutsDir . $conf->template)) {
            // if relative path was given
        } elseif (file_exists($conf->template)) {
            // if absolute path was given
        } else {
            // file doesn't exist, throw error
            throw new \RuntimeException("Error: File Not Found: template file doesn't exist: '{$conf->template}'", 404);
        }

        // composing the view class string
        $viewClass = '\Alchemy\Mvc\Adapter\\' . ucfirst($conf->engine) . 'View';

        // check if view engine class exists, if does not exist throw an exception
        if (! class_exists($viewClass)) {
            throw new \RuntimeException(sprintf(
                "Runtime Error: Template Engine is not available: '%s'",
                $conf->engine
            ));
        }

        // resolving assets location
        $locateDirPrecedence = array();

        foreach ($conf->assetsPrecedence as $assetItem) {
            if (array_key_exists($assetItem, $conf->assetsLocate)) {
                $locateDirPrecedence[$assetItem] = $conf->assetsLocate[$assetItem];
            } else {
                $locateDirPrecedence[$assetItem] = $conf->webDir.'/assets/'.$assetItem;
            }
        }

        // create view object
        $view = new $viewClass($conf->template, $this->assetsHandler);

        // setup view object
        $view->enableDebug($conf->debug);
        $view->enableCache($conf->cacheEnabled);
        $view->setCacheDir($conf->cacheDir);
        $view->setCharset($conf->charset);

        // setting templates directories
        $view->setTemplateDir($conf->templateDir);
        $view->setTemplateDir($layoutsDir);


        $baseurl = '/' . $this->request->getBaseUrl();

        if (substr($baseurl, -4) == '.php') {
            $baseurl = substr($baseurl, 0, strrpos($baseurl, '/') + 1);
        } elseif (substr($baseurl, -1) !== '/') {
            $baseurl .= '/';
        }

        // setting data to be used by template
        $view->assign('baseurl', $baseurl);
        $view->assign($data);

        // setting & registering assets handler on view
        $view->assetsHandler->setBaseDir($conf->webDir);
        $view->assetsHandler->setLocateDir($locateDirPrecedence);
        $view->assetsHandler->setCacheDir($conf->cacheDir);
        $view->assetsHandler->setOutputDir('assets/compiled');
        $view->assetsHandler->setVendorDir($conf->vendorDir);

        return $view;
    }

    /**
     * This method prepares the request parameters to be consumed by Controller Resolver
     * @param  array $data   array containing all request params.
     * @return array $params array containing all prepared params included _controller,
     *                       _action params, _controllerClass and _controllerMethod
     */
    protected function prepareRequestParams(array $data)
    {
        $params = array();

        foreach ($data as $listParams) {
            $params = array_merge($params, $listParams);
        }

        $params['_controllerClass'] = $params['_controller'];
        $params['_controllerMethod'] = $params['_action'];
        $params['_controller'] = $params['_controllerClass'] . '::' . $params['_controllerMethod'];

        return $params;
    }

    /**
     * Creates a directory recursively
     * @param  string  $strPath path
     * @param  integer $rights  right for new directory
     */
    protected static function createDir($strPath, $rights = 0777)
    {
        $folderPath = array($strPath);
        $oldumask    = umask(0);

        while (!@is_dir(dirname(end($folderPath)))
            && dirname(end($folderPath)) != '/'
            && dirname(end($folderPath)) != '.'
            && dirname(end($folderPath)) != ''
        ) {
            array_push($folderPath, dirname(end($folderPath)));
        }

        while ($parentFolderPath = array_pop($folderPath)) {
            if (!@is_dir($parentFolderPath)) {
                if (!@mkdir($parentFolderPath, $rights)) {
                    throw new \Exception("Runtime Error: Can't create folder '$parentFolderPath'");
                }
            }
        }

        umask($oldumask);
    }
}

