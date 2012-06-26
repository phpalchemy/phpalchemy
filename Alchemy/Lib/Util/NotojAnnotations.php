<?php
namespace Alchemy\Lib\Util;

use Notoj\ReflectionMethod;

class NotojAnnotations extends Annotations
{
    public function __construct($config = null)
    {
        if ($config instanceof \Alchemy\Config) {
            \Notoj\Notoj::enableCache($config->get('app.cache_dir') . DIRECTORY_SEPARATOR . "_annotations.php");
        }
    }

    public function getMethodAnnotationsObjects($class, $method)
    {
        $refl        = new ReflectionMethod($class, $method);
        $annotations = $refl->getAnnotations();
        $objects     = array();

        foreach ($annotations as $annotation) {
            $annotationClass = ucfirst($annotation['method']);
            $class = $this->defaultNamespace . $annotationClass . 'Annotation';

            if (empty($objects[$annotationClass])) {
                if (!class_exists($class)) {
                    throw new \Exception(sprintf('Annotation Class Not Found: %s', $class));
                }

                $objects[$annotationClass] = new $class();
            }

            foreach ($annotation['args'] as $key => $value) {
                $objects[$annotationClass]->set($key, $value);
            }
        }

        return $objects;
    }
}