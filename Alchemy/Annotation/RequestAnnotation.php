<?php
namespace Alchemy\Annotation;

use Alchemy\Component\Http\Request;

class RequestAnnotation extends Annotation
{
    protected $allowedMethods = array();
    protected $deniedMethods = array();

    public function prepare(Request $request)
    {
        echo '<pre>';
        print_r($request);
    }
}

