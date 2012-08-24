<?php
namespace Alchemy\Mvc;

use Alchemy\Component\WebAssets\Bundle;

/**
 * View
 *
 * This is the parent class to support view at MVC Pattern
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
abstract class View
{
    /**
     * Contains all variables that are available on template file
     *
     * @var array
     */
    protected $data = array();

    /**
     * Contains the absolute path where the engine can found all templates
     *
     * @var string
     */
    protected $templateDir = '';

    /**
     * Contains the absolute path where the engine store the cache files
     *
     * @var string
     */
    protected $cacheDir = '';

    /**
     * String to store the output string that is sent by http response
     *
     * @var string
     */
    protected $content = '';

    /**
     * Relative path of template file
     *
     * @var string
     */
    protected $tpl = '';

    /**
     * Cache flag to specify if templating cache is enabled or not
     *
     * @var string
     */
    protected $cache = false;

    /**
     * Charset Encodig
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    protected $uiElements = array();

    public $assetsHandler;

    /**
     * Debug flag to indicate to template engine is a debug environment
     * Defaults false
     *
     * @var bool
     */
    public $debug = false;

    /**
     * @param string $tpl template file
     * @param Bundle $assetsHandler Bundle Assets Handler obj
     */
    public function __construct($tpl = '', Bundle $assetsHandler = null)
    {
        $this->tpl = $tpl;
        $this->assetsHandler = $assetsHandler;
    }

    /**
     * Sets template file path (absolute or partial path)
     *
     * @param string $tpl contains the path of template file
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    /**
     * Gets template file
     */
    public function getTpl()
    {
        return $this->tpl;
    }

    /**
     * Sets the base path where the engine can be find all templates
     *
     * @param string $path contains the absolute path where templates are stored
     */
    public function setTemplateDir($path)
    {
        $this->templateDir = $path;
    }

    /**
     * Gets the templates files base path
     *
     * @return string returns the templates base path
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * Sets cache directory path
     *
     * @param string $path cache directory path
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = $dir;

        if (!is_dir($this->cacheDir)) {
            $this->createDir($this->cacheDir);
        }
    }

    /**
     * Gets cache directory path
     *
     * @return string contains cache directory path
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Enable/Disable templating cache
     *
     * @param  bool $value boolean value to enable or not cache
     */
    public function enableCache($value)
    {
        $this->cache = $value ? true: false;
    }

    /**
     * Enable/Disable debug mode of template engine
     *
     * @param  bool $value boolean value to enable or not debug mode
     */
    public function enableDebug($value)
    {
        $this->debug = $value ? true: false;
    }


    /**
     * Sets charset encoding for template engine
     * @param string $charset conatins a valid charset like UTF-8, ISO-8859-1 (latin), etc.
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Gets charset encoding
     *
     * @return string charset encoding
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Alias of method assign()
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function set($name, $value = null)
    {
        $this->assign($name, $value);
    }

    /**
     * Assings a variable to the template file
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function assign($name, $value = null)
    {
        if (is_string($name)) {
            return $this->data[$name] = $value;
        }

        if (is_array($name)) {
            return $this->assignFromArray($name);
        }

        throw new \InvalidArgumentException("Invalid data type for key, '" .gettype($name) . "' given.");
    }

    /**
     * Gets a variable that was previously assigned
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function get($name)
    {
        if (!isset($this->data[$name])) {
            throw new \InvalidArgumentException("Variable '$name' doesn't exist.");
        }

        return $this->data[$name];
    }

    /**
     * Gets final template parsed output string
     *
     * @return string parsed output
     */
    public function getOutput()
    {
        $output = '';
        \ob_start();
        $this->render();
        $output = \ob_get_contents();
        \ob_end_clean();

        return $output;
    }

    /**
     * Render the output string (To override by child class)
     */
    abstract public function render();

    /**
     * Multiple variable assignment
     *
     * @param  array $data associative array conatining variables, the keys are used as variables names
     */
    protected function assignFromArray($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                "Invalid data type: argument should be array, '" .gettype($name) . "' given."
            );
        }

        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }

    protected function createDir($strPath, $rights = 0777)
    {
        $folderPath = array($strPath);
        $oldumask    = umask(0);

        while (!@is_dir(dirname(end($folderPath)))
            && dirname(end($folderPath)) != '/'
            && dirname(end($folderPath)) != '.'
            && dirname(end($folderPath)) != ''
        ) {
            array_push($folderPath, dirname(end($folderPath)));
        }

        while ($parentFolderPath = array_pop($folderPath)) {
            if (!@is_dir($parentFolderPath)) {
                if (!@mkdir($parentFolderPath, $rights)) {
                    throw new \Exception("Templating Engine Error: Can't create folder '$parentFolderPath'");
                }
            }
        }

        umask($oldumask);
    }

    public function setUiElement($id, $element)
    {
        $this->uiElements[$id] = $element;
    }

    public function registerAssetHandler($assetHandler)
    {
        $this->assetsHandler = $assetHandler;
    }
}

