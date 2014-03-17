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
        defined("DS") || define("DS", DIRECTORY_SEPARATOR);
        empty($data) || $this->load($data);
    }

    /**
     * Set a setting on configuration object
     *
     * @param string $name Name of setting variable.
     * @param mixed $value Mixed value to store on configuration file.
     * @throws \Exception
     */
    public function set($name, $value)
    {
        if (!is_string($name)) {
            throw new \Exception("Invalid configuration key.");
        }

        $this->config[$name] = $value; //$this->prepare($value, $name);
    }

    /**
     * Get a setting from configuration object
     *
     * @param  string $name Name of setting variable.
     * @param  mixed $default A default value to be returned is the setting doesn't exist.
     * @throws \Exception
     * @return mixed           The setting value if it exists, if doesn't exist the default value passed
     *                         will be returned (if it was set, if doesn't a exception will be thrown).
     */
    public function get($name, $default = null)
    {
        if (empty($default) && !$this->exists($name)) {
            throw new \Exception("Configuration Missing: '$name' is not defined.");
        }

        return $this->exists($name) ? $this->prepare($this->config[$name], $name) : $default;
    }

    /**
     * Returns a collection of configurations by its section prefix
     *
     * @param $targetSection
     * @return array
     */
    public function getSection($targetSection)
    {
        $config = array();

        foreach ($this->config as $key => $value) {
            if (substr($key, 0, strlen($targetSection)) . "." == "$targetSection.") {
                $varname = strpos($key, '.') !== false ? ltrim(substr($key, strlen($targetSection)), '.') : $key;
                $config[$varname] = $this->get($key);
            }
        }

        return $config;
    }

    /**
     * Verify if a configuration exists
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->config);
    }

    /**
     * Verify if a configuration has empty value
     *
     * @param $name
     * @return bool
     */
    public function isEmpty($name)
    {
        return $this->get($name) == "";
    }

    /**
     * Load configuration data from a ini file and store on self::config array
     *
     * @param string $iniFilename Absolute ath to read the ini file
     * @throws \Exception
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

    /**
     * Loads configuration data from array
     * @param array $configList
     */
    private function loadFromArray($configList)
    {
        foreach ($configList as $section => $config) {
            foreach ($config as $key => $value) {
                $this->set($section . '.' . $key, $value);
            }
        }
    }

    /**
     * Loads configuration from array or string sources
     *
     * @param string|array $value
     */
    public function load($value = null)
    {
        if (is_array($value)) {
            $this->loadFromArray($value);
        } elseif (is_string($value) && file_exists($value)){
            $this->loadFromFile($value);
        }
    }

    /**
     * Get all configuration data - alias of self::all()
     *
     * @return array|mixed
     */
    public function getAll()
    {
        return $this->prepare($this->config);
    }

    /**
     * Get all configuration data
     *
     * @return array|mixed
     */
    public function all()
    {
        return $this->getAll();
    }

    /**
     * Prepare configurations data that depends of another configuration value
     * like: some_dir = "%app.root_dir%/Application"
     *
     * @param $value
     * @return array|mixed
     * @throws \Exception
     */
    public function prepare($value)
    {
        if (is_string($value) && preg_match_all('/\%([^\%]+)\%/', $value, $match)) {
            foreach ($match[1] as $match) {
                try {
                    $value = str_replace("%{$match}%", $this->get($match), $value);
                } catch (\Exception $e) {
                    throw new \Exception("Configuration Missing for %" . $match . "%");
                }
            }
        } elseif (is_array($value)) {
            $result = array();

            foreach ($value as $key => $item) {
                $result[$key] = $this->prepare($item);
            }

            $value = $result;
        }

        return $value;
    }
}

