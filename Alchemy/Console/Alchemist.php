<?php
namespace Alchemy\Console;

use Alchemy\Config;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Application\Cli\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Alchemist extends Application
{
    protected $homeDir    = '';
    protected $currentDir = '';
    protected $config     = array();
    protected $app        = null;

    public function __construct(Config $config)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\');

        $this->config     = $config;
        $this->homeDir    = $this->config->get('phpalchemy.root_dir');
        $this->currentDir = $this->config->get('app.root_dir');

        $this->config->load($this->homeDir . DS . 'config' . DS . 'defaults.application.ini');

        if (file_exists($this->currentDir . DS . 'application.ini')) {
            $this->config->load($this->currentDir . DS . 'application.ini');
        }

        $this->config->set('phpalchemy.root_dir', $this->homeDir);

        $title    = "\n PHPAlchemy Framework Cli. ";
        $version  = '1.0';

        parent::__construct($title, $version);
        $this->setCatchExceptions(true);
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

    protected function prepare()
    {
        $helpers  = array();
        $commands = array();

        // adding command for a project environment
        if ($this->isAppDirectory()) {
            $commands[] = new \Alchemy\Console\Command\ServeCommand($this->config);
        }

        $helperSet = $this->getHelperSet();

        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }

        $this->addCommands($commands);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->prepare();
        return parent::run();
    }
}

