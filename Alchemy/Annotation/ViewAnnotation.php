<?php
namespace Alchemy\Annotation;

class ViewAnnotation
{
    public $template = '';
    public $engine   = '';

    public function __construct($param)
    {
        if (is_string($param)) {
            $this->template = $param;
        } elseif (is_array($param)){
            if (isset($param['template'])) { // long name
                $this->template = $param['template'];
            } elseif (isset($param['tpl'])) { // short name
                $this->template = $param['tpl'];
            }

            if (isset($param['engine'])) {
                $this->engine = $param['engine'];
            }
        }
    }
}