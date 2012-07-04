<?php
namespace Alchemy\Console;

use Alchemy\Config;

class Alchemist
{
    protected $homeDir    = '';
    protected $currentDir = '';
    protected $config     = array();

    public function __construct(Config $config = null)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\');

        $this->config     = $config;
        $this->homeDir    = $this->config->get('phpalchemy.root_dir');
        $this->currentDir = $this->config->get('app.root_dir');

        $this->config->load($this->homeDir . DS . 'config' . DS . 'defaults.application.ini');
        $this->config->set('phpalchemy.root_dir', $this->currentDir);
    }

    public function setCurrentDir($path)
    {
        $this->currentDir = rtrim($path, DS) . DS;
    }

    public function isAppDirectory()
    {
        if (!file_exists($this->currentDir . DS . 'application.ini')) {
            return false;
        }

        $this->config->load($this->currentDir . DS . 'config' . DS . 'application.ini');

        foreach ($this->config->all() as $key => $value) {
            if (substr($key, 0, 3) === 'app' && substr($key, -4) === '_dir' && substr($key, -9) !== 'cache_dir') {
                if (!is_dir($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function run()
    {
        if ($this->isAppDirectory())
            echo 'is an app dir.';
        else
            echo 'is not an app dir.';

        echo "\n";
    }
}