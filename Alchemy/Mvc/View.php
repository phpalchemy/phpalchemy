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
    protected $_data     = array();

    /**
     * Contains the absolute path where the engine can found all templates
     *
     * @var string
     */
    protected $_templateDir = '';

    /**
     * Contains the absolute path where the engine store the cache files
     *
     * @var string
     */
    protected $_cacheDir = '';

    /**
     * String to store the output string that is sent by http response
     *
     * @var string
     */
    protected $_content  = '';

    /**
     * Relative path of template file
     *
     * @var string
     */
    protected $_tpl  = '';

    /**
     * Cache flag to specify if templating cache is enabled or not
     *
     * @var string
     */
    protected $_cacheEnabled = false;

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
        $this->_tpl = $tpl;
    }

    /**
     *
     */
    public function getTpl()
    {
        return $this->_tpl;
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

        $this->_data[$name] = $value;
    }

    /**
     * Gets a variable that was previously assigned
     *
     * @param  string $name  name or key to store teh value passed
     * @param  string $value variable value
     */
    public function getVar($name)
    {
        if (!isset($this->_data[$name])) {
            throw new \InvalidArgumentException("Variable '$name' doesn't exist.");
        }

        return $this->_data[$name];
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
        ob_start();
        $this->render();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Render the output string
     * Needs to override
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
        $this->_templateDir = $path;
    }

    /**
     * Gets the templates files base path
     *
     * @return string returns the templates base path
     */
    public function getTemplateDir()
    {
        return $this->_templateDir;
    }

    /**
     * @param string $path cache directory path
     */
    public function setCacheDir($path)
    {
        $this->_cacheDir = $path;
    }

    /**
     * Gets cache path
     */
    public function getCacheDir()
    {
        return $this->_cacheDir;
    }

    public function enableCache($value)
    {
        $this->_cacheEnabled = $value === true;
    }
}