<?php
namespace Alchemy\UI;

class UiFrameworkBundle
{
    private $engine;
    private $dom;
    private $root;
    public  $schemaWidgets = array();

    public function __construct($engine, $filepath)
    {
        $this->engine = $engine;
        $this->dom = new \DOMDocument();
        $this->filepath = $filepath;

        $this->load();
    }

    public function load()
    {
        if (!file_exists($this->filepath)) {
            throw new \Exception('Squema for ' . $this->engine . ' does not exist.');
        }

        $xmlPersedSuccess = @$this->dom->load($this->filepath);

        if (!$xmlPersedSuccess) {
            throw new \Exception('Problems parsing ' . $this->filepath . ', the xml is malformed.');
        }

        $this->root = $this->dom->firstChild;

        foreach ($this->root->childNodes as $childNode) {
            //if ($childNode->nodeType == XML_ELEMENT_NODE) {
            if (get_class($childNode) == 'DOMElement') {
                $widget = new \StdClass();
                $widget->name = $childNode->getAttribute('name');
                //$widget->mapping = $childNode->getAttribute('mapping');

                //getting inner attributes
                $attributes = array();
                $attributesNodeList = $childNode->getElementsByTagName('attribute');

                for($i = 0; $i < $attributesNodeList->length; $i++) {
                    $attributeName  = $attributesNodeList->item($i)->getAttribute('name');
                    $attributeMapping = $attributesNodeList->item($i)->getAttribute('mapping');

                    if ($attributeMapping != '') {
                        $attributes[$attributeMapping] = $attributeName;
                    }
                }

                $widget->attributes = $attributes;
                $this->schemaWidgets[$childNode->getAttribute('mapping')] = $widget;
            }
        }
    }

    public function getWidgets()
    {
        return $this->widgets;
    }

    public function mapWidgets(\Alchemy\UI\WidgetCollection $widgetCollection)
    {
        $widgetsBundle = array();

        do {
            $widget = $widgetCollection->get();
            $widgetBundle = new \Alchemy\UI\WidgetBase();

            if (!isset($this->schemaWidgets[$widget->type])) {
                continue;
            }

            $widgetBundle->type = $this->schemaWidgets[$widget->type]->name;

            $widget->attributes['type'] = $widgetBundle->type;

            foreach ($widget->attributes as $key => $value) {
                if (isset($this->schemaWidgets[$widget->type]->attributes[$key])) {
                    $widgetBundle->attributes[$this->schemaWidgets[$widget->type]->attributes[$key]] = $value;
                } else {
                    $widgetBundle->attributes[$key] = $value;
                }
            }

            $widgetsBundle[] = $widgetBundle;

        } while ($widgetCollection->next());
        
        return $widgetsBundle;
    }
}