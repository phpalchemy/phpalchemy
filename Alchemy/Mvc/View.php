<?php
namespace Alchemy\Mvc;

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
class View
{
    /**
     * Contains all variables that are available on template file
     *
     * @var array
     */
    protected $data     = array();

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
    protected $content  = '';

    /**
     * Relative path of template file
     *
     * @var string
     */
    protected $tpl  = '';

    /**
     * Cache flag to specify if templating cache is enabled or not
     *
     * @var string
     */
    protected $cacheEnabled = false;

    /**
     * @param string $tpl template file
     */
    public function __construct($tpl = '')
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        if (!empty($tpl)) {
            $this->setTpl($tpl);
        }
    }

    /**
     * @param unknown_type $tpl
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    /**
     *
     */
    public function getTpl()
    {
        return $this->tpl;
    }

    /**
     * Assings a variable to the template file
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function assign($name, $value)
    {
        if (is_array($name)) {
            return $this->assignFromArray($name);
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException("Invalid data type '" .gettype($name) . "' for key.");
        }

        $this->data[$name] = $value;
    }

    /**
     * Gets a variable that was previously assigned
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function getVar($name)
    {
        if (!isset($this->data[$name])) {
            throw new \InvalidArgumentException("Variable '$name' doesn't exist.");
        }

        return $this->data[$name];
    }

    /**
     * Multiple variable assignment
     *
     * @param  array $data associative array conatining variables, the keys are used as variables names
     */
    private function assignFromArray($data)
    {
        foreach ($name as $key => $value) {
            $this->assign($key, $value);
        }
    }

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
     * Render the output string
     * To override by childs classes
     */
    public function render()
    {
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
     * @param string $path cache directory path
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = $dir;

        if  (!is_dir($this->cacheDir)) {
            if (!@mkdir($this->cacheDir)) {
                throw new \Exception("Could't create template engine cache directory: '$dir'");
            }
        }
    }

    /**
     * Gets cache path
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function enableCache($value)
    {
        $this->cacheEnabled = $value === true;
    }
}