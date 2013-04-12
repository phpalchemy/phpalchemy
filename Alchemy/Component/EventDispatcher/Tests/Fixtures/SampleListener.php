<?php
use Alchemy\Component\EventDispatcher\Event;

class SampleListener
{
    public function onAction(Event $event)
    {
        $params = $event->getParameters();
        $params = implode(', ', $params);

        echo "exec: SampleListener::onAction($params)";
    }
}

