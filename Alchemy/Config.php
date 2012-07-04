<?php
namespace Alchemy;

/**
 * Class Config
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class Config
{
    protected $config = array();

    public function __construct($data = array())
    {
        empty($data) || $this->load($data);
    }

    /**
     * Set a setting on configuration object
     *
     * @param string $name  Name of setting variable.
     * @param mixed  $value Mixed value to store on configuration file.
     */
    public function set($name, $value)
    {
        if (!is_string($name)) {
            throw new \Exception("Invalid configuration key.");
        }

        if (is_string($value) && preg_match('/.*\%(.+\..+)\%.*/', $value, $match)) {
            try {
                $value = str_replace("%{$match[1]}%", $this->get($match[1]), $value);
            } catch(\Exception $e) {
                throw new \Exception(
                    "Configuration Missing for %" . $match[1] . "% for " .
                    "key: '$name', with value: '$value'"
                );
            }

            if (substr($name, -4) === '_dir') {
                $value = rtrim($value, DS);
            }

            $this->config[$name] = $value;
        }

        $this->config[$name] = $value;
    }

    /**
     * Get a setting from configuration object
     *
     * @param  string $name    Name of setting variable.
     * @param  mixed  $default A default value to be returned is the setting doesn't exist.
     * @return mixed           The setting value if it exists, if doesn't exist the default value passed
     *                         will be returned (if it was set, if doesn't a exception will be thrown).
     */
    public function get($name, $default = null)
    {
        if (empty($default) && !$this->exists($name)) {
            throw new \Exception("Configuration Missing: '$name' is not defined.");
        }

        return $this->exists($name) ? $this->config[$name] : $default;
    }

    public function exists($name)
    {
        return array_key_exists($name, $this->config);
    }

    public function isEmpty($name)
    {
        return empty($this->config[$name]);
    }

    /**
     * Load configuration from a ini file and store on self::config array
     *
     * @param string $iniFilename Absolute ath to read the ini file
     */
    private function loadFromFile($iniFilename)
    {
        if (!file_exists($iniFilename)) {
            throw new \Exception("File $iniFilename doesn't exist.");
        }

        $configList = @parse_ini_file($iniFilename, true);

        if ($configList === false) {
            throw new \Exception("Parse Error: File $iniFilename has errors.");
        }

        $this->loadFromArray($configList);
    }

    private function loadFromArray($configList)
    {
        foreach ($configList as $section => $config) {
            foreach ($config as $key => $value) {
                if (substr_count($value, '%') !== 2) {
                    $this->set($section . '.' . $key, $value);
                    unset($configList[$section][$key]);
                }
            }
        }

        foreach ($configList as $section => $config) {
            foreach ($config as $key => $value) {
                $this->set($section . '.' . $key, $value);
            }
        }
    }

    public function load($value=null)
    {
        if (is_array($value)) {
            $this->loadFromArray($value);
        } elseif (is_string($value) && file_exists($value)){
            $this->loadFromFile($value);
        }
    }

    public function getAll()
    {
        return $this->config;
    }

    public function all()
    {
        return $this->getAll();
    }
}