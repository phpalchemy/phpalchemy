<?php
namespace Alchemy\Annotation;

class ViewAnnotation extends Annotation
{
    public $template = '';
    public $engine   = '';

    public function resolveTemplateName()
    {
        if ($this->exists('0')) {
            $this->template = $this->get('0');
        } elseif ($this->exists('file')) {
            $this->template = $this->get('file');
        } elseif ($this->exists('template')) {
            $this->template = $this->get('template');
        }

        $this->engine = $this->get('engine', '');
    }
}

