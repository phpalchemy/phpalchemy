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

    public function setChecked(bool $value)
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
}

