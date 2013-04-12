<?php
namespace Alchemy\Component\UI\Element;

use Alchemy\Component\UI\Widget\WidgetInterface;

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

    protected  $buttons = array();
    protected $xtype = 'form';

    private $widgets = array();

    public function getWidgets()
    {
        return $this->widgets;
    }

    public function add(WidgetInterface $w)
    {
        $this->widgets[] = $w;
    }

    public function getXtype()
    {
        return $this->xtype;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    protected function build()
    {
    }
}

