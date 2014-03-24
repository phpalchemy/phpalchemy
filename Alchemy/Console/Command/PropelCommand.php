<?php
namespace Alchemy\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Alchemy\Config;

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
class PropelCommand extends Command
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
        $this->setName('propel')
        ->setDescription('Helper for Propel, build fast the model classes and sql schema.')
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
        ->setHelp('Propel ORM Helper');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bin = $this->config->get("app.root_dir") . "/vendor/propel/propel/bin/propel";

        $inputDir = $this->config->get("propel.input_dir", $this->config->get("app.database_schema_dir"));
        $outputClassDir = $this->config->get("propel.output_dir", $this->config->get("app.model_dir"));
        $outputSchemaDir = $this->config->get("propel.schema_dir", $this->config->get("app.database_schema_dir"));
        $dbEngineConf = $this->config->get("database.engine", "");

        //accepted values for db engine
        $dbEngines = array("mysql", "pgsql", "mssql", "oracle", "sqlite");

        // Resolving database platform for propel
        switch ($dbEngineConf) {
            case "mysql": $dbEngine = "MysqlPlatform"; break;
            case "pgsql": $dbEngine = "PgsqlPlatform"; break;
            case "mssql": $dbEngine = "MssqlPlatform"; break;
            case "oracle": $dbEngine = "OraclePlatform"; break;
            case "sqlite": $dbEngine = "SqlitePlatform"; break;
            case "sqlsrv": $dbEngine = "SqlsrvPlatform"; break;

            default: throw new \RuntimeException(sprintf(
                "Missing or invalid database engine on .ini configuration file." . PHP_EOL .
                "Accepted values: [%s]" . PHP_EOL .
                "Given: %s" . PHP_EOL,
                implode("|", $dbEngines),
                $dbEngineConf
            ));
        }

        if (! file_exists($bin)) {
            throw new \RuntimeException("Seems propel is not installed in your project.");
        }

        $output->writeln("PhpAlchemy Helper for Propel2 ver. 1.0" . PHP_EOL);
        $output->writeln("Propel input dir: " . $inputDir);
        $output->writeln("Propel output class dir: " . $outputClassDir);
        $output->writeln("Propel output sql dir: " . $outputSchemaDir);
        echo PHP_EOL;

        $commands = array();
        $commands["model"] = sprintf("%s model:build --input-dir=%s --output-dir=%s", $bin, $inputDir, $outputClassDir);
        $commands["sql"] = sprintf("%s sql:build --input-dir=%s --output-dir=%s --platform=%s", $bin, $inputDir, $outputSchemaDir, $dbEngine);

        foreach ($commands as $build => $command) {
            $output->write(sprintf("- Building %s ... ", $build));
            system($command);
            $output->writeln("<info>DONE</info>");
        }

        //proc_open($command, array(STDIN, STDOUT, STDERR), $pipes);
        //var_dump($pipes);

        echo PHP_EOL;
    }
}

