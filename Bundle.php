<?php
namespace Alchemy\Component\WebAssets;

use Alchemy\Component\WebAssets\Filter\FilterInterface;
use Alchemy\Component\WebAssets\Filter\CssMinFilter;
use Alchemy\Component\WebAssets\Asset;

class Bundle
{
    protected $outputFilename = '';
    protected $filter = null;
    protected $assets = array();

    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if ($arg instanceof Asset) {
                $this->assets[] = $arg;
            } elseif ($arg instanceof FilterInterface) {
                if ($this->filter === null) {
                    $this->setFilter($arg);
                } else {
                    throw new \RuntimeException("Runtime Error: A filter is already registered.");
                }
            }
        }
    }

    public function setOutputFilename($filename)
    {
        $this->outputFilename = $filename;
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function getOutput()
    {
        $contents = '';

        foreach ($this->assets as $asset) {
            $contents .= "/*! -- File : '".$asset->getFilename()."' -- */\n";
            $contents .= $asset->getOutput();
        }

        return $contents;
    }
}