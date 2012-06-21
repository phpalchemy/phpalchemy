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
 * EventDispatcher implements the Observer Design Pattern
 *
 * @see http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework
 */

/**
 * Class Event
 *
 * @package    phpalchemy
 * @subpackage EventDispatcher
 * @author     Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @version    1.0
 */

class EventDispatcher
{
    protected $_listeners = array();

    /**
     * Add a listener for a given event name.
     *
     * @param string  $eventName An event name
     * @param mixed   $listener  Mixed value
     */
    public function addListener($eventName, $listener)
    {
        if (!isset($this->_listeners[$eventName])) {
          $this->_listeners[$eventName] = array();
        }

        $this->_listeners[$eventName][] = $listener;
    }

    /**
     * Disconnects a listener for a given event name.
     *
     * @param string   $name      An event name
     * @param mixed    $listener  A PHP callable
     *
     * @return mixed false if listener does not exist, null otherwise
     */
    public function removeListener($eventName, $listener)
    {
        if (!isset($this->_listeners[$eventName])) {
          return false;
        }

        // removing listeners for a given event
        foreach ($this->_listeners[$eventName] as $i => $callback) {
            if ($listener === $callback) {
                unset($this->_listeners[$eventName][$i]);
            }
        }

        // cleaning listener container if there is not more listeners on it
        if (count($this->_listeners[$eventName]) == 0) {
            unset($this->_listeners[$eventName]);
        }
    }

    /**
     * Dispatch all listeners of a given event.
     *
     * @param Event $event A Event object instance
     *
     * @return the same Event object passed by param.
     */
    public function dispatch($eventName, Event $event)
    {
        if (!$this->hasListeners($eventName)) {
            return $event;
        }

        // setting event name
        $event->setName($eventName);

        // execute all callbacks for a event given
        foreach ($this->getListeners($event->getName()) as $listener) {
            call_user_func($listener, $event);

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }

    /**
     * Dispatch all listeners of a given event until one returns a non null value.
     *
     * @param  Event $event A Event instance
     *
     * @return Event The Event instance
     */
    public function dispatchUntil($eventName, Event $event)
    {
        // skip dispatching because event is already dispatched
        if ($event->isProcessed()) {
            return $event;
        }

        // skip dispatching because event hasn't listeners
        if (!$this->hasListeners($eventName)) {
            return $event;
        }

        $event->setName($eventName);

        foreach ($this->getListeners($event->getName()) as $listener) {
            if (call_user_func($listener, $event)) {
                $event->setProcessed(true);
                break;
            }
        }

        return $event;
    }

    /**
     * Returns true if the given event name has some listeners.
     *
     * @param  string   $name    The event name
     *
     * @return Boolean true if some listeners are connected, false otherwise
     */
    public function hasListeners($eventName = '')
    {
        $count = 0;

        if (empty($eventName)) {
            $count = count($this->_listeners);
        }
        else if (isset($this->_listeners[$eventName])) {
            $count = count($this->_listeners[$eventName]);
        }

        return (boolean) $count;
    }

    /**
     * Returns all listeners associated with a given event name.
     *
     * @param  string   $name    The event name
     *
     * @return array  An array of listeners
     */
    public function getListeners($name = null)
    {
        if (empty($name)) {
            return $this->_listeners;
        }

        if (!isset($this->_listeners[$name])) {
            return array();
        }

        return $this->_listeners[$name];
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $params = array($params);
            }

            if (is_array($params)) {
                foreach ($params as $listener) {
                    $this->addListener($eventName, array($subscriber, $listener));
                }
            }
            else {
                throw new Exception("Invalid listener type, given: '".gettype($params)."'.");
            }
        }
    }
}


