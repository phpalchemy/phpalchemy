<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Config;
use Alchemy\Component\Http\Request;
use Alchemy\Annotation\ViewAnnotation;

class ViewEvent extends KernelEvent
{
    /**
     * Contains the controller class name
     * @var array
     */
    protected $controllerClass = '';

    /**
     * Contains the controller method name
     * @var array
     */
    protected $controllerMethod = '';

    /**
     * Contains all data that can be passed to template file
     * @var array
     */
    protected $data = array();

    /**
     * Contains the view annotation from controller method docblock
     * @var array
     */
    protected $annotation = array();

    /**
     * Config instance, it contains all app configuration
     * @var Config
     */
    protected $config = null;

    /**
     * Contains the view object
     * @var View
     */
    protected $view = null;

    public function __construct(
        KernelInterface $kernel, $ctrlrClass, $ctrlrMethod, array $data,
        array $annotation, Config $config, Request $request
    )
    {
        parent::__construct($kernel, $request);

        $this->controllerClass  = $ctrlrClass;
        $this->controllerMethod = $ctrlrMethod;

        $this->data       = $data;
        $this->annotation = $annotation;
        $this->config     = $config;
    }

    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getAnnotation()
    {
        return $this->annotation;
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
