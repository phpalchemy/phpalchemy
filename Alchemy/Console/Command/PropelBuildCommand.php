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
class PropelBuildCommand extends Command
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
        $this->setName('propel:build')
        ->setDescription('Fast build of model classes and sql schema.')
        ->setDefinition(array(
            new InputOption(
                'engine', 'mysql', InputOption::VALUE_OPTIONAL,
                'Data Base Engine', ''
            )
        ))
        ->setHelp('Propel ORM Helper BUILD');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bin = $this->config->get("app.root_dir") . "/vendor/propel/propel/bin/propel";

        $inputDir = $this->config->get("propel.input_dir", $this->config->get("app.database_schema_dir"));
        $outputSchemaDir = $this->config->get("propel.schema_dir", $this->config->get("app.database_schema_dir"));
        $dbEngineConf = $this->config->get("database.engine", "");

        if (! file_exists($outputSchemaDir . "/schema.xml")) {
            throw new \RuntimeException(sprintf(
                "Propel Schema file is missing!" . PHP_EOL .
                "In directory: %s", $outputSchemaDir
            ));
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        if (! $dom->load($outputSchemaDir . "/schema.xml") || ! ($root = $dom->getElementsByTagName('database'))) {
            $errors = libxml_get_errors();
            $errorMessage = "";

            foreach ($errors as $error) {
                echo self::display_xml_error($error, file_get_contents($outputSchemaDir . "/schema.xml"));
            }

            libxml_clear_errors();

            throw new \RuntimeException("Can't parse schema.xml file, it contains errors!" . PHP_EOL . $errorMessage);
        }

        $namespace = $root->item(0)->getAttribute("namespace");
        $defaultClassDir = empty($namespace)? $this->config->get("app.model_dir"): $this->config->get("app.app_root_dir");
        $outputClassDir = $this->config->get("propel.class_dir", $defaultClassDir);


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
        $output->writeln("<comment>Directories:</comment>");

        $output->writeln("       Input dir: " . $outputSchemaDir);
        $output->writeln("Output class dir: " . $outputClassDir);
        $output->writeln("  Sql output dir: " . $outputSchemaDir);
        echo PHP_EOL;

        $commands = array();
        $commands["model"] = sprintf("%s model:build --input-dir=%s --output-dir=%s", $bin, $inputDir, $outputClassDir);
        $commands["sql"] = sprintf("%s sql:build --input-dir=%s --output-dir=%s --platform=%s", $bin, $inputDir, $outputSchemaDir, $dbEngine);

        $output->writeln("<comment>Execution:</comment>");
        foreach ($commands as $build => $command) {

            $output->write(sprintf(" %15s ... ", "Build $build"));
            passthru($command, $stat);
            $statMessage = $stat == 0 ? "<info>done</info>": "<error>failed</error>";
            $output->writeln($statMessage);
        }

        echo PHP_EOL;
    }

    public static function display_xml_error($error, $xml)
    {
        $return  = $xml[$error->line - 1] . "\n";
        $return .= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $return .= "\n  File: $error->file";
        }

        return "$return\n\n---\n\n";
    }
}

