<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Component\Http\Request;

/**
 * Allows filtering of a controller callable
 *
 * You can call getController() to retrieve the current controller. With
 * setController() you can set a new controller that is used in the processing
 * of the request.
 *
 * Controllers should be callables.
 */
class FilterControllerEvent extends KernelEvent
{
    /**
     * The current controller
     * @var callable
     */
    private $controller;

    public function __construct(KernelInterface $kernel, $controller, Request $request)
    {
        parent::__construct($kernel, $request);
        $this->setController($controller);
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

    /**
     * Sets a new controller
     *
     * @param callable $controller
     */
    public function setController($controller)
    {
        // controller must be a callable
        if (!is_callable($controller)) {
            throw new \LogicException(sprintf('The controller must be a callable (%s given).', gettype($controller)));
        }

        $this->controller = $controller;
    }
}

