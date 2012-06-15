<?php
namespace Alchemy\Kernel\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\ResponseEvent;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Net\Http\Response;

class ResponseListener implements EventSubscriberInterface
{
    public function onControllerHandling(ControllerEvent $event)
    {
        // getting information from ControllerEvent object
        $controller = $event->getController();
        $arguments  = $event->getArguments();

        // call (execute) the controller method
        $response = call_user_func_array($controller, $arguments);

        // check returned value from method
        if (is_array($response)) { // if it returns a array
            // set all data to view instance from controller
            foreach ($response as $key => $value) {
                $controller[0]->view->$key = $value;
            }
        } elseif ($response instanceof Response) {
            $controller[0]->setResponse($response);
        }
    }

    /**
     * Static method that returns all subscribed events on this listener
     *
     * @return array subscribed events
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onControllerHandling'),
        );
    }
}