<?php
namespace Alchemy\Component\UI\Element;

use \Alchemy\Component\UI\Element\Form\Widget\Button;

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
    public $width = '';
    public $action = '';
    public $method = 'post';
    public $title = '';

    protected $buttons = array();
    protected $toolbar = array();
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
        $buttons = array();
        $tbItems = array();

        foreach ($this->getItems() as $itemData) {
            $itemClass = 'Alchemy\Component\UI\Element\Form\Widget\\' . ucfirst($itemData["xtype"]);
            unset($itemData["xtype"]);
            $items[] = new $itemClass($itemData);
        }

        foreach ($this->getButtons() as $buttonData) {
            $buttons[] = new Button($buttonData);
        }

        foreach ($this->getToolbar() as $tbItemData) {
            $tbItems[] = new Button($tbItemData);
        }

        $subElements["items"] = $items;
        $subElements["buttons"] = $buttons;
        $subElements["toolbar"] = $tbItems;

        $this->items = $items;
        $this->buttons = $buttons;
        $this->toolbar = $tbItems;

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

    /**
     * @param array $toolbarItems
     */
    public function setToolbar($toolbar)
    {
        $this->toolbar = $toolbar;
    }

    /**
     * @return array
     */
    public function getToolbar()
    {
        return $this->toolbar;
    }

    protected function build()
    {
    }
}

