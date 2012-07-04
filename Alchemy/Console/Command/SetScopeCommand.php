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
class SetScopeCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('scope:set')
        ->setDescription('Set an determinated vendor scope ')
        ->setDefinition(array(
            new InputArgument(
                'scope', InputArgument::REQUIRED,
                'scope  supported: '
            ),
            new InputOption(
                'use', null, InputOption::VALUE_REQUIRED, //VALUE_OPTIONAL,
                'use', ''
            )
        ))
        ->setHelp('any supported scope for a supported vendor, Possible Values: doctrine|propel|activerecords|mongodb');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        //$conn = $this->getHelper('db')->getConnection();
        $scope = $input->getArgument('scope');
        $value = $input->getOption('value');

        switch ($scope) {
            case 'doctrine':
                $conf["console.scope.$scope"] = $value;
                break;
        }
        
        
        \file_put_contents(Registry::get('core.tmpFile'), \serialize($conf));
        print_r($conf);
        echo \serialize($conf);
        echo PHP_EOL;
    }
}
