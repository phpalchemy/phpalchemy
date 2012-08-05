<?php
namespace Alchemy\Component\WebAssets\Filter;

class CssMinFilter implements FilterInterface
{
    private $lineBreak = 2000;

    public function __construct()
    {
        include_once 'lib/cssmin.php';
    }

    public function setLineBreak($lineBreakNum)
    {
        $this->lineBreak = $lineBreakNum;
    }

    public function apply($content)
    {
        $cssMin = new \CSSmin();
        return $cssMin->run($content, $this->lineBreak);
    }
}
