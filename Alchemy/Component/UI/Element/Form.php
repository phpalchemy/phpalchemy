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
     * @var string contains Form mode, it onl accepts values: [edit|view]
     */
    protected $mode = 'edit';

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

    public function getSubElements($matchData = array())
    {
        $subElements = array();
        $items = array();
        $buttons = array();
        $tbItems = array();
        $validModes = array("view", "edit");

        foreach ($this->getItems() as $itemData) {
            $itemClass = 'Alchemy\Component\UI\Element\Form\Widget\\' . ucfirst($itemData["xtype"]);
            unset($itemData["xtype"]);
            $item = new $itemClass($itemData);

            if (in_array($this->mode, $validModes)) {
                $item->setMode($this->mode);
            }

            $items[] = $item;
        }

        foreach ($this->getButtons() as $buttonData) {
            $button = new Button($buttonData);
            //$button->setMatchData($matchData);
            //$button->setAttributesFromArray($buttonData);

            if (in_array($this->mode, $validModes)) {
                $button->setMode($this->mode);
            }

            $buttons[] = $button;
        }


        foreach ($this->getToolbar() as $tbItemData) {
            $tbItem = new Button(array(), $this);
            $tbItem->setMatchData($matchData);
            $tbItem->setAttributesFromArray($tbItemData);

            if (in_array($this->mode, $validModes)) {
                $button->setMode($this->mode);
            }
            $tbItems[] = $tbItem;
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

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    protected function build()
    {
    }
}

