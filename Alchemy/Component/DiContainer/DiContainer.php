<?php
/**
 * Class DiContainer
 *
 * Simplest Dependency injection container
 * (DiContainer is fork of Pimple, with modifications, some concepts were changed,
 * i.e. the method protect has other functionality than Pimple has)
 *
 * This Class is a adapted version of \DiContainer
 * Code subject to the MIT license
 * Copyright (c) 2009 Fabien Potencier
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class DiContainer implements \ArrayAccess
{
    private $container;
    private $protected = array();

    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $container The parameters or objects.
     */
    public function __construct (array $container = array())
    {
        $this->container = $container;
    }

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as public function names (strings) are callable (creating a public function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to defined an object
     */
    public function offsetSet($id, $value)
    {
        if (in_array($id, $this->protected)) {
            //TODO need implements a logger to store this modifications attempts
            return;
        }

        $this->container[$id] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     *
     * @return mixed  The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->container)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->container[$id] instanceof Closure ? $this->container[$id]($this) : $this->container[$id];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param  string $id The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->container);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        unset($this->container[$id]);
    }

    /**
     * Returns a closure that stores the result of the given closure for
     * uniqueness in the scope of this instance of DiContainer.
     *
     * @param Closure $callable A closure to wrap for uniqueness
     * @return Closure The wrapped closure
     */
    public function share(Closure $callable)
    {
        return function ($c) use ($callable) {
            static $object;

            if (is_null($object)) {
                $object = $callable($c);
            }

            return $object;
        };
    }

    /**
     * This method protect to previously defined service to posterior modifications
     * to prevent break the initial logic.
     *
     * @param string $id the service id
     * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
     */
    public function protect($id)
    {
        $this->protected[] = $id;
    }

    /**
     * Share and protects a callable from being interpreted as a service.
     * This is useful when you want to store a callable as a parameter.
     *
     * @param Closure $callable A closure to protect from being evaluated
     * @return Closure The protected closure
     */
    public function shareCallable(Closure $callable)
    {
        return function ($c) use ($callable) {
            return $callable;
        };
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return mixed  The value of the parameter or the closure defining an object
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function raw($id)
    {
        if (!array_key_exists($id, $this->container)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->container[$id];
    }

    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param  string  $id       The unique identifier for the object
     * @param  Closure $callable A closure to extend the original
     *
     * @return Closure The wrapped closure
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function extend($id, Closure $callable)
    {
        if (!array_key_exists($id, $this->container)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        $factory = $this->container[$id];

        if (!($factory instanceof Closure)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }

        return $this->container[$id] = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
    }

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->container);
    }

    /**
     * Get all elements.
     *
     * @return array/mixed contained items
     */
    public function all()
    {
        return $this->container;
    }
}

