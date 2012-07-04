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
class EnvironmentSetCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('env:set')
        ->setDescription('Set an environment as default to this shell')
        ->setDefinition(array(
            new InputArgument(
                'env', InputArgument::OPTIONAL,
                'The environment name mapping to config/[env_name].ini'
            )/*,
            new InputArgument(
                'use', null, InputOption::VALUE_REQUIRED, //VALUE_OPTIONAL,
                'use', ''
            )*/
        ))
        ->setHelp('The environment name mapping to config/[env_name].ini');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        //$conn = $this->getHelper('db')->getConnection();
        //$scope = $input->getArgument('scope');
        $env = $input->getArgument('env');

        if (Alchemist::isValidEnv($env, SYS_CONFIG_DIR)) {
            Alchemist::setTmpEnv(SYS_NAME, $env, SYS_CONFIG_DIR);
            $output->writeln("<info>Using <info> <comment>$env</comment> <info>configuration.<info> ");
        }
        else {
            $output->writeln(PHP_EOL . "<error>Error: Invalid environment: **$env**</error>" . PHP_EOL);
        }
    }
}
