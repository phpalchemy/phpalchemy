<?php
namespace Alchemy\Annotation;

class RegisterAnnotation extends Annotation
{
    protected $sources = array();

    public function prepare()
    {
        foreach ($this->all() as $key => $value) {
            if (! is_array($value)) {
                $sources[$key][] = $value;
            } else {
                if (! is_array($sources[$key])) {
                    $sources[$key] = array();
                }

                $sources[$key] = array_merge($sources[$key], $value);
            }
        }

        $this->data = $sources;
    }
}

