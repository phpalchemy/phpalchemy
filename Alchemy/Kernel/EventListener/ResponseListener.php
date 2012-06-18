<?php
namespace Alchemy\Kernel\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\ResponseEvent;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Net\Http\Response;

class ResponseListener implements EventSubscriberInterface
{
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $headers  = $response->headers;

        $headers->set('Content-Length', 11);

        if (!$headers->has('Content-Length') && !$headers->has('Transfer-Encoding')) {
            $headers->set('Content-Length', strlen($response->getContent()));
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
            KernelEvents::RESPONSE => array('onResponse'),
        );
    }
}