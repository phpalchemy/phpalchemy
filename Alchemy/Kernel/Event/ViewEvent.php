<?php
namespace Alchemy\Kernel\Event;

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

    public function __construct(KernelInterface $kernel, View $view, Request $request, Reader $annotationReader)
    {
        parent::__construct($kernel, $request);
        $this->view = $view;
        $this->annotationReader = $annotationReader;
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

