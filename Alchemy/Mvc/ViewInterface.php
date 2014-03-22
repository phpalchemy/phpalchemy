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

    /**
     * @return string parsed template output
     */
    public function getOutput();

    /**
     * Render Parsed Template
     * @return string
     */
    public function render();

    /**
     * Sets UI Element Content
     * @param string $elementId
     * @param string $elementContent
     * @return mixed
     */
    public function setUiElement($elementId, $elementContent);
}

