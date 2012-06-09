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
 * Class Event
 *
 * @package    IronG
 * @subpackage EventDispatcher
 * @author     Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @version    1.0
 */
class Event
{
    protected $value      = null;
    protected $processed  = false;
    protected $name       = '';
    protected $parameters = array();

    /**
     * Event Constructor.
     *
     * @param string $name       The event name
     * @param array  $parameters An array of parameters
     */
    public function __construct($parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets the event name.
     *
     * @param string The event name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the event name.
     *
     * @return string The event name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the processed flag.
     *
     * @param Boolean $processed The processed flag value
     */
    public function setProcessed($processed)
    {
        $this->processed = (boolean) $processed;
    }

    /**
     * Returns whether the event has been processed by a listener or not.
     *
     * @return Boolean true if the event has been processed, false otherwise
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * Sets the event parameters.
     *
     * @param array The event parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the event parameters.
     *
     * @return array The event parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
