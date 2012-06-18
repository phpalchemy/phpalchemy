<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;

class ResponseEvent extends KernelEvent
{
    private $request;
    private $response;

    public function __construct(KernelInterface $kernel, Response $response, Request $request)
    {
        parent::__construct($kernel, $request);

        $this->response = $response;
        $this->request = $request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}