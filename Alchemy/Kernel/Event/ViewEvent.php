<?php
namespace Alchemy\Kernel\Event;

use Alchemy\Kernel\KernelInterface;
use Alchemy\Component\Http\Request;
use Alchemy\Mvc\View;

class ViewEvent extends KernelEvent
{
    /**
     * Contains the view object
     * @var View
     */
    protected $view = null;

    public function __construct(KernelInterface $kernel, View $view, Request $request)
    {
        parent::__construct($kernel, $request);
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView(View $view)
    {
        $this->view = $view;
    }
}

