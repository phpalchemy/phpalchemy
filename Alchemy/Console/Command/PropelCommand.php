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
class PropelCommand extends Console\Command\Command
{
    public $propel;

    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('propel')
        ->setDescription('Doctrine Scope: Connections Settings')
        ->setDefinition(array(
            new InputOption(
                'use', null, InputOption::VALUE_OPTIONAL, //VALUE_REQUIRED
                'connection'//, 'default'
            ),
            new InputArgument(
                'cmd', InputOption::VALUE_OPTIONAL,
                'propel command'
            )
        ))
        ->setHelp('Propel');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
    	$this->propel->setRuntime(false);
    	
        //$value = $input->getOption('use');
        $command = $input->getArgument('cmd');
        $command = is_string($command) ? $command: null;
        $options = $this->propel->getDefaultOptions();
        
        $this->propel->callPhing($command, $options);
    }
    
}



