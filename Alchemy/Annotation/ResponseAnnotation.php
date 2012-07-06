<?php
namespace Alchemy\Annotation;

class ResponseAnnotation extends Annotation
{
    public function getHeaders()
    {
        return $this->data;
    }
}

