<?php
namespace Alchemy\Console;

use Alchemy\Config;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Application\Cli\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Alchemist extends Application
{
    protected $homeDir    = '';
    protected $projectDir = '';
    protected $config     = array();
    protected $app        = null;

    public function __construct(Config $config)
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\');

        $this->config     = $config;
        $this->homeDir    = $this->config->get('phpalchemy.root_dir');
        $this->projectDir = $this->config->get('app.root_dir');

        //var_dump($this->isAppDirectory()); die;

        if ($this->isAppDirectory()) {
            $this->config->load($this->homeDir . DS . 'config' . DS . 'defaults.application.ini');
            $this->config->load($this->projectDir . DS . 'application.ini');
        }

        $this->config->set('phpalchemy.root_dir', $this->homeDir);

        $title    = "\n PHPAlchemy Framework Cli. ";
        $version  = '1.0';

        parent::__construct($title, $version);
        $this->setCatchExceptions(true);
    }

    protected function prepare()
    {
        $helpers  = array();
        $commands = array();

        // adding command for a project environment
        $commandsList = glob(__DIR__ . '/Command/*Command.php');

        foreach ($commandsList as $command) {
            $commandClass = '\Alchemy\Console\Command\\' . substr(basename($command), 0, -4);

            if (stripos($commandClass, 'serve') !== false) {
                if (! $this->isAppDirectory()) {
                    continue;
                }
            }

            if (stripos($commandClass, 'initApp') !== false) {
                continue; //TODO this command will be removed soon
            }

            // adding command
            $commands[] = new $commandClass($this->config, $this);
        }

        // if (! $this->isAppDirectory()) {
        //     $commands[] = new \Alchemy\Console\Command\ServeCommand($this->config);
        // }

        $helperSet = $this->getHelperSet();

        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }

        $this->addCommands($commands);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->prepare();
        return parent::run();
    }

    public function isAppDirectory()
    {
        if (! file_exists($this->projectDir . DS . 'application.ini')) {
            return false;
        }

        $this->config->load($this->projectDir . DS . 'config' . DS . 'env.ini');

        foreach ($this->config->all() as $key => $value) {
            if (substr($key, 0, 3) === 'app' && substr($key, -4) === '_dir' && substr($key, -9) !== 'cache_dir') {
                if (! is_dir($value)) {
                    var_dump($value);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Creates a project directory skeleton
     *
     * @param  string $projectName the project name
     */
    public function createProjectSkel($projectName)
    {
        $rootDir = $this->config->get('phpalchemy.root_dir');
        $defAppIniFile = $rootDir . '/config/defaults.application.ini';

        if (! self::validateProjectName($projectName)) {
            throw new \Exception(
                "Error: Invalid project name!\nProject name can be contains only alphanumeric characteres."
            );
        }

        $newProjectDir = getcwd() . '/' . $projectName;

        if (! file_exists($defAppIniFile)) {
            throw new \Exception("FATAL ERROR: File: 'defaults.application.ini' is missing!");
        }

        $this->projectDir = $newProjectDir;
        $this->config->set('app.root_dir', $newProjectDir);

        // create main project dir
        self::createDir($newProjectDir);

        $this->config->load($this->homeDir . DS . 'config' . DS . 'defaults.application.ini');
        $this->config->load($this->projectDir . DS . 'application.ini');

        $appInitConf = $this->config->all();
        //print_r($appInitConf); die;

        // creating directories skel.
        foreach ($appInitConf as $key => $targetDir) {
            // process only directory path ini. conf.
            //if (strpos($key, '_dir') !== false) {
            if (preg_match('/^app\.[\w]+_dir/', $key, $m) && $key !== 'app.root_dir') {
                //echo $key.' -> '.$targetDir . PHP_EOL;
                self::createDir($targetDir);
            }
        }

        // defining vars
        $data = array();
        $data['appName'] = $projectName;
        $data['namespace'] = self::camelize($projectName);
        $data['framework_dir'] = $this->homeDir;

        // read project templates & others files
        $projectFiles = glob($rootDir . '/templates/project/*');

        foreach ($projectFiles as $file) {
            $targetFile = $this->projectDir.'/'.str_replace('|', DIRECTORY_SEPARATOR, basename($file));
            self::createDir(pathinfo($targetFile, PATHINFO_DIRNAME));

            if (substr($file, -4) === '.tpl') {
                $contents = file_get_contents($file);
                $targetFile = str_replace('.tpl', '', $targetFile);

                // replace data on template
                foreach ($data as $key => $value) {
                    $contents = str_replace('{'.$key.'}', $value, $contents);
                }

                // write the composed template to target file
                file_put_contents($targetFile, $contents);
            } else {
                // just we need to copy file
                copy($file, $targetFile);
            }
        }

        return $projectName;
    }

    /*** Static Functions ***/

    /**
     * Creates a directory recursively
     * @param  string  $strPath path
     * @param  integer $rights  right for new directory
     */
    public static function createDir($strPath, $rights = 0777)
    {
        $folderPath = array($strPath);
        $oldumask    = umask(0);

        while (!@is_dir(dirname(end($folderPath)))
            && dirname(end($folderPath)) != '/'
            && dirname(end($folderPath)) != '.'
            && dirname(end($folderPath)) != ''
        ) {
            array_push($folderPath, dirname(end($folderPath)));
        }

        while ($parentFolderPath = array_pop($folderPath)) {
            if (!@is_dir($parentFolderPath)) {
                if (!@mkdir($parentFolderPath, $rights)) {
                    throw new \Exception("Runtime Error: Can't create folder '$parentFolderPath'");
                }
            }
        }

        umask($oldumask);
    }

    public static function validateProjectName($name)
    {
        $validChars = array('_'); //array('-', '_');

        return ctype_alnum(str_replace($validChars, '', $name));
    }

    public static function camelize($str)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

}

