<?php
namespace Alchemy\Kernel\EventListener;

use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\Event\FilterResponseEvent;
use Alchemy\Kernel\KernelEvents;
use Alchemy\Component\Http\Response;

class ResponseListener implements EventSubscriberInterface
{
    private $charset;

    public function __construct($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Filters the Response.
     *
     * @param FilterResponseEvent $event    A FilterResponseEvent instance
     */
    public function onResponse(FilterResponseEvent $event)
    {
        // $response = $event->getResponse();
        // $headers  = $response->headers;

        // $headers->set('Content-Length', 11);

        // if (!$headers->has('Content-Length') && !$headers->has('Transfer-Encoding')) {
        //     $headers->set('Content-Length', strlen($response->getContent()));
        // }



        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('HEAD' === $request->getMethod()) {
            // cf. RFC2616 14.13
            $length = $response->headers->get('Content-Length');
            $response->setContent('');
            if ($length) {
                $response->headers->set('Content-Length', $length);
            }
        }

        if ($response->getCharset() === null) {
            $response->setCharset($this->charset);
        }

        if ($response->headers->has('Content-Type')) {
            return;
        }

        $format = $request->getRequestFormat();

        if (($format !== null) && ($mimeType = $request->getMimeType($format))) {
            $response->headers->set('Content-Type', $mimeType);
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




