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
class PropelInitCommand extends Command
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
        $this->setName('propel:init')
        ->setDescription('Init Project DB, create db (if not exists) and tables.')
        ->setDefinition(array(
            new InputOption(
                'engine', 'mysql', InputOption::VALUE_OPTIONAL,
                'Data Base Engine', ''
            )
        ))
        ->setHelp('Propel ORM Helper INIT DB');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $propelBin = $this->config->get("app.root_dir") . "/vendor/propel/propel/bin/propel";

        $inputDir = $this->config->get("propel.input_dir", $this->config->get("app.database_schema_dir"));
        $schemaDir = $this->config->get("propel.schema_dir", $this->config->get("app.database_schema_dir"));
        $dbEngineConf = $this->config->get("database.engine", "");
        $dbHost = $this->config->get("database.host", ""); 
        $dbName = $this->config->get("database.dbname", ""); 
        $dbUser = $this->config->get("database.user", "");; 
        $dbPassword = $this->config->get("database.password", ""); 
        $dbPort = $this->config->get("database.port", "");;

        if (empty($dbName)) throw new \Exception("DB Name configuration missing.");
        if (empty($dbHost)) throw new \Exception("DB Host configuration missing.");
        if (empty($dbUser)) throw new \Exception("DB User configuration missing.");
        if (empty($dbPassword)) throw new \Exception("DB Password configuration missing.");

        if (! file_exists($schemaDir . "/schema.xml")) {
            throw new \RuntimeException(sprintf(
                "Propel Schema file is missing!" . PHP_EOL .
                "In directory: %s", $schemaDir
            ));
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        if (! $dom->load($schemaDir . "/schema.xml") || ! ($root = $dom->getElementsByTagName('database'))) {
            $errors = libxml_get_errors();
            $errorMessage = "";

            foreach ($errors as $error) {
                echo self::display_xml_error($error, file_get_contents($schemaDir . "/schema.xml"));
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

        if (! file_exists($propelBin)) {
            throw new \RuntimeException("Seems propel is not installed in your project.");
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        if (! $dom->load($schemaDir . "/schema.xml") || ! ($root = $dom->getElementsByTagName('database'))) {
            $errors = libxml_get_errors();
            $errorMessage = "";

            foreach ($errors as $error) {
                echo self::display_xml_error($error, file_get_contents($schemaDir . "/schema.xml"));
            }

            libxml_clear_errors();

            throw new \RuntimeException("Can't parse schema.xml file, it contains errors!" . PHP_EOL . $errorMessage);
        }

        $srcName = $root->item(0)->getAttribute("name");

        // prepare Data Base
        try {
            $dbh = new \PDO("$dbEngineConf:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        } catch (\Exception $e) {
            $dbh = new \PDO("$dbEngineConf:host=$dbHost", $dbUser, $dbPassword);
            $dbh->exec("CREATE DATABASE IF NOT EXISTS $dbName");
        }

        $output->writeln("PhpAlchemy Helper for Propel2 ver. 1.0" . PHP_EOL);
        $output->writeln("<comment>Target Data Base:</comment> $dbName");
        $output->write("<comment>Confirm:</comment> Model tables could exist, do you want overwrite? (Yes/n): ");

        $ans = rtrim(fgets(fopen("php://stdin","r")));
        if ($ans !== "Yes") {
            $output->writeln("<comment>Aborted!</comment>");
            echo PHP_EOL;
            exit(0);
        }

        $dbPort = empty($dbPort)? "": ";port=".$dbPort;
        $dsn = sprintf("%s:host=%s;dbname=%s;user=%s;password=%s%s", $dbEngineConf, $dbHost, $dbName, $dbUser, $dbPassword, $dbPort);
        $command = sprintf("%s sql:insert --input-dir=%s --connection=\"%s=%s\"", $propelBin, $schemaDir, $srcName, $dsn);

        echo "Building Data Base ... ";
        passthru($command, $stat);

        if ($stat === 0) {
            $output->writeln("<info>DONE</info>");
        } else {
            $output->writeln("<error>FAILED</error>");
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

