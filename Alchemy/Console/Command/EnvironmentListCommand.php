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
class EnvironmentListCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('env:list')
        ->setDescription('List all availables environments configurations')
        ->setDefinition(array())
        ->setHelp('List all availables environments configurations');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $currentEnv = \Alchemy\Common\Registry::get('project.environment');
        
        $files = glob('config' . DS . '*.ini');
        echo PHP_EOL;
        foreach ($files as $i => $envFile) {
            $env = str_replace('config' . DS, '', $envFile);
            $env = str_replace('.ini', '', $env);
            //$output->writeln("<info>[".($i+1)."]</info> <info>$env</info> => <comment>$envFile</comment>");
            
            $current = $currentEnv == $env ? "<comment>(current)</comment>" : ''; 
            
            $output->writeln("<info>- $env </info>$current");
        }
        echo PHP_EOL;
    }
}
