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

    public function getClassAnnotations($className)
    {
        $reflection  = new ReflectionClass($className);

        return $this->convertAnnotationObj($reflection);
    }

    public function getMethodAnnotations($className, $methodName)
    {
        $reflection  = new ReflectionMethod($className, $methodName);

        return $this->convertAnnotationObj($reflection);
    }

    public function getMethodAnnotationsObjects($class, $method)
    {
        $annotations = $this->getMethodAnnotations($class, $method);
        $objects     = array();

        foreach ($annotations as $class => $args) {
            $class = $this->defaultNamespace . ucfirst($class) . 'Annotation';

            if (empty($objects[$class])) {
                if (!class_exists($class)) {
                    if ($this->strict) {
                        throw new \Exception(sprintf('Annotation Class Not Found: %s', $class));
                    } else {
                        continue;
                    }
                }

                $objects[$class] = new $class();
            }

            foreach ($args as $key => $value) {
                $objects[$class]->set($key, $value);
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

