<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Widget\WidgetInterface;
use Alchemy\Component\UI\ReaderFactory;

/**
 * Class Parser
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
class Engine
{
    protected $schema = 'html';
    protected $cacheDir = './';
    protected $engine = '';

    public function __construct($bundle, $targetFile)
    {
        $this->bundle  = $bundle;

        if (!is_dir('bundle/' . $bundle))
            throw new \Exception(sprintf("Error: Bundle '%s' does not exist!.", $bundle));
        }

        $genscriptFilename = 'bundle/' . $bundle . '/components.genscript';
        $mappingFilename    = 'bundle/' . $bundle . '/mapping.xml';

        //verify if the bundle is registered
        if (!file_exists($schemaPath)) {
            throw new \Exception("Framework Bundle '".$this->frameworkBundle."' is not registered.");
        }

        if (!file_exists($genscriptPath)) {
            throw new \Exception("Genscript for Framework Bundle '".$this->frameworkBundle."' is not present.");
        }

        // load the web ui (xml file)
        $this->reader = ReaderFactory::loadReader($targetFile);
    }

    public static function setSchema($schema)
    {
        self::$schema = $schema;
    }

    public function buildWidget(WidgetInterface $widget)
    {
        //$content = file_get_contents($this->schema.'.ui');
        $tpl   = self::$schema . '.schema.djt';
        $cache = true;
        $debug = true;

        $config = array(
            'template_dir' => __DIR__ . '/schema/',
            'cache_dir' => self::$cacheDir,
            'debug' => $debug,
        );

        if ($cache && is_callable('xcache_isset')) {
            /* don't check for changes in the template for the next 5 min */
            $config['check_ttl'] = 300;
            $config['check_get'] = 'xcache_get';
            $config['check_set'] = 'xcache_set';
        }

        $data = array('element' => $widget->getProperties());

        \Haanga::configure($config);
        $output = '';
        \ob_start();
        \Haanga::Load($tpl, $data);
        $output = \ob_get_contents();
        \ob_end_clean();

        return $output;
    }

    public static function setCacheDir($path)
    {
        self::$cacheDir = $path;
    }
}