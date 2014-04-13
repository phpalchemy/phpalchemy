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
class Button extends Widget
{
    protected $type = "button";
    protected $label = "";
    protected $url = "";
    protected $target = "";
    protected $menu = array();
    protected $iconCls = "";

    public function __construct(array $attributes = array(), $parent = null)
    {
        parent::__construct($attributes, $parent);
        $this->setXtype('button');
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setIconCls($iconCls)
    {
        $this->iconCls = $iconCls;
    }

    /**
     * @return string
     */
    public function getIconCls()
    {
        return $this->iconCls;
    }

    /**
     * @param array $menu
     */
    public function setMenu($menu)
    {
        $composed = array();

        if (isset($menu["items"])) {
            $composed["items"] = $menu["items"];
        } else {
            $composed["items"] = $menu;
        }
        if (isset($menu["type"])) {
            $composed["type"] = $menu["type"];
        } else {
            $composed["type"] = "single";
        }

        $this->menu = $composed;
    }

    /**
     * @return array
     */
    public function getMenu()
    {
        return $this->menu;
    }
}

