<?php
namespace Alchemy\Component\UI;

use \Alchemy\Component\Yaml\Yaml;

class YamlReader extends Reader
{
    public $widgets;
    public $attributes;
    public $filepath;

    public function __construct($filepath)
    {
        if (!is_file($filepath)) {
            throw new \Exception("yaml file '$filepath' doesn't exist.");
        }

        $this->widgets  = array();
        $this->filepath = $filepath;
        $this->attributes  = Array();

        $this->parse();
        //var_dump($this->attributes); die;
    }

    public function parse()
    {
        $yaml = new Yaml();
        $data = (array) $yaml->load($this->filepath);

        if (!is_array($data)) {
            throw new \Exception("Invalid UI definition");
        }
        $keys = array_keys($data);

        if (count($keys) !== 1) {
            throw new \Exception("Invalid UI definition, zero or more at one ui component was defined.");
        }

        $this->attributes['type'] = $keys[0];
        $data = $data[$keys[0]];

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $this->attributes = array_merge($this->attributes, $data['attributes']);
            unset($data['attributes']);
        } else {
            foreach ($data as $key => $value) {
                if (!is_numeric($key) && is_string($key) && !is_array($value)) {
                    $this->attributes[$key] = $value;
                    unset($data[$key]);
                }
            }
        }

        // getting items
        if (isset($data['items']) && is_array($data['items'])) {
            $data = $data['items'];
        }

        $elementClass = 'Alchemy\Component\UI\Element\\' . ucfirst($keys[0]);

        if (! class_exists($elementClass)) {
            throw new \RuntimeException(
                sprintf("Runtime Error: Undefined UI Element Class '%s'.", ucfirst($this->root->nodeName)
            ));
        }

        $this->element = new $elementClass($this->attributes);

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            list($type) = array_keys($item);

            $widgetClass = 'Alchemy\Component\UI\Widget\\' . ucfirst($type);

            if (! class_exists($widgetClass)) {
                throw new \RuntimeException(
                    sprintf("Runtime Error: Undefined UI Widget Class '%s'.", ucfirst($type)
                ));
            }

            $widget = new $widgetClass();
            $widget->setXtype($type);

            foreach ($item[$type] as $name => $value) {
                $widget->setAttribute($name, $value);
            }

            $this->element->add($widget);
        }
    }
}

