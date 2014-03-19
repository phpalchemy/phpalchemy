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
class Listbox extends Widget
{
    public $disabled;
    public $editable;
    public $multiple;

    protected $items = array();

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->setXtype('listbox');
    }

    function getAttributes() {
        $attributes = parent::getAttributes();

        if ($this->multiple) {
            $attributes["name"] .= "[]";
        }

        return $attributes;
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


}

