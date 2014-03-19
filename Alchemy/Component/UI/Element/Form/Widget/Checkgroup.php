<?php
namespace Alchemy\Component\UI\Element\Form\Widget;

/**
 * Class Textbox
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
class Checkgroup extends Widget
{
    public $disabled;
    public $editable;
    public $readonly;

    protected $label = '';
    protected $checked = false;
    protected $items = array();
    protected $inline = false;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->setXtype('checkgroup');
    }

    public function setChecked($value)
    {
        $this->checked = $value;
    }

    public function getChecked()
    {
        return $this->checked;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
    	return $this->label;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param boolean $inline
     */
    public function setInline($inline)
    {
        $this->inline = $inline;
    }

    /**
     * @return boolean
     */
    public function getInline()
    {
        return $this->inline;
    }

    public function setAttribute($name, $value = "")
    {
        parent::setAttribute($name, $value);

        if ($name == "items" && ! empty($this->name)) {
            foreach ($this->items as $i => $item) {
                $this->items[$i]["name"] = $this->name . "[]";
            }
        } elseif ($name == "name") {
            foreach ($this->items as $i => $item) {
                $this->items[$i]["name"] = $this->name . "[]";
            }
        }
    }
}

