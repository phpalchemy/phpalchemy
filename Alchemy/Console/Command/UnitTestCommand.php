<?php
namespace Alchemy\Console\Command;

use Alchemy\Common\Registry;
use Alchemy\Common\Alchemist;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

/**
 * Task for executing projects serve
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.phpalchemy.org
 * @since   1.0
 * @version $Revision$
 * @author  Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class UnitTestCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('lime')
        ->setDescription('Unit Testing with Lime2')
        ->setDefinition(array(
            new InputArgument(
                'help', InputArgument::OPTIONAL,
                'Lime help.'
            ),
            new InputOption(
                'init', null, InputOption::VALUE_OPTIONAL,
                'init'
            ),
            new InputOption(
                'test', null, InputOption::VALUE_OPTIONAL,
                'test argument', ''
            )
        ))
        ->setHelp('unit test');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        require_once VENDOR_LIME_HOME . '/lib/LimeAutoloader.php';
        //$port = $input->getArgument('port');
        $testArg = $input->getOption('test');
        //$output->write(PHP_EOL . 'unit test' . PHP_EOL);
        
        \LimeAutoloader::register();
        
        if (!file_exists(getcwd() . DS . 'lime.config.php')) {
            if (!@copy(ALCHEMY_TEMPLATES_DIR.'lime.config.tpl', getcwd() . DS . 'lime.config.php')) {
                throw new \Exception('I can\'t create lime config file');
            }
        }
        
        $cli = new \LimeCli();
        global $argv;
        unset($argv[0]);
        unset($argv[1]);
        $args = array();
        $args[0] = './lime';
        
        foreach ($argv as $arg) {
            array_push($args, $arg);
        }
        
        if (isset($args[1])) {
            switch($args[1]){
                case 'help': $args[1] = '--help'; break;
            }
        }
        
        exit($cli->run($args));
    }
}
