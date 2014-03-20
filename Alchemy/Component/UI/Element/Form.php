<?php
namespace Alchemy\Component\UI\Element;

use Alchemy\Component\UI\Element\WidgetInterface;

/**
 * Class Form
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
class Form extends Element
{
    public $width = '250px';
    public $action = '';
    public $method = 'post';
    public $title = '';

    protected $buttons = array();
    protected $xtype = 'form';

    /**
     * @var \Alchemy\Component\UI\Element\Form\Widget\Widget[]
     */
    protected $items = array();
    //private $widgets = array();

    /**
     * @param array
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getSubElements()
    {
        $subElements = array();
        $items = array();

        foreach ($this->getItems() as $itemData) {
            $itemClass = 'Alchemy\Component\UI\Element\Form\Widget\\' . ucfirst($itemData["xtype"]);
            unset($itemData["xtype"]);
            $items[] = new $itemClass($itemData);
        }

        $subElements["items"] = $items;

        //var_dump($subElements); die;

        return $subElements;
    }

    public function add(WidgetInterface $w)
    {
        $this->items[] = $w;
    }

    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    protected function build()
    {
    }
}

