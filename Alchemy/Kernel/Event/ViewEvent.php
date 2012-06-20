<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Config;
use Alchemy\Component\Http\Request;

class ViewEvent extends KernelEvent
{
    /**
     * The view object
     *
     * @var callable
     */
    protected $view   = null;
    protected $config = null;

    protected $controllerMeta = array();
    protected $data           = array();

    public function __construct(
        KernelInterface $kernel, array $data, array $controllerMeta,
        Config $config, Request $request
    )
    {
        parent::__construct($kernel, $request);

        $this->controllerMeta = $controllerMeta;

        $this->data   = $data;
        $this->config = $config;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getControllerMeta()
    {
        return $this->controllerMeta;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}
