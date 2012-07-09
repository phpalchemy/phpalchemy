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

    /**
     * UI\Engine Constructor
     *
     * @param string $bundle bundle name to generate a its output based in its generation script and mapping rules
     * @param Reader $reader meta file source reader
     * @param Parser $parser metafile source parser
     */
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

    /**
     * Sets cache directory for UI Engine
     *
     * @param string $path to store cache dir path
     */
    public static function setCacheDir($path)
    {
        self::$cacheDir = $path;
    }

    /**
     * Stores a array containing all widgets collection
     *
     * @return array widgets collection
     */
    public function getWidgetsCollection()
    {
        return $this->widgetsCollection;
    }

    /**
     * Build Web UI
     */
    public function build()
    {
        $widgestWithoutIdCounter = 0;

        foreach ($this->reader->getWidgets() as $widget) {
            if ($widget->getId() === '') {
                $widget->setId('x-gen-' . ++$widgestWithoutIdCounter);
            }

            $data = $this->mapWidget($widget);

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

    /**
     * Map the widget attributes & properties to a determinated UI languaje
     * @param  WidgetInterface $widget widget object to map its attributes & properties
     * @return array                   mapped widget information
     */
    protected function mapWidget(WidgetInterface $widget)
    {
        $mapping = $this->mapping['widget_mapping'];
        $widgetInfo = $widget->getInfo();

        if (!array_key_exists($widget->getXtype(), $mapping)) {
            return $widgetInfo;
        }

        $overrides = $mapping[$widget->getXtype()];

        foreach ($overrides as $attributeName => $attribInfo) {
            $isAttribute = false;

            if (array_key_exists($attributeName, $widgetInfo)) {
                // find on properties
                $value = $widgetInfo[$attributeName];
            } elseif (array_key_exists($attributeName, $widgetInfo['attributes'])) {
                // find on attributes
                $value = $widgetInfo['attributes'][$attributeName];
                $isAttribute = true;
            } else {
                // attribute or property doesn't exist on widget class
                // throw new \RuntimeException(sprintf(
                //     "Runtime Error: Attribute '%s' doesn't exist on %s widget class.",
                //     $attributeName,
                //     ucfirst($widget->getXtype())
                // ));

                continue;
            }

            // if the attribute's name has overrides
            if (array_key_exists('name', $attribInfo)) {
                // the attribute name should be overridden and the original name should be removed

                // removing original attribute name
                unset($widgetInfo[$attributeName]);

                // geeting the override attribute name
                $attributeName = $this->processMappedWidgetInfo(
                    $widget,
                    $attribInfo['name'],
                    $value
                );
            }

            // if the attribute's value has overrides
            if (array_key_exists('value', $attribInfo)) {
                // the attribute value shoul be overridden by a composed structure
                $widgetInfo[$attributeName] = $this->processMappedWidgetInfo(
                    $widget,
                    $attribInfo['value'],
                    $value
                );
            }

            if ($isAttribute) {
                $widgetInfo['attributes'][$attributeName] = $widgetInfo[$attributeName];
            } else {
                $widgetInfo[$attributeName] = $widgetInfo[$attributeName];
            }

        }

        return $widgetInfo;
    }

    /**
     * Process the mapped widget information
     *
     * This method match a single mapped property or attribute of a widget
     * it can match the property or attribute value and even attribute name itself
     *
     * @param  WidgetInterface $widget widget object
     * @param  mixed           $info   can contains a array|string|Closure
     * @param  string          $value  the property or attribute value
     * @return string                  returns a mapped varname or value
     */
    private function processMappedWidgetInfo(WidgetInterface $widget, $info, $value)
    {
        if (is_string($info)) {
            $result = $info;
        } elseif (is_array($info)) {
            $strValue = $this->toString($value);

            if (array_key_exists($strValue, $info)) {
                $result = $info[$strValue];
            } else {
                $result = $value;
            }
        } elseif ($info instanceof \Closure){
            $result = $info($widget);
        } else {
            throw new \RuntimeException(sprintf(
                "Runtime Error: invalid data type '%s' for widget attribute", gettype($value)
            ));
        }

        return $result;
    }

    private function toString($val)
    {
        if ($val === true) {
            return 'true';
        } elseif ($val === false) {
            return 'false';
        } else {
            return (string) $val;
        }
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

