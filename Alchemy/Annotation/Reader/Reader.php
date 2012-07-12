<?php
namespace Alchemy\Annotation\Reader;

/**
 * Annotations Reader Class
 *
 * This class reads all annotations from a class and methods
 *
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
abstract class Reader
{
    /**
     * Indicates that annotations should has strict behavior, 'true' by default
     * @var boolean
     */
    protected $strict = true;

    /**
     * Contains cache directory path
     * @var string
     */
    protected $cacheDir = '';

    /**
     * Stores the default namespace for Objects instance, usually used on methods like getMethodAnnotationsObjects()
     * @var string
     */
    protected $defaultNamespace = '';

    /**
     * Sets strict variable to true/false
     *
     * @param bool $value boolean value to indicate that annotations to has strict behavior
     */
    public function setStrict($value)
    {
        $this->strict = (bool) $value;
    }

    /**
     * Sets strict variable to true/false
     *
     * @param bool $value boolean value to indicate that annotations to has strict behavior
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Sets cache directory
     *
     * @param string $path a cache directory
     */
    public function setCacheDir($cacheDir)
    {
        if (! is_dir($cacheDir)) {
            throw new \RuntimeException('Runtime Error: Cache directory does not exist!');
        }

        $this->cacheDir = $cacheDir;
    }

    /**
     * Sets cache directory
     *
     * @param string $path a cache directory
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Sets default namespace to use in object instantiation
     * @param string $namespace default namespace
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
    }

    /**
     * Sets default namespace to use in object instantiation
     * @param string $namespace default namespace
     */
    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    /**
     * Gets all anotations from a given class
     *
     * @param  string $className             class name to get annotations
     * @return array  self::$annotationCache all annotated elements
     */
    abstract public function getClassAnnotations($className);

    /**
     * Gets all anotations from a determinated method of a given class
     *
     * @param  string $className             class name
     * @param  string $methodName            method name to get annotations
     * @return array  self::$annotationCache all annotated elements of a method given
     */
    abstract public function getMethodAnnotations($className, $methodName);

    /**
     * Gets all anotations objects from a determinated method of a given class
     * and instance its abcAnnotation class
     *
     * @param  string $className             class name
     * @param  string $methodName            method name to get annotations
     * @return array  self::$annotationCache all annotated objects of a method given
     */
    abstract public function getMethodAnnotationsObjects($className, $methodName);
}

