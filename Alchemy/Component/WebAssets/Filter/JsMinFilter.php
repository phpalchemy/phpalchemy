<?php
namespace Alchemy\Component\WebAssets\Filter;

class JsMinFilter implements FilterInterface
{
    public function __construct()
    {
        include_once 'lib/jsmin.php';
    }

    public function apply($content)
    {
        return \JSMin::minify($content);
    }
}
