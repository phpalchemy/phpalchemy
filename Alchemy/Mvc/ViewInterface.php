<?php
namespace Alchemy\Mvc;

interface ViewInterface
{
    public function setTpl($template);
    public function getTpl();
    public function setTemplateDir($path);
    public function getTemplateDir();
    public function setCacheDir($dir);
    public function getCacheDir();
    public function enableCache($value);
    public function enableDebug($value);
    public function setCharset($charset);
    public function getCharset();
    public function assign($name, $value);
    public function get($name);
    public function getOutput();
    public function render();
}

