<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Net\Http\Request;
use Alchemy\Component\EventDispatcher\Event;
use Alchemy\Kernel\KernelInterface;

class ControllerEvent extends KernelEvent
{
    /**
     * The current controller
     *
     * @var callable
     */
    private $controller = null;
    private $arguments  = array();

    public function __construct(KernelInterface $kernel, $controller, $arguments, Request $request)
    {
        parent::__construct($kernel, $request);

        $this->setController($controller);
        $this->setArguments($arguments ? $arguments : array());
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

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}
