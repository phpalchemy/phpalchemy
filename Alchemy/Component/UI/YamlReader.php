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
                $key = self::toCamelCase($key);
                if (! is_numeric($key) && is_string($key)) {
                    $this->attributes[$key] = $value;
                }

                if (is_array($this->attributes[$key])
                    && !empty($this->attributes[$key][0]) && is_array($this->attributes[$key][0])
                ) {
                    $skeys = array_keys($this->attributes[$key][0]);
                    if (! empty($skeys) && is_array($this->attributes[$key][0][$skeys[0]])) {
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

        $elementClass = 'Alchemy\Component\UI\Element\\' . ucfirst($keys[0]);

        if (! class_exists($elementClass)) {
            throw new \RuntimeException(
                sprintf("Runtime Error: Undefined UI Element Class '%s'.", ucfirst($elementClass)
            ));
        }

        $this->element = new $elementClass($this->attributes);
    }
}

