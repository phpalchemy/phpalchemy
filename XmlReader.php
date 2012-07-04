<?php
namespace Alchemy\UI;

class XmlReader extends Reader
{
    protected $dom;
    protected $root;
    protected $xmlns;
    protected $xmlnsPrefix;

    public $widgets;
    public $attributes;
    public $filepath;

    public function __construct($filepath)
    {
        $this->dom      = new \DOMDocument();
        $this->widgets  = new WidgetCollection();
        $this->filepath = $filepath;
        $this->attributes  = Array();
        
        $this->xmlnsPrefix = 'ui';
        
        $this->dom->load($this->filepath);
        $this->root  = $this->dom->firstChild;
        $this->xmlns = $this->dom->lookupnamespaceURI($this->xmlnsPrefix);
        
        $this->parse();
    }

    public function parse()
    {
        // getting element attributes
        foreach ($this->root->attributes as $attribute) {
            // filtering by xmlns
            if (preg_match('/' . $this->xmlnsPrefix . ':(.+)/', $attribute->nodeName, $match)) {
                $this->attributes[$match[1]] = $attribute->nodeValue;
            } 
            else {
                $this->attributes[$attribute->nodeName] = $attribute->nodeValue;
            }
        }
        $this->attributes['type'] = $this->root->nodeName;

        // getting child nodes
        foreach ($this->root->childNodes as $childNode) {
            //if ($childNode->nodeType == XML_ELEMENT_NODE) {
            if (get_class($childNode) == 'DOMElement') {
                $widget = new WidgetBase();
                $widget->type = $childNode->nodeName;

                foreach ($childNode->attributes as $attribute) {
                    // filtering by xmlns
                    if (preg_match('/' . $this->xmlnsPrefix . ':(.+)/', $attribute->nodeName, $match)) {
                        $widget->attributes[$match[1]] = $attribute->nodeValue;
                    } 
                    else {
                        $widget->attributes[$attribute->nodeName] = $attribute->nodeValue;
                    }
                }

                //getting inner attributes
                $attributes = array();
                $attributesNodeList = $childNode->getElementsByTagName('attribute');

                for($i = 0; $i < $attributesNodeList->length; $i++) {
                    $attributeNode  = $attributesNodeList->item($i);
                    $attributeName  = $attributeNode->getAttribute('name');
                    $attributeValue = $attributeNode->getAttribute('value');

                    $attributeInnerValue = trim((string) simplexml_import_dom($attributeNode));

                    if ($attributeInnerValue != '') {
                        $attributeValue = $attributeInnerValue;
                    }
                    
                    if ($attributeName != '' && $attributeValue != '') {
                        if (preg_match('/' . $this->xmlnsPrefix . ':(.+)/', $attribute->nodeName, $match)) {
                            $attributes[$match[1]] = $attributeValue;
                        }
                        else {
                            $attributes[$attributeName] = $attributeValue;
                        }
                    }
                }
                
                $widget->attributes = array_merge($widget->attributes, $attributes);
                $attributeInnerValue = trim((string) simplexml_import_dom($childNode));

                if ($attributeInnerValue != '') {
                    $widget->attributes['value'] = $attributeInnerValue;
                }

                // at the end create the type attribute, override if was defined by user
                ////////erik $widget->attributes['type'] = $childNode->nodeName;
                
                $this->widgets->add($widget);
            }
        }
    }
}
