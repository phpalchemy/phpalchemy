<?php
namespace Alchemy\Component\WebAssets\Filter;

class JsMinPlusFilter implements FilterInterface
{
    public function __construct()
    {
        include_once 'lib/JSMinPlus.php';
    }

    public function apply($content)
    {
        return \JSMinPlus::minify($content);
    }
}
