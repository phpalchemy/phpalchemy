<?php
namespace Alchemy\Component\WebAssets\Filter;

interface FilterInterface
{
    public function apply($content);
}