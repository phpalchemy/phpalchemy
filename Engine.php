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
    protected $schema   = 'html';
    protected $cacheDir = './';
    protected $bundle   = '';
    protected $mapping  = array();

    protected $widgetsCollection = array();

    public function __construct($bundle, Reader $reader, Parser $parser)
    {
        $this->bundle = $bundle;
        $bundleDir    = __DIR__ . DIRECTORY_SEPARATOR . 'bundle' . DIRECTORY_SEPARATOR . $bundle . DIRECTORY_SEPARATOR;

        if (!is_dir($bundleDir)) {
            throw new \Exception(sprintf("Error: Bundle '%s' does not exist!.", $bundle));
        }

        $genscriptFilename = $bundleDir . DIRECTORY_SEPARATOR . 'components.genscript';
        $mappingFilename   = $bundleDir . DIRECTORY_SEPARATOR . 'mapping.php';

        //verify if the bundle is registered
        if (!file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: genscript file for '%s' bundle is missing.", $bundle));
        }

        if (!file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: mapping file for '%s' bundle is missing.", $bundle));
        }

        $this->reader = $reader;
        $this->parser = $parser;
        $this->parser->setScriptFile($genscriptFilename);
        $this->parser->parse();

        $this->mapping = include($mappingFilename);
    }

    public static function setCacheDir($path)
    {
        self::$cacheDir = $path;
    }

    public function getWidgetsCollection()
    {
        return $this->widgetsCollection;
    }

    public function build()
    {
        $widgestWithoutIdCounter = 0;

        foreach ($this->reader->getWidgets() as $widget) {
            if (trim($widget->getId()) === '') {
                $widget->setId('x-gen-' . ++$widgestWithoutIdCounter);
            }

            $data = $this->mapWidget($widget);
            $xtype = strtolower($this->getClass($widget));

            $this->widgetsCollection[$widget->getId()] = array(
                'object' => $widget,
                'data'   => $data,
                'output' => $this->parser->generate(
                    $data['xtype'],
                    $data
                )
            );
        }
    }

    protected function mapWidget(WidgetInterface $widget)
    {
        $data = $widget->getData();

        foreach ($data as $key => $value) {
            if (!array_key_exists($widget->getXtype(), $this->mapping)) {
                continue;
            }

            $overrides = $this->mapping[$widget->getXtype()];

            if (array_key_exists('_callback', $overrides)) {
                $callback = $overrides['_callback'];
                unset($overrides['_callback']);

                $overridden = $callback($widget);
                $overrides = array_merge($overrides, $overridden);
            }

            foreach ($overrides as $key => $value) {
                if (array_key_exists($key, $data)) {
                    // find on properties
                    $data[$key] = $value;
                } elseif (array_key_exists($key, $data['attributes'])) {
                    // find on attributes
                    $data['attributes'][$key] = $value;
                }
            }
        }

        return $data;
    }

    protected function getClass($obj, $trimWorkspace = true)
    {
        $class = get_class($obj);

        if ($trimWorkspace) {
            $class = substr($class, strpos(strrev($class), '\\') * -1);
        }

        return $class;
    }
}

