<?php
namespace Alchemy\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Application;

use Alchemy\Config;

/**
 * Task for admin projects
 *
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link    www.phpalchemy.org
 * @since   1.0
 * @version $Revision$
 * @author  Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class InitAppCommand extends Command
{
    protected $config = null;
    protected $app = null;

    public function __construct(Config $config, Application $app)
    {
        $this->config = $config;
        $this->app    = $app;

        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        parent::__construct();
    }

    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('init-app')
        ->setDescription('Create a new project')
        ->setDefinition(array(
            new InputArgument(
                'project-name', InputArgument::OPTIONAL,
                'Enter project name'
            )
        ))
        ->setHelp('Create a new phpalchemy project');
    }

    /**
     * Execute Method
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectName = $input->getArgument('project-name');
        echo PHP_EOL;

        $output->write(sprintf("* Creating Project '<info>%s</info>' ... ", $projectName));
        $projectName = $this->app->createProjectSkel($projectName);
        $output->writeln("Done!");

        chdir($projectName);

        $output->write("* Verifying connection ... ");
        $cnnType = self::canConnect("getcomposer.org") ? 'online' : 'offline';
        $output->writeln(sprintf("<info>(%s)</info>", $cnnType));

        $output->write("* <info>Building project ... </info>");

        // get checksum from framework cache
        $cachedMetadata = self::getCachedVendorsMetadata();

        // get checksum from composer config file
        $checksum = self::getVendorsChecksum();

        if ($cnnType === 'online' && $checksum !== $cachedMetadata->checksum) {
            $output->writeln("(from packagist)");

            self::remoteBuild();
            self::saveVendorPkgs($checksum);
        } else {
            $output->writeln("(local)");

            self::localBuild();
        }
    }

    protected static function remoteBuild()
    {
        system('curl -s http://getcomposer.org/installer | php');
        system('php composer.phar install');
    }

    protected static function localBuild()
    {
        // define framework cache dir. on user home directory
        $cacheHomeDir = getenv("HOME") . DS . '.phpalchemy' . DS . 'cache' . DS . 'composer' . DS;

        require_once 'Archive_Tar/Archive/Tar.php';

        \Alchemy\Console\Alchemist::createDir(getcwd() . '/vendor');

        $filter = new \Zend\Filter\Decompress(array(
            'adapter' => 'Tar',
            'options' => array(
                'archive' => $cacheHomeDir . 'vendor.tar',
                'target' => getcwd(),
            )
        ));

        $compressed = $filter->filter($cacheHomeDir . 'vendor.tar');

        copy($cacheHomeDir . 'composer.phar', getcwd() . '/composer.phar');
        copy($cacheHomeDir . 'composer.lock', getcwd() . '/composer.lock');
    }

    protected static function saveVendorPkgs($checksum = '')
    {
        $checksum !== '' || $checksum = self::getVendorsChecksum();

        // define framework cache dir. on user home directory
        $cacheHomeDir = getenv("HOME") . DS . '.phpalchemy' . DS . 'cache' . DS . 'composer' . DS;

        // create cache dir. if it doesn't exist.
        if (! is_dir($cacheHomeDir)) {
            \Alchemy\Console\Alchemist::createDir($cacheHomeDir);
        }

        // create zip file
        // save zip file on framework cache dir
        require_once 'Archive_Tar/Archive/Tar.php';

        $filter = new \Zend\Filter\Compress(array(
            'adapter' => 'Tar',
            'options' => array(
                'archive' => $cacheHomeDir . 'vendor.tar',
            )
        ));

        $compressed = $filter->filter('vendor/');

        if (! $compressed) {
            $output->writeln("<error>Couldn't save the cache tarball.</error>");
        }

        copy('composer.phar', $cacheHomeDir . 'composer.phar');
        copy('composer.lock', $cacheHomeDir . 'composer.lock');

        $vendors = self::getVendorsList();

        $meta = array(
            'date' => date('Y-m-d H:i:s'),
            'checksum' => $checksum,
            'packages' => $vendors->require
        );

        file_put_contents($cacheHomeDir . 'meta.json', json_encode($meta));
    }

    protected static function getCachedVendorsMetadata()
    {
        // define framework cache dir. on user home directory
        $cacheHomeDir = getenv("HOME") . DS . '.phpalchemy' . DS . 'cache' . DS . 'composer' . DS;

        if (! file_exists($cacheHomeDir . 'meta.json')) {
            return '';
        }

        $meta = json_decode(file_get_contents($cacheHomeDir . 'meta.json'));

        return $meta;
    }

    protected static function getVendorsChecksum()
    {
        // validation
        if (! file_exists('composer.json')) {
            throw new \Exception("File Not Found Error: File 'composer.json' is missing!");
        }

        // read the composer.json file to get all vendor's name and version
        $vendorsList = self::getVendorsList();
        $chksum = array();

        // with that info calculate the checksum
        foreach ($vendorsList->require as $name => $ver) {
            $chksum[] = sha1($name . '@' . $ver);
        }

        return sha1(implode(',', $chksum));
    }

    protected static function getVendorsList()
    {
        // validation
        if (! file_exists('composer.json')) {
            throw new \Exception("File Not Found Error: File 'composer.json' is missing!");
        }

        // read the composer.json file to get all vendor's name and version
        return json_decode(file_get_contents('composer.json'));
    }

    protected static function canConnect($url)
    {
        $c = curl_init($url);

        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_VERBOSE, false);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        if (curl_exec($c)) {
            $statusCode = curl_getinfo($c, CURLINFO_HTTP_CODE);

            if ($statusCode == 200 || $statusCode == 302) {
                curl_close($c); // success
                return true;
            }
        }

        curl_close($c);
        return false;
    }
}







