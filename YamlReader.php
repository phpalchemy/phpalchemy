<?php
namespace Alchemy\Component\UI;

use \Alchemy\Component\Yaml\Yaml;

class YamlReader extends ReaderInterface
{
    public $widgets;
    public $attributes;
    public $filepath;

    public function __construct($filepath)
    {
        if (!is_file($filepath)) {
            throw new \Exception("yaml file '$filepath' doesn't exist.");
        }

        $this->widgets  = new WidgetCollection();
        $this->filepath = $filepath;
        $this->attributes  = Array();

        $this->parse();
        //var_dump($this->attributes); die;
    }

    public function parse()
    {
        $data = (array) Yaml::load($this->filepath);

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

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            list($type) = array_keys($item);

            $widget = new WidgetBase();
            $widget->type = $type;
            $widget->attributes = $item[$type];

            $this->widgets->add($widget);
        }
    }
}

