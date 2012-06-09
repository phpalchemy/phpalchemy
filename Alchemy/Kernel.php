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
    protected $matcher;
    protected $resolver;
    protected $dispatcher;
    protected $config;

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
    }

    /**
     * handle the http user request
     * @param  Request $request user hhtp request
     */
    public function handle(Request $request)
    {
        try {
            $requestParams  = $this->matcher->match($request->getPathInfo());
            $requestParams  = array_merge($requestParams, $request->query->all());
            $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $requestParams['_controller'])));

            $controllerClass = sprintf(
                '\\%s\\Controller\\%sController::%sAction',
                $this->config->get('app.namespace'),
                $controllerName,
                $requestParams['_action']
            );

            $requestParams['_controller'] = $controllerClass;

            $request->attributes->add($requestParams);

            $controller = $this->resolver->getController($request);
            $arguments  = $this->resolver->getArguments($request, $controller);

            // call the controller action
            $response   = call_user_func_array($controller, $arguments);
            $controllerObject = $controller[0];

            // if action() returs a Response instance
            if ($response instanceof Response) {
                // $response is already a Response instance
            }
            // if action() returns array data for template)
            else if (is_array($response)) {
                // controller data
            }
            // if action() had not returned any value
            else if ($response === null) {
                // if the response object was built in action
                if (isset($controllerObject->response)) {
                    $response = $controllerObject->response;
                }
                // Any Reponse instance was build on action()
                else {
                    $response = new Response();
                }
            }

        }
        catch (ResourceNotFoundException $e) {
            $response = new Response($e->getMessage(), 404);
        }
        catch (\Exception $e) {
            $response = new Response('<pre>'.$e->getMessage().'<br/>'.$e->getTraceAsString(), 500);
        }

        // dispatch a response event
        //$this->dispatcher->dispatch('response', new ResponseEvent($response, $request));

        return $response;
    }
}
