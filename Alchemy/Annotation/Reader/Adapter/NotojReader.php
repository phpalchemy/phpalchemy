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
    public function setCacheDir($cacheDir)
    {
        parent::setCacheDir($cacheDir);
        \Notoj\Notoj::enableCache($cacheDir . DIRECTORY_SEPARATOR . "_annotations.php");
    }

    public function getClassAnnotations($class)
    {
        return $this->convertAnnotationObj(new ReflectionClass($class));
    }

    public function getMethodAnnotations($class, $methodName)
    {
        return $this->convertAnnotationObj(new ReflectionMethod($class, $methodName));
    }

    public function getClassAnnotationsObjects($class)
    {
        return $this->createAnnotationObjects($this->getClassAnnotations($class));
    }

    public function getMethodAnnotationsObjects($class, $method)
    {
        return $this->createAnnotationObjects($this->getMethodAnnotations($class, $method));
    }

    protected function createAnnotationObjects(array $annotations)
    {
        $objects     = array();

        foreach ($annotations as $name => $args) {
            $name = ucfirst($name);
            $class = $this->defaultNamespace . $name . 'Annotation';

            if (empty($objects[$class])) {
                if (!class_exists($class)) {
                    if ($this->strict) {
                        throw new \Exception(sprintf('Annotation Class Not Found: %s', $class));
                    } else {
                        continue;
                    }
                }

                $objects[$name] = new $class();
            }

            foreach ($args as $key => $value) {
                $objects[$name]->set($key, $value);
            }
        }

        return $objects;
    }

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

