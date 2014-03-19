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
        //echo "<pre>"; print_r($data); die;

        if (!is_array($data)) {
            throw new \Exception("Invalid UI definition");
        }
        $keys = array_keys($data);

        if (count($keys) !== 1) {
            throw new \Exception("Invalid UI definition, zero or more at one ui component was defined.");
        }

        $this->attributes['type'] = $keys[0];
        $data = $data[$keys[0]];
        //var_dump($data); //die;

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $this->attributes = array_merge($this->attributes, $data['attributes']);
        } else {
            foreach ($data as $key => $value) {
                if (! is_numeric($key) && is_string($key)) {
                    $this->attributes[$key] = $value;
                }

                if (is_array($this->attributes[$key])
                    && !empty($this->attributes[$key][0]) && is_array($this->attributes[$key][0])
                ) {
                    $skeys = array_keys($this->attributes[$key][0]);
                    if (! empty($skeys)) {
                        if (is_array($this->attributes[$key][0][$skeys[0]])) {
                            foreach ($this->attributes[$key] as $i => $sitem) {
                                if (! empty($sitem)) {
                                    $sxtype = array_keys($sitem);
                                    $this->attributes[$key][$i] = $sitem[$sxtype[0]];
                                    $this->attributes[$key][$i]["xtype"] = $sxtype[0];
                                }
                            }
                        }
                    }
                }
            }
        }
        //echo "<pre>";print_r($this->attributes);die('..');

        //var_dump($this->attributes); die('---');
        $elementClass = 'Alchemy\Component\UI\Element\\' . ucfirst($keys[0]);
        //var_dump($elementClass); die;
        if (! class_exists($elementClass)) {
            throw new \RuntimeException(
                sprintf("Runtime Error: Undefined UI Element Class '%s'.", ucfirst($elementClass)
            ));
        }

        // getting items
//        $items = array();
//        if (isset($this->attributes['items']) && is_array($this->attributes['items'])) {
//            $items = $this->attributes['items'];
//        }

        //var_dump($this->attributes); die;
        $this->element = new $elementClass($this->attributes);

        //var_dump($items); die;

        /*foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            list($type) = array_keys($item);
            $elementType = $this->element->getXtype();
            var_dump($elementType); die;
            switch ($elementType) {
                case "form":
                    $widgetClass = 'Alchemy\Component\UI\Element\\'.ucfirst($elementType).'\Widget\\' . ucfirst($type);
                    break;
            }

            var_dump($widgetClass); die;

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
        }*/

        //pr($this->element); die;
    }
}

