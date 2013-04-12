<?php
use Alchemy\Component\EventDispatcher\Event;

function onBeforeLogin(Event $event)
{
    echo 'executed before login';
}

function onAfterLogin(Event $event)
{
    $params = $event->getParameters();
    $params = implode(', ', $params);

    echo 'executed after login, with params: ' . $params;
}

// others functions
function onBeforeLogin2(Event $event)
{
    echo 'executed before login #2';

    return true;
}

function onBeforeLogin3(Event $event)
{
    echo 'executed before login #3';
}

