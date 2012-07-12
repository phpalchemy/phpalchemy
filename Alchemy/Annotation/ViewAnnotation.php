<?php
namespace Alchemy\Annotation;

class ViewAnnotation extends Annotation
{
    public $template = '';
    public $engine   = '';

    public function prepare()
    {
        if ($this->has('0')) {
            $this->template = $this->get('0');
        } elseif ($this->has('file')) {
            $this->template = $this->get('file');
        } elseif ($this->has('template')) {
            $this->template = $this->get('template');
        }

        $this->engine = $this->get('engine', '');
    }
}

