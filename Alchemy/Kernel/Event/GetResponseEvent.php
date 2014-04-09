<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\Kernel\Event;

use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Application;

/*
 * This Class is a adapted version of Symfony\Component\HttpKernel\Event\GetResponseEvent
 * Code subject to the MIT license
 * Copyright (c) 2004-2012 Fabien Potencier
 */

/**
 * Class GetResponseEvent
 *
 * Allows to create a response for a request
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 *
 * @version   1.0
 * @author    Bernhard Schussek <bernhard.schussek@symfony.com>
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class GetResponseEvent extends KernelEvent
{
    /**
     * The response object
     * @var \Alchemy\Component\Http\Response
     */
    private $response;
    /**
     * The running application
     * @var \Alchemy\Application
     */
    private $app;

    public function __construct(KernelInterface $kernel, Request $request, Application $app = null)
    {
        parent::__construct($kernel, $request);
        $this->app = $app;
    }

    /**
     * Returns the response object
     *
     * @return \Alchemy\Component\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets a response and stops event propagation
     *
     * @param \Alchemy\Component\Http\Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        $this->stopPropagation();
    }

    /**
     * Returns whether a response was set
     *
     * @return Boolean Whether a response was set
     */
    public function hasResponse()
    {
        return $this->response !== null;
    }

    /**
     * Returns the request the kernel is currently processing
     *
     * @return \Alchemy\Application
     */
    public function getApplication()
    {
        return $this->app;
    }
}

