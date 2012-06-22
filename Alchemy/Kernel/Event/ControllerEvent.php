<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Component\EventDispatcher\Event;
use Alchemy\Kernel\KernelInterface;

class ControllerEvent extends KernelEvent
{
    /**
     * The current controller
     * @var callable
     */
    private $controller = null;
    private $response   = null;
    private $arguments  = array();

    public function __construct(
        KernelInterface $kernel, $controller, $arguments,
        Request $request, Reesponse $response = null
    )
    {
        parent::__construct($kernel, $request);

        $this->setController($controller);
        $this->arguments = $arguments;
        $this->response  = $request;
    }

    /**
     * Sets a controller
     *
     * @param callable $controller
     */
    public function setController($controller)
    {
        // controller must be a callable
        if (!is_callable($controller)) {
            throw new \LogicException(sprintf(
                'The controller must be a callable (%s given).',
                gettype($controller)
            ));
        }

        $this->controller = $controller;
    }

    /**
     * Returns the current controller
     *
     * @return callable
     */
    public function getController()
    {
        return $this->controller;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
