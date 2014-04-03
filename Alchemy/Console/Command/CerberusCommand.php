<?php
namespace Alchemy\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Alchemy\Config;
use Alchemy\Component\Cerberus\Cerberus;
use Alchemy\Service\CerberusServiceProvider;

/**
 * Task for executing projects serve
 *
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link    www.phpalchemy.org
 * @since   1.0
 * @version $Revision$
 * @author  Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class CerberusCommand extends Command
{
    protected $config = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        parent::__construct();

        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    }

    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('cerberus:init')
        ->setDescription('Init Cerberus database schema.')
        ->setDefinition(array(
            new InputOption(
                'engine', 'mysql', InputOption::VALUE_OPTIONAL,
                'Data Base Engine', ''
            ),
//            new InputOption(
//                'v', '', InputOption::VALUE_OPTIONAL,
//                'Verbose mode', ''
//            ),
        ))
        ->setHelp('Cerberus Init');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vendorDir = $this->config->get("app.root_dir") . "/vendor";
        $bin = $this->config->get("app.root_dir") . "/vendor/phpalchemy/cerberus/bin/cerberus";

        if (! file_exists($bin) || ! class_exists('\Alchemy\Component\Cerberus\Cerberus')) {
            throw new \RuntimeException(sprintf(
                "Cerberus Component is not installed or not loaded!" . PHP_EOL
            ));
        }

        $config = $this->config->getSection("cerberus");
        if (empty($config)) {
            $config = $this->config->getSection("database");
        }
        if (empty($config)) {
            throw new \RuntimeException("Database configuration is missing.");
        }

        $config = CerberusServiceProvider::configure($config);

        $output->writeln("PhpAlchemy Helper for Cerberus RBAC/Auth. Component - ver. 1.0" . PHP_EOL);
        echo PHP_EOL;

        $port = isset($config["db-port"]) ? "--db-port=" . $config["db-port"] : "";

        $command = sprintf("%s build --db-engine=%s --db-name=%s --db-host=\"%s\" --db-user=%s --db-password=%s %s",
            $bin, $config["db-engine"], $config["db-name"], $config["db-host"], $config["db-user"], $config["db-password"], $port);

        system($command);
        $output->writeln("<info>DONE</info>");

        echo PHP_EOL;
    }
}

