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
class UnsetScopeCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('scope:unset')
        ->setDescription('Unset an determinated vendor scope for Php Alchemy console')
        ->setDefinition(array(
            new InputArgument(
                'scope', InputArgument::REQUIRED,
                'scope supported: [doctrine|propel|activerecords|mongodb]'
            )/*,
            new InputOption(
                'port', null, InputOption::VALUE_OPTIONAL,
                'Set an alternative http port', '3000'
            )*/
        ))
        ->setHelp('any supported scope for a supported vendor, Possible Values: doctrine|propel|activerecords|mongodb');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        //$conn = $this->getHelper('db')->getConnection();
        if (($scope = $input->getArgument('scope')) === null) {
            throw new \RuntimeException("Argument 'scope' is required in order to execute this command correctly.");
        }

        $conf = \unserialize(\file_get_contents(Registry::get('core.tmpFile')));
        unset($conf['console.scope']);

        \file_put_contents(Registry::get('core.tmpFile'), \serialize($conf));
        //\Registry::set('core.tmpFile');
        echo 'Done!';
        echo PHP_EOL;
    }
}
