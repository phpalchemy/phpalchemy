<?php
namespace Alchemy\Component\WebAssets\Filter;

class JsMinFilter implements FilterInterface
{
    public function apply($content)
    {
        return \CssMin::run($content, $this->lineBreak);
    }
}
