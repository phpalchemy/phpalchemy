<?php
namespace Alchemy\Annotation;

class ServeUiAnnotation extends Annotation
{
    public $metaFile   = '';
    public $id         = '';
    public $attributes = array();
    public $bundle     = 'html';

    public function prepare()
    {
        if ($this->has('data')) {
            $this->attributes = $this->get('data');
            $this->remove('data');
        }

        if ($this->has('ui-bundle')) {
            $this->bundle = $this->get('ui-bundle');
            $this->remove('ui-bundle');
        }

        $params = $this->all();
        $keys = array_keys($params);

        if (count($keys) == 0) {
            throw new Exception("Runtime Error: UI Server: Meta UI file is mising.");
        }

        if (is_numeric($keys[0])) {
            $this->metaFile = $params[0];
        } else {
            $this->id = $keys[0];
            $this->metaFile = $params[$this->id];
        }
    }
}

