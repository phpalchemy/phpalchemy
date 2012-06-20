<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Config;
use Alchemy\Component\Http\Request;

class ViewEvent extends KernelEvent
{
    /**
     * The view object
     * @var callable
     */
    protected $view   = null;
    
    /**
     * Config instance, it contains all app configuration
     * @var Config
     */
    protected $config = null;

    /**
     * Contains the controller class name 
     * @var string
     */
    protected $ctrlrClass  = '';
    
    /**
     * Contains Controller method name 
     * @var string
     */
    protected $ctrlrMethod = '';
    
    /**
     * Contains all data that can be passed to template file
     * @var array
     */
    protected $data = array();
    
    /**
     * Contains the current request instance
     * @var Request
     */
    protected $request = null;

    public function __construct(KernelInterface $kernel, $ctrlrClass, $ctrlrMethod, 
                                array $data, Config $config, Request $request)
    {
        parent::__construct($kernel, $request);

        $this->ctrlrClass  = $ctrlrClass;
        $this->ctrlrMethod = $ctrlrMethod;
        $this->data        = $data;
        $this->config      = $config;
        $this->request     = $request;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getControllerClass()
    {
        return $this->ctrlrClass;
    }
    
    public function getControllerMethod()
    {
        return $this->ctrlrMethod;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getRequest()
    {
        return $this->request;
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
