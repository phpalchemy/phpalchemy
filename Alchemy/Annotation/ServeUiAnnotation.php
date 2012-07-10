<?php
namespace Alchemy\Annotation;

class ServeUiAnnotation extends Annotation
{
    public $metaFile  = '';
    public $name      = '';
    public $attributes = array();

    public function prepare()
    {
        if ($this->exists('data')) {
            $this->attributes = $this->get('data');
            $this->remove('data');
        }
        $params = $this->all();
        $keys = array_keys($params);

        if (count($keys) == 0) {
            throw new Exception("Runtime Error: UI Server: Meta UI file is mising.");
        }

        if (is_numeric($keys[0])) {
            $this->metaFile = $params[0];
        } else {
            $this->name = $keys[0];
            $this->metaFile = $params[$this->name];
        }
    }
}

