<?php
namespace Alchemy\Console\Command;

use Alchemy\Common\Registry;
use Alchemy\Common\Alchemist;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

/**
 * Task for doctrine handling
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.phpalchemy.org
 * @since   1.0
 * @version $Revision$
 * @author  Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class DoctrineCommand extends Console\Command\Command
{   
    public $doctrine;
    
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('doctrine:connection')
        ->setDescription('Doctrine Scope: Connections Settings')
        ->setDefinition(array(
            new InputOption(
                'use', null, InputOption::VALUE_REQUIRED, //VALUE_OPTIONAL,
                'connection'//, 'default'
            ),
            new InputArgument(
                'list', InputOption::VALUE_OPTIONAL,
                'list available connections'
            )
        ))
        ->setHelp('Doctrine settings');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $value = $input->getOption('use');
        $list = $input->getArgument('list');
        $ems = $this->doctrine->getEntityManagersList();

        $conf = @unserialize(\file_get_contents(Registry::get('core.tmpFile')));
        $doctrineUsingConn = isset($conf["console.scope.doctrine.use"]) ? $conf["console.scope.doctrine.use"] : 'default';

        //verifying if there is one cnn configuration at least
        if (count($ems) == 0) {
            $output->writeln("* <error>There are not any connection available, please check your doctrine.yaml configuration file</error>");
        }

        // list available connections
        if ($list == 'list') {
            foreach ($ems as $cnn) {
                if($doctrineUsingConn == $cnn)
                    $output->writeln("* <info>$cnn</info>");
                else 
                    $output->writeln('  '.$cnn);
            }
            exit(0);
        }

        // show current connection that is using
        if ($value == '') {
            $output->writeln("[Doctrine Scope]\nUsing: <info>$doctrineUsingConn</info> connection");
            exit(0);
        }
        
        // show c
        // verifying if the environment is available
        if (!in_array($value, $this->doctrine->getEntityManagersList())) {
            $output->writeln("<error>Doctrine: The \"$value\" connection doesn't exist!</error>");
            exit(0);
        }
        
        $conf["console.scope.doctrine.use"] = $value;
        
        // saving configuration on tmp file
        \file_put_contents(Registry::get('core.tmpFile'), \serialize($conf));

        $output->writeln("");
        $sconf = @unserialize(\file_get_contents(Registry::get('core.tmpFile')));
        $output->write("* Writting Scope Setting..............");

        if ($sconf["console.scope.doctrine.use"] == $conf["console.scope.doctrine.use"])
            $output->writeln("<info>DONE!</info>");
        else
            $output->writeln("<error>FAILED!</error>");
                
        echo \Alchemy\Common\r($conf) . PHP_EOL . PHP_EOL;
    }
}
