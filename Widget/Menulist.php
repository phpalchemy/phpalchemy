<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Widget\WidgetInterface;

/**
 * Class Textbox
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
class Menulist extends Widget
{
    public $disabled = false;
    public $editable  = true;
    public $emptytext = '';
    public $readonly = '';

    protected $items = array();

    public function __construct(array $attributes = array())
    {
        // call parent constructor
        parent::__construct($attributes);
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }
}
