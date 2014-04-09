<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Application;
use Alchemy\Kernel\KernelInterface;
use Alchemy\Component\Http\Request;
use Alchemy\Mvc\View;
use Alchemy\Annotation\Reader\Reader;

class ViewEvent extends KernelEvent
{
    /**
     * Contains the view object
     * @var View
     */
    protected $view = null;
    protected $annotationReader = null;
    protected $app = null;

    public function __construct(KernelInterface $kernel, View $view, Request $request, Reader $annotationReader, Application $app = null)
    {
        parent::__construct($kernel, $request);
        $this->view = $view;
        $this->annotationReader = $annotationReader;
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    public function setView(View $view)
    {
        $this->view = $view;
    }
}

