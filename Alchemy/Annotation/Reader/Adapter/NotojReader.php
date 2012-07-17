<?php
namespace Alchemy\Annotation\Reader\Adapter;

use Alchemy\Annotation\Reader\Reader;
use Notoj\ReflectionClass;
use Notoj\ReflectionMethod;

/**
 * Reader Adapter for Notoj Annotations loader Library
 */
class NotojReader extends Reader
{
    /**
     * {@inheritdoc}
     */
    public function setCacheDir($cacheDir)
    {
        parent::setCacheDir($cacheDir);
        \Notoj\Notoj::enableCache($cacheDir . DIRECTORY_SEPARATOR . "_annotations.php");
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotations($class)
    {
        return $this->convertAnnotationObj(new ReflectionClass($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotations($class, $methodName)
    {
        return $this->convertAnnotationObj(new ReflectionMethod($class, $methodName));
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotationsObjects($class)
    {
        return $this->createAnnotationObjects($this->getClassAnnotations($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotationsObjects($class, $method)
    {
        $objects = $this->createAnnotationObjects($this->getMethodAnnotations($class, $method));

        foreach ($objects as $object) {
            $object->prepare();
        }

        return $objects;
    }

    /**
     * Create annotations object
     *
     * @param  array  $annotations annotated elements
     * @return array               array of annoatated objects
     */
    protected function createAnnotationObjects(array $annotations)
    {
        $objects     = array();

        foreach ($annotations as $name => $args) {
            $name = ucfirst($name);
            $class = $this->defaultNamespace . $name . 'Annotation';

            if (! array_key_exists($class, $objects)) {
                if (!class_exists($class)) {
                    if ($this->strict) {
                        throw new \Exception(sprintf('Annotation Class Not Found: %s', $class));
                    } else {
                        continue;
                    }
                }

                $objects[$name] = new $class();
            }
            //var_dump($args);
            foreach ($args as $key => $value) {
                $objects[$name]->set($key, $value);
            }
        }

        return $objects;
    }

    /**
     * This method converts Notoj returned array to reader data
     *
     * @param  Mixed/Reflection  $reflection a Notoj reflection object
     * @return array             annoations array
     */
    protected function convertAnnotationObj($reflection)
    {
        $annotations = (array) $reflection->getAnnotations();
        $result = array();

        foreach ($annotations as $annotation) {

            foreach ($annotation['args'] as $key => $value) {
                if (is_numeric($key)) {
                    $result[$annotation['method']][] = $value;
                } else {
                    $result[$annotation['method']][$key] = $value;
                }
            }
        }

        return $result;
    }
}

