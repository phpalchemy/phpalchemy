<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Component\EventDispatcher;

/**
 * An EventSubscriber knows himself what events he is interested in.
 * If an EventSubscriber is added to an EventDispatcherInterface, the manager invokes
 * getSubscribedEvents() and registers the subscriber as a listener for all
 * returned events.
 */

/**
 * Class Event
 *
 * @subpackage EventDispatcher
 * @author     Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @version    1.0
 */
interface EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * sample:
     *
     *   array('eventName' => 'methodName')
     *   array('eventName' => array('methodName1', 'methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents();
}

