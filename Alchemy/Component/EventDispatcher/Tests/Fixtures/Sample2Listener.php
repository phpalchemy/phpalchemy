<?php
use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Component\EventDispatcher\Event;

class Sample2Listener implements EventSubscriberInterface
{
    public function onBeforeLogin(Event $event)
    {
        $params = implode(', ', $event->getParameters());

        echo "executed Sample2Listener::onBeforeLogin($params)";
    }

    public function onBeforeLogin2(Event $event)
    {
        $params = implode(', ', $event->getParameters());

        echo "executed Sample2Listener::onBeforeLogin2($params)";
    }

    public function onAfterLogin(Event $event)
    {
        $params = implode(', ', $event->getParameters());

        echo "executed Sample2Listener::onAfterLogin($params)";
    }

    public static function getSubscribedEvents()
    {
        return array(
            'system.before_login' => array('onBeforeLogin', 'onBeforeLogin2'),
            'system.after_login'  => 'onAfterLogin'
        );
    }
}

