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
class ServeCommand extends Command
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
        $this->setName('serve')
        ->setDescription('Serve project over http')
        ->setDefinition(array(
            new InputArgument(
                'environment', InputArgument::OPTIONAL,
                'Environment to startup', 'development'
            ),
            new InputOption(
                'host', null, InputOption::VALUE_OPTIONAL,
                'Set the server address', ''
            ),
            new InputOption(
                'port', null, InputOption::VALUE_OPTIONAL,
                'Set an alternative http port', '3000'
            )
        ))
        ->setHelp('Serve project over http');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host') == '' ? $this->config->get('dev_appserver.host') : $input->getOption('host');
        $port = $input->getOption('port') ? $this->config->get('dev_appserver.port') : $input->getOption('port');
        $env  = $input->getArgument('environment');

        $devServer  = $this->config->get('dev_appserver.name');
        $homeDir    = $this->config->get('phpalchemy.root_dir');
        $appName    = $this->config->get('app.name');
        $tmpDir     = $this->config->isEmpty('app.cache_dir') ?
                      rtrim(sys_get_temp_dir(), DS) . DS : $this->config->get('app.cache_dir') . DS;

        $phpCgiBin = $this->config->isEmpty('dev_appserver.php-cgi_bin') ?
                     $this->resolveBin('php-cgi') : $this->config->get('dev_appserver.php-cgi_bin');

        $docRoot = $this->config->get('app.root_dir') . '/web';

        // validations
        if (! is_dir($tmpDir)) {
            if (! mkdir($tmpDir, 0777, true)) {
                throw new \RuntimeException(sprintf(
                    "Runtime Error: Temporal directory '%s' does not exit, and is not possible create it.\n" .
                    "Check permissions for: ",
                    $tmpDir
                ));
            }
        }

        if (! is_dir($docRoot)) {
            throw new \InvalidArgumentException(sprintf(
                'Document root directory "%s" does not exist', $docRoot
            ));
        }

        if (! is_readable($docRoot)) {
            throw new \InvalidArgumentException(sprintf(
                'Document root directory "%s" is not readable', $docRoot
            ));
        }

        switch ($devServer) {
            case 'lighttpd':
                $devServerBin = $this->config->isEmpty('dev_appserver.lighttpd_bin') ?
                                $this->resolveBin($devServer) : $this->config->get('dev_appserver.lighttpd_bin');

                if (empty($phpCgiBin)) {
                    throw new \Exception("php-cgi binary not found!");
                }

                if (empty($devServerBin)) {
                    throw new \Exception("Seems Lighttpd is not installed!");
                }

                //setting lighttpd configuration variables
                $config = array();
                $config['doc_root']    = PHP_OS == 'WINNT' ? self::convertPathToPosix(SYS_DIR) : $docRoot;
                $config['host']        = $host;
                $config['port']        = $port;
                $config['tmp_dir']     = $tmpDir;
                $config['bin_path']    = $phpCgiBin;
                $config['socket_path'] = $tmpDir . "php.socket";
                $config['environment'] = $env;

                // load the lighttpd.conf template with the configurations
                $lighttpdTmpConfFile = $tmpDir . '_lighttpd.conf';
                $lighttpdConfContent = $this->loadTemplate(
                    $homeDir . DIRECTORY_SEPARATOR . 'templates'.DIRECTORY_SEPARATOR.'lighttpd.conf.tpl', $config
                );

                if (@file_put_contents($lighttpdTmpConfFile, $lighttpdConfContent) === false) {
                    throw new \Exception("Error while creating the lighttpd configuration file!");
                }

                $command = "$devServerBin -f $lighttpdTmpConfFile -D";
                
                break;

            case 'built-in':
                if (PHP_VERSION_ID < 50400) {
                    throw new \Exception("Built-in server needs php version 5.4.x");
                }

                chdir($docRoot);

                $routerFile = $tmpDir . 'router.php';
                $config = array('srvFile' => 'app.php');
                $content = $this->loadTemplate($homeDir . DS . 'templates' . DS . 'router.php.tpl', $config);

                if (@file_put_contents($routerFile, $content) === false) {
                    throw new \Exception("Error while creating temporal configuration file!");
                }

                $command = escapeshellcmd(sprintf('%s -S %s:%s %s', PHP_BINARY, $host, $port, $routerFile));
                
                break;
            default:
                throw new \Exception('Error: "dev_appserver" is not configurated yet.');
        }

        // if (PHP_OS == 'WINNT') {
        //     $iniConfig['phpcgi_bin'] = self::convertPathToPosix($iniConfig['phpcgi_bin']);
        //     //$iniConfig['lighttpd_bin'] = $iniConfig['lighttpd_bin'];
        //     $iniConfig['tmp_path'] = self::convertPathToPosix($tmpPath);
        // }

        if (empty($command)) {
            throw new \Exception('Error: Configuration missing!');
        }

        $output->writeln("\n--= PhpAlchemy Framework Cli  =--\n    (Running on " . self::getOs() . ')'. PHP_EOL);
        //$output->writeln('<comment>Using "'.$env.'" environment.</comment>');
        $output->writeln(sprintf('- The Project "<info>%s</info>" is running on port: <info>%s</info>', $appName, $port));
        $output->writeln("- URL: <info>http://$host:$port</info>");
        $output->writeln(PHP_EOL." (*) Press CTRL+C to stop the service.".PHP_EOL);

        //$lighttpdTmpConfFile = PHP_OS == 'WINNT' ? self::convertPathToPosix($lighttpdTmpConfFile): $lighttpdTmpConfFile;

        system($command);
        //proc_open($command, array(STDIN, STDOUT, STDERR), $pipes);
    }

    protected function resolveBin($name)
    {
        $paths = array();
        $names = array();

        if (array_key_exists('PATH', $_SERVER)) {
            $paths = explode(PATH_SEPARATOR, $_SERVER['PATH']);
        }

        switch ($name) {
            case 'lighttpd':
                $paths[] = '/usr/local/sbin/lighttpd'; //for osx installed with mac ports
                $paths[] = '/usr/bin/lighttpd';  //for linux
                $paths[] = '/usr/sbin/lighttpd'; //for linux
                $names = array('lighttpd', 'lighttpd.exe', 'LightTPD.exe');
                break;
            case 'php-cgi':
                $paths[] = '/usr/bin/php5-cgi';  // for linux
                $paths[] = '/opt/local/bin/php-cgi'; // for osx installed with mac ports
                $names = array('php5-cgi', 'php-cgi', 'php5-cgi.exe', 'php-cgi.exe');
                break;
        }

        $paths = array_reverse($paths);

        foreach ($paths as $path) {
            foreach ($names as $name) {
                $binFile = $path . DIRECTORY_SEPARATOR . $name;
                if (file_exists($binFile)) {
                    return $binFile;
                }
            }
        }

        return '';
    }

    protected function loadTemplate($tplFile, $vars)
    {
        if (! is_file($tplFile)) {
            throw new \Exception("The file $tplFile doesn't exist!");
        }

        $patterns     = array();
        $replacements = array();
        $content      = file_get_contents($tplFile);

        foreach ($vars as $varName => $varValue) {
            array_push($patterns, '/{' . $varName . '}/');
            array_push($replacements, $varValue);
        }

        return preg_replace($patterns, $replacements, $content);
    }

    protected static function convertPathToPosix($path)
    {
        $r = '/cygdrive/' . preg_replace(array('/(?):/', '/\\\/', '/\s/'), array('${1}', '/', '\ '), $path);
        $r = str_replace('/cygdrive/C', '/cygdrive/c', $r);
        $r = str_replace('/cygdrive/D', '/cygdrive/d', $r);

        return $r;
    }

    protected static function getOs()
    {
        switch (PHP_OS) {
            case 'Darwin':
                $os = 'OSX/' . PHP_OS;
                break;
            default:
                $os = PHP_OS;
        }

        return $os;
    }
}

