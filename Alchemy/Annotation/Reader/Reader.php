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
    protected $targetClass = '';
    protected $targetMethod = '';
    protected $annotations = array();

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


    public function __constructor($class = '', $method = '')
    {
        $this->setTarget($class, $method);
    }

    /**
     * Sets target class to handling
     *
     * @param string $class target class name to annotations handling
     */
    public function setTarget($class, $method = '')
    {
        $this->targetClass  = $class;
        $this->targetMethod = $method;
    }

    /**
     * Gets target class
     *
     * @return string $this->targetClass target class name
     */
    public function getClassTarget()
    {
        return $this->targetClass;
    }

    /**
     * Gets target method
     *
     * @return string $this->methodTarget target method name
     */
    public function getMethodTarget()
    {
        return $this->targetClass;
    }

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
        // fix namespace, to ensure a final ns separator at the end
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

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

    public function hasTarget()
    {
        return ! empty($this->targetClass);
    }

    /**
     * Loads class annotations and persist on the reader
     *
     * @param  string $class (optional) class name to read & load annotations objects
     */
    protected function loadClassAnnotations()
    {
        if (empty($this->targetClass)) {
            throw new \RuntimeException("Runtime Error: Any class was defined as target to annotations handling.");
        }

        $annotationsObjects = array();
        $this->annotations['_class_'] = $this->getClassAnnotationsObjects($this->targetClass);
    }

    /**
     * Loads method annotations form target class and persist on the reader
     *
     * @param  string $method method name of target class to read & load annotations objects
     */
    protected function loadMethodAnnotations()
    {
        if (empty($this->targetClass)) {
            throw new \RuntimeException("Runtime Error: Any class was defined as target to annotations handling.");
        }

        if (empty($this->targetMethod)) {
            throw new \RuntimeException(sprintf(
                "Runtime Error: Any method of class '%s' was defined as target to annotations handling.",
                $this->targetClass
            ));
        }

        $annotationsObjects = array();
        $this->annotations[$this->targetMethod] = $this->getMethodAnnotationsObjects(
            $this->targetClass,
            $this->targetMethod
        );
    }

    /**
     * Gets annotations objects previously loaded on reader
     *
     * @param  string $name (optional) method name of target class
     * @return array  Array containing all annotation objects for the method passed as param,
     *                if the $methodName param is not passed the class annotations objects it will be returned
     */
    public function getAnnotation($annotationName)
    {
        if (empty($this->targetClass)) {
            throw new \RuntimeException("Runtime Error: Any class was defined as target to annotations handling.");
        }

        $annotationInstance = null;

        // if any method was specified as target just process class annotations
        if (empty($this->targetMethod)) {
            // if class annotations wasn't loaded before load it
            if (! array_key_exists('_class_', $this->annotations)) {
                $this->loadClassAnnotations();
            }
            // get annotation instance if defined
            if (array_key_exists($annotationName, $this->annotations['_class_'])) {
                $annotationInstance = $this->annotations['_class_'][$annotationName];
            }
        } else {
            // if method class annotations wasn't loaded before load it
            if (! array_key_exists($this->targetMethod, $this->annotations)) {
                $this->loadMethodAnnotations();
            }
            // get annotation instance if defined
            if (array_key_exists($annotationName, $this->annotations[$this->targetMethod])) {
                $annotationInstance = $this->annotations[$this->targetMethod][$annotationName];
            }
        }

        return $annotationInstance;
    }

    public function getAnnotations()
    {
        if (empty($this->targetClass)) {
            throw new \RuntimeException("Runtime Error: Any class was defined as target to annotations handling.");
        }

        // if any method was specified as target just process class annotations
        if (empty($this->targetMethod)) {
            // if class annotations wasn't loaded before load it
            if (! array_key_exists('_class_', $this->annotations)) {
                $this->loadClassAnnotations();
            }

            return $this->annotations['_class_'];
        } else {
            // if method class annotations wasn't loaded before load it
            if (! array_key_exists($this->targetMethod, $this->annotations)) {
                $this->loadMethodAnnotations();
            }

            return $this->annotations[$this->targetMethod];
        }
    }

    protected static function getExcludeAnnotationList()
    {
        return array(
            "author",
            "api",
            "category",
            "copyright",
            "deprecated",
            "example",
            "filesource",
            "global",
            "ignore",
            "internal",
            "license",
            "link",
            "method",
            "package",
            "param",
            "property-read",
            "property-write",
            "property",
            "return",
            "see",
            "since",
            "source",
            "subpackage",
            "throws",
            "todo",
            "uses",
            "used-by",
            "var",
            "version"
        );
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
    abstract public function getClassAnnotationsObjects($className);

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

