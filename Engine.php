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
        $bundleDir = __DIR__ . DIRECTORY_SEPARATOR . 'bundle' . DIRECTORY_SEPARATOR . $bundle . DIRECTORY_SEPARATOR;

        if (!is_dir($bundleDir)) {
            throw new \Exception(sprintf("Error: Bundle '%s' does not exist!.", $bundle));
        }

        $genscriptFilename = $bundleDir . '/components.genscript';
        $mappingFilename   = $bundleDir . '/mapping.xml';

        //verify if the bundle is registered
        if (!file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: genscript file for '%s' bundle is missing.", $bundle));
        }

        if (!file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: mapping file for '%s' bundle is missing.", $bundle));
        }

        // load the web ui (xml file)
        $this->reader = ReaderFactory::loadReader($targetFile);

        print_r($this->reader->getWidgets());
    }

    public static function setSchema($schema)
    {
        self::$schema = $schema;
    }

    public function buildWidget(WidgetInterface $widget)
    {

    }

    public static function setCacheDir($path)
    {
        self::$cacheDir = $path;
    }
}