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
    private $config     = array();
    private $appPath    = '';
    private $configPath = '';

    private $appIniFile = '';
    private $envIniFile = '';

    public function __construct()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    }

    /**
     * Init read configuration from ini files
     */
    public function init()
    {
        // prepare configuration
        $this->prepare();

        //load configuration application ini file
        $this->loadAppConfFile();

        //load configuration environment ini file
        $this->loadEnvConfFile();
    }

    public function setAppPath($path)
    {
        $this->appPath = rtrim($path, DS) . DS;
    }

    public function getAppPath()
    {
        if (empty($this->appPath)) {
            throw new \Exception("Missing configuration for 'Application Path'!");
        }

        return $this->appPath;
    }

    public function setConfigPath($path)
    {
        $this->configPath = rtrim($path, DS) . DS;
    }

    public function getConfigPath()
    {
        if (empty($this->configPath)) {
            throw new \Exception("Missing configuration for 'Application Config Path'!");
        }

        return $this->configPath;
    }

    public function setAppIniFile($path)
    {
        $this->appIniFile = $path;
    }

    public function getAppIniFile()
    {
        if (empty($this->appIniFile)) {
            throw new \Exception("Application ini file ({$this->appIniFile}) is missing!");
        }

        return $this->appIniFile;
    }

    public function setEnvIniFile($path)
    {
        $this->envIniFile = $path;
    }

    public function getEnvIniFile()
    {
        if (empty($this->envIniFile)) {
            throw new \Exception("'Environment ini file' is missing!");
        }

        return $this->envIniFile;
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

        if (is_string($value) && strpos($value, '%project_dir%') !== false) {
            $value = str_replace('%project_dir%', rtrim($this->getAppPath(), DS), $value);
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
        if (empty($default) && !isset($this->config[$name])) {
            throw new \Exception(get_class($this) . " - Configuration doesn't exist for key: $name");
        }

        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

    public function exists($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Load Application Configuration ini file
     */
    private function loadAppConfFile()
    {
        $this->loadFromFile($this->getAppIniFile());
    }

    /**
     * Load Environment Configuration ini file
     */
    private function loadEnvConfFile()
    {
        $this->loadFromFile($this->getEnvIniFile());
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

        foreach ($configList as $section => $config) {
            foreach ($config as $key => $value) {
                $this->set("$section.$key", $value);
            }
        }
    }

    private function prepare()
    {
        if (empty($this->configPath)) {
            $this->setConfigPath($this->getAppPath() . 'config' . DS);
        }

        if (empty($this->appIniFile)) {
            $this->setAppIniFile($this->getConfigPath() . 'application.ini');
        }

        if (empty($this->envIniFile)) {
            $this->setEnvIniFile($this->getConfigPath() . 'env.ini');
        }
    }
}