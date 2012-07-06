<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Annotation\ResponseAnnotation;

/**
 * Allows to filter a Response object
 *
 * You can call getResponse() to retrieve the current response. With
 * setResponse() you can set a new response that will be returned to the
 * browser.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 *
 * @api
 */
class FilterResponseEvent extends KernelEvent
{
    /**
     * The current response object
     * @var Alchemy\Component\Http\Response
     */
    private $response    = null;
    private $annotations = null;

    public function __construct(
        KernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseAnnotation $annotations = null
    ) {
        parent::__construct($kernel, $request);
        $this->setResponse($response);
        $this->annotations = $annotations;
    }

    /**
     * Returns the current response object
     *
     * @return Alchemy\Component\Http\Response
     *
     * @api
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets a new response object
     *
     * @param Alchemy\Component\Http\Response $response
     *
     * @api
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }
}

