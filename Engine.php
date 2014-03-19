<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Element\WidgetInterface;
use Alchemy\Component\UI\Element\Element;
use Alchemy\Component\UI\ReaderFactory;
use Alchemy\Component\UI\Parser;

/**
 * Class Parser
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
class Engine
{
    protected static $cacheDir = './';
    protected $targetBundle = '';
    protected $metaFile = '';
    protected $mapping  = array();
    protected $readerFactory = null;
    /**
     * @var \Alchemy\Component\UI\Reader
     */
    protected $reader = null;
    protected $parser = null;

    protected $generated = array();
    protected $build = array();
    protected $bundleExtensionDir = "";

    /**
     * UI\Engine Constructor
     *
     * @param \Alchemy\Component\UI\ReaderFactory $readerFactory
     * @param \Alchemy\Component\UI\Parser $parser metafile source parser
     */
    public function __construct(ReaderFactory $readerFactory, Parser $parser)
    {
        $this->readerFactory = $readerFactory;
        $this->parser = $parser;

        defined("DS") || define("DS", DIRECTORY_SEPARATOR);
    }

    /**
     * Prepare all engine dependencies
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return void
     */
    public function prepare()
    {
        if (empty($this->targetBundle) && empty($this->bundleExtensionDir)) {
            throw new \RuntimeException(sprintf(
                "Runtime Error: Any Bundle was selected for ui generation."
            ));
        }

        $bundleDir = empty($this->targetBundle) ? $this->bundleExtensionDir.DS : $this->bundleExtensionDir.DS.$this->targetBundle.DS;

        if (! file_exists($bundleDir . 'components.genscript') && ! file_exists($bundleDir . 'mapping.php')) {
            $bundleDir = __DIR__ . DS . 'bundle' . DS . $this->targetBundle . DS;
            if (! file_exists($bundleDir . 'components.genscript') && ! file_exists($bundleDir . 'mapping.php')) {
                throw new \Exception(sprintf("Error: Bundle '%s' does not exist!.", $this->targetBundle));
            }
        }

        $genscriptFilename = $bundleDir . 'components.genscript';
        $mappingFilename   = $bundleDir . 'mapping.php';

        //verify if the bundle is registered
        if (! file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: genscript file for '%s' bundle is missing.", $this->targetBundle));
        }

        if (! file_exists($genscriptFilename)) {
            throw new \Exception(sprintf("Error: mapping file for '%s' bundle is missing.", $this->targetBundle));
        }

        if (! file_exists($this->metaFile)) {
            throw new \Exception(sprintf("Error: meta file '%s' does not exist.", $this->metaFile));
        }

        $this->parser->setScriptFile($genscriptFilename);
        $this->parser->parse();
        $this->mapping = include($mappingFilename);

        // load reader from factory
        $this->reader = $this->readerFactory->load($this->metaFile);
    }

    /**
     * Set Bundle Extension directory, usually used to set user's bundles
     *
     * @param $dir
     */
    public function setBundleExtensionDir($dir)
    {
        $this->bundleExtensionDir = rtrim($dir, DS);
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

    public function setTargetBundle($bundleName)
    {
        $this->targetBundle = $bundleName;
    }

    public function setMetaFile($metaFile)
    {
        $this->metaFile = $metaFile;
    }

    /**
     * Stores a array containing the generated output element and widgets
     *
     * @return array generated output
     */
    public function getGenerated()
    {
        return $this->generated;
    }

    /**
     * Build Web UI
     *
     * @param array $data
     * @param string $targetBundle
     * @param string $metaFile
     * @return \Alchemy\Component\UI\Element\Element
     */
    public function build(array $data = array(), $targetBundle = '', $metaFile = '')
    {
        if (! empty($targetBundle)) {
            $this->setTargetBundle($targetBundle);
        }

        if (! empty($metaFile)) {
            $this->setMetaFile($metaFile);
        }

        $this->prepare();

        $widgestWithoutIdCounter = 0;
        $elementItems = array();

        /** @var \Alchemy\Component\UI\Element\Form $element */
        $element = $this->reader->getElement();

        foreach ($element->getWidgets() as $widget) {
            if ($widget->getId() === '') {
                if ($widget->name === '') {
                    $widget->setId('x-gen-' . ++$widgestWithoutIdCounter);
                    $widget->setAttribute("name", $widget->getId());
                } else {
                    $widget->setId($widget->name);
                }
            } else {
                if ($widget->name === '') {
                    $widget->setAttribute("name", $widget->getId());
                }
            }

            // setting widget data
            if (array_key_exists($widget->name, $data)) {
                $widget->setValue($data[$widget->name]);
            }

            // mapping element attributes
            $info = $this->mapElementInformation($widget);

            // generate the code
            $generated = $this->parser->generate($info['xtype'], $info);

            // setting generate code on widget property
            $widget->setGenerated($generated);

            $elementItems[$widget->getId()] = array(
                "source" => array("html" => "", "js" => ""),
                "label" => $widget->getFieldLabel()
            );

            foreach ($generated as $type => $src) {
                $elementItems[$widget->getId()]["source"][$type] = $src;
            }

            // fallback
            if (! isset($elementItems[$widget->getId()]["source"]) || ! isset($elementItems[$widget->getId()]["source"]["html"])) {
                $elementItems[$widget->getId()]["source"]["html"] = "";
            }

            $this->generated['widgets'][$widget->getId()] = array(
                'object' => $widget,
                'info'   => $info,
                'generated' => $generated
            );
        }

        $info = $this->mapElementInformation($element);
        $info['items'] = $elementItems;

        $generated = $this->parser->generate(
            $element->getXtype(),
            $info
        );

        $this->generated['element'] = $generated;
        $element->setGenerated($generated);

        return $element;
    }

    /**
     * Map the widget attributes & properties to a determined UI language
     *
     * @param \Alchemy\Component\UI\Element\Element $widget widget object to map its attributes & properties
     * @return array mapped widget information
     */
    protected function mapElementInformation(Element $widget)
    {
        $mapping = $this->mapping['widget_mapping'];
        $widgetInfo = $widget->getInfo();

        if (! array_key_exists($widget->getXtype(), $mapping)) {
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

            // if the name of attribute has overrides
            if (array_key_exists('name', $attribInfo)) {
                // the attribute name should be overridden and the original name should be removed
                unset($widgetInfo[$attributeName]); // removing original attribute name

                // getting the override attribute name
                $attributeName = $this->processMappedWidgetInfo(
                    $widget,
                    $attribInfo['name'],
                    $value
                );
            }

            // if the value of attribute has overrides
            if (array_key_exists('value', $attribInfo)) {
                // the attribute value should be overridden by a composed structure
                $widgetInfo[$attributeName] = $this->processMappedWidgetInfo(
                    $widget,
                    $attribInfo['value'],
                    $value
                );
            }

            if ($isAttribute) {
                $widgetInfo['attributes'][$attributeName] = $widgetInfo[$attributeName];
            }/* else {
                $widgetInfo[$attributeName] = $widgetInfo[$attributeName];
            }*/

        }

        return $widgetInfo;
    }

    /**
     * Process the mapped widget information
     *
     * This method match a single mapped property or attribute of a widget
     * it can match the property or attribute value and even attribute name itself
     *
     * @param  \Alchemy\Component\UI\Element\WidgetInterface $widget widget object
     * @param  mixed $info can contains a array|string|Closure
     * @param  string $value the property or attribute value
     * @throws \RuntimeException
     * @return string returns a mapped varname or value
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

    /**
     * Internal function that make a boolean to string
     *
     * @param $val
     * @return string
     */
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
}

