<?php
namespace {namespace}\Application\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\ViewEvent;
use Alchemy\Kernel\KernelEvents;

class BeforeResponse implements EventSubscriberInterface
{
    public function onResponse()
    {
        echo 'on response from event suscriber';
    }

    public function onView(ViewEvent $event)
    {
        $view = $event->getView();

        $menu = include(__DIR__.'/../../config/menu.php');
        $view->assign('_menu', $menu);
    }

    public static function getSubscribedEvents()
    {
        return array(
            //Kernel::BEFORE_RESPONSE => 'onResponse',
            KernelEvents::VIEW => 'onView'
        );
    }
}
