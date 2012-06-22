<?php
namespace Alchemy\Mvc;

interface ViewInterface
{
    function setTpl($template);
    function getTpl();
    function setTemplateDir($path);
    function getTemplateDir();
    function setCacheDir($dir);
    function getCacheDir();
    function enableCache($value);
    function enableDebug($value);
    function setCharset($charset);
    function getCharset();
    function assing($name, $value);
    function get();
    function getOutput();
    function render();
}