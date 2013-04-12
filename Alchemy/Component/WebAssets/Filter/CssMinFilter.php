<?php
namespace Alchemy\Component\WebAssets\Filter;

class CssMinFilter implements FilterInterface
{
    private $lineBreak = false;

    public function __construct()
    {
        include_once 'lib/cssmin.php';
    }

    public function setLineBreak($lineBreak)
    {
        $this->lineBreak = $lineBreak;
    }

    public function apply($content)
    {
        $cssMin = new \CSSmin();
        return $cssMin->run($content, $this->lineBreak);
    }
}
