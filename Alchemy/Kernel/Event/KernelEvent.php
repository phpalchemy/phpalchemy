<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Net\Http\Request;
use Alchemy\Component\EventDispatcher\Event;
use Alchemy\Kernel\KernelInterface;

/**
 * Base class for events thrown in the HttpKernel component
 */
class KernelEvent extends Event
{
    /**
     * The kernel in which this event was thrown
     * @var Alchemy\Component\Kernel\KernelInterface
     */
    private $kernel;

    /**
     * The request the kernel is currently processing
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;


    public function __construct(KernelInterface $kernel, Request $request)
    {
        $this->kernel = $kernel;
        $this->request = $request;
    }

    /**
     * Returns the kernel in which this event was thrown
     *
     * @return Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the request the kernel is currently processing
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
