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
    protected $config     = array();
    protected $appIniFile = '';
    protected $envIniFile = '';

    private static $appDefaultsIniFile = 'defaults.application.ini';

    public function __construct($params = array())
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        if (empty($params['phpalchemy']['root_dir'])) {
            throw new \Exception("Configuration Missing: 'phpalchemy.root_dir' is not defined.");
        }

        if (empty($params['app']['root_dir'])) {
            throw new \Exception("Configuration Missing: 'app.root_dir' is not defined.");
        }

        // fix base paths
        $params['phpalchemy']['root_dir'] = realpath($params['phpalchemy']['root_dir']);
        $params['app']['root_dir']        = realpath($params['app']['root_dir']);

        $this->set('phpalchemy.root_dir', $params['phpalchemy']['root_dir']);
        $this->set('app.root_dir', $params['app']['root_dir']);

        unset($params['phpalchemy']['root_dir']);
        unset($params['app']['root_dir']);

        // load defaults configurations
        $this->loadFromFile($this->get('phpalchemy.root_dir').DS.'config'.DS.self::$appDefaultsIniFile);

        // load application config
        $this->loadFromArray($params);

        //load configuration environment ini file
        $this->loadEnvConfFile();
    }

    public function setAppRootDir($path)
    {
        $this->set('app.root_dir', $path);
    }

    public function getAppRootDir()
    {
        return $this->get('app.root_dir');
    }

    public function getAppConfigDir()
    {
        return $this->get('app.config_dir');
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
            $this->setEnvIniFile($this->getAppConfigDir() . DS . 'env.ini');
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

        $this->loadFromArray($configList);
    }

    private function loadFromArray($configList)
    {
        foreach ($configList as $section => $config) {
            foreach ($config as $key => $value) {
                $this->set("$section.$key", $value);
            }
        }
    }

    public function getAll()
    {
        return $this->config;
    }
}