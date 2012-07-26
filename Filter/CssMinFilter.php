<?php
namespace WebAssets\Filter;

class CssMinFilter implements FilterInterface
{
    private $lineBreak = 2000;

    public function setLineBreak($lineBreakNum)
    {
        $this->lineBreak = $lineBreakNum;
    }

    public function apply($content)
    {
        return \CssMin::run($content, $this->lineBreak);
    }
}
