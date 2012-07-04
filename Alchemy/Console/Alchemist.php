<?php
namespace Alchemy\Console;

use Alchemy\Config;
use Symfony\Component\Console\Application;

class Alchemist
{
    protected $homeDir    = '';
    protected $currentDir = '';
    protected $config     = array();
    protected $app        = null;

    public function __construct(Config $config = null, Application $app)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\');

        $this->config     = $config;
        $this->homeDir    = $this->config->get('phpalchemy.root_dir');
        $this->currentDir = $this->config->get('app.root_dir');

        $this->config->load($this->homeDir . DS . 'config' . DS . 'defaults.application.ini');
        $this->config->set('phpalchemy.root_dir', $this->currentDir);

        $this->app = $app;
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

    protected function prepareApp()
    {
        $title    = ' -= PHPAlchemy Framework =-';
        $version  = '1.0';
        $helpers  = array();
        $commands = array();

        $this->app->setName($title);
        $this->app->setVersion($version);
        $this->app->setCatchExceptions(true);

        // adding command for a project environment
        if ($this->isAppDirectory()) {
            array_push($commands, new \Alchemy\Console\Command\ServeCommand());
        }

        $helperSet = $this->app->getHelperSet();

        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }

        $this->app->addCommands($commands);
    }

    public function run()
    {
        $this->prepareApp();

        $this->app->run();
    }
}