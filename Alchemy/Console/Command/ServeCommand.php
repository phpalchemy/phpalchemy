<?php
namespace Alchemy\Console\Command;

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
class ServeCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('serve')
        ->setDescription('Serve project over http')
        ->setDefinition(array(
            /*new InputArgument(
                'port', InputArgument::OPTIONAL,
                'a alternative port.'
            ),*/
            new InputArgument(
                'environment', InputArgument::OPTIONAL,
                'Environment to startup', 'development'
            ),
            new InputOption(
                'host', null, InputOption::VALUE_OPTIONAL,
                'Set the server address', 'localhost'
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
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $lighttpdCmd = false;
        $cgiBinPath  = false;
        $socketPath  = false;

        $soTmpDir = \Alchemy\Common\Alchemist::basepath(sys_get_temp_dir());
        $hostOpt     = $input->getOption('host');
        $portOpt     = $input->getOption('port'); //$port = $input->getArgument('port');
        $env         = $input->getArgument('environment');
        $binaries    = self::resolveBinaries();
        //var_dump($port); die;
        // Setting defaults.
        $env           = $env != '' ? $env : 'development';
        $lighttpd_bin  = $binaries['lighttpd_bin'] ? $binaries['lighttpd_bin'] : null;
        $host = 'localhost';
        $port = '3000';
        $phpcgi_bin    = $binaries['phpcgi_bin'] ? $binaries['phpcgi_bin'] : null;

        //if (PHP_OS == 'Darwin' || PHP_OS == 'Linux') {  //macos & linux $tmpPath = '/tmp/';}

        // Getting environment configuration.
        LazyLoad::object('Bootstrap')->setEnvironment($env);
        LazyLoad::object('Bootstrap')->loadingConfiguration();

        // Getting configuration.
        $envConfig     = Registry::get('env.config');
        $projectConfig = Registry::get('project.config');

        // Setting configurations.
        $tmpDir = $envConfig['app']['main']['tmp_dir'];
        $lighttpdTmpConfFile = $tmpDir . '.lighttpd.conf';

        // overwrite default conf
        if (isset($envConfig['server']['lighttpd']['daemon_bin'])) {
            $lighttpd_bin = $envConfig['server']['lighttpd']['bin'];
        }

        if (isset($envConfig['server']['main']['host'])) {
            $host = $envConfig['server']['main']['host'];
        }

        if (isset($envConfig['server']['main']['port'])) {
            $port = $envConfig['server']['main']['port'];
        }
        //end overwrite default conf

        // overwrite previous conf with teh args passed
        if (!empty($hostOpt)) {
            $host = $hostOpt;
        }

        if (!empty($portOpt)) {
            $port = $portOpt;
        }


        // if (PHP_OS == 'WINNT') {
        //     $iniConfig['phpcgi_bin'] = self::convertPathToPosix($iniConfig['phpcgi_bin']);
        //     //$iniConfig['lighttpd_bin'] = $iniConfig['lighttpd_bin'];
        //     $iniConfig['tmp_path'] = self::convertPathToPosix($tmpPath);
        // }

        // Making validations.
        if (empty($lighttpd_bin)) {
            throw new \Exception("Seems Lighttpd is not installed!");
        }

        if (empty($phpcgi_bin)) {
            throw new \Exception("php-cgi binary not found!");
        }

        //setting lighttpd configuration variables
        $config['doc_root']    = PHP_OS == 'WINNT' ? self::convertPathToPosix(SYS_DIR) : SYS_DIR;
        $config['host']        = $host;
        $config['port']        = $port;
        $config['tmp_dir']     = $tmpDir;
        $config['bin_path']    = $phpcgi_bin;
        $config['socket_path'] = $soTmpDir . "php.socket";
        $config['environment'] = $env;

        // load the lighttpd.conf template with the configurations
        $lighttpdConf = Alchemist::loadTemplate(ALCHEMY_TEMPLATES_DIR . 'lighttpd.conf.tpl', $config);

        if (file_put_contents($lighttpdTmpConfFile, $lighttpdConf) === false) {
            throw new Exception ("Error while creating the lighttpd configuration file!");
        }

        $output->writeln(PHP_EOL . '--= PhpAlchemy Framework ver. 0.7 ('.PHP_OS.')=--'.PHP_EOL);
        $output->writeln('<comment>Using "'.$env.'" environment.</comment>');
        $output->writeln(PHP_EOL . sprintf('* The Project "<info>%s</info>" is running on port: <info>%s</info>', SYS_NAME, $port));
        $output->writeln("* URL: <info>http://$host:$port</info>");

        $lighttpdTmpConfFile = PHP_OS == 'WINNT' ? self::convertPathToPosix($lighttpdTmpConfFile): $lighttpdTmpConfFile;

        $command = "$lighttpd_bin -f $lighttpdTmpConfFile -D";

        system($command);
    }

    static function resolveBinaries()
    {
        //setting defaults
        $phpcgiBin   = false;
        $lighttpdBin = false;

        $cgiBinPathList   = array();
        $lighttpdPathList = array(
            '/usr/sbin/lighttpd', //for linux
            '/usr/bin/lighttpd',
            '/usr/local/sbin/lighttpd'  //macos
        );
        $cgiBinPathList = array(
            '/usr/bin/php5-cgi',  //linux
            '/opt/local/bin/php-cgi' //macos
        );

        if (isset($_SERVER['PATH'])) {
            $cgiBinRelativePathList = explode(PATH_SEPARATOR, $_SERVER['PATH']);

            //TODO we need to improve this
            foreach ($cgiBinRelativePathList as $envpath) {
                $cgiBinPathList[] = $envpath . DS . 'php5-cgi';
                $cgiBinPathList[] = $envpath . DS . 'php-cgi';
                $cgiBinPathList[] = $envpath . DS . 'php5-cgi.exe';
                $cgiBinPathList[] = $envpath . DS . 'php-cgi.exe';
                $lighttpdPathList[] = $envpath . DS . 'lighttpd';
                $lighttpdPathList[] = $envpath . DS . 'lighttpd.exe';
                $lighttpdPathList[] = $envpath . DS . 'LightTPD.exe'; //alternative non standard
            }
        }

        foreach ($lighttpdPathList as $filepath) {
            if (is_file($filepath)) {
                $lighttpdBin = $filepath;
                break;
            }
        }

        foreach ($cgiBinPathList as $cgiBin) {
            if (is_file($cgiBin)) {
                $phpcgiBin = $cgiBin;
                break;
            }
        }

        return array(
            'lighttpd_bin' => $lighttpdBin,
            'phpcgi_bin'   => $phpcgiBin
        );
    }

    static function convertPathToPosix($path)
    {
        $r = '/cygdrive/' . preg_replace(array('/(?):/', '/\\\/', '/\s/'), array('${1}', '/', '\ '), $path);
        $r = str_replace('/cygdrive/C', '/cygdrive/c', $r);
        $r = str_replace('/cygdrive/D', '/cygdrive/d', $r);
        return $r;
    }
}
