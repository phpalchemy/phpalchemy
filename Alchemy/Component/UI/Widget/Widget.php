<?php
namespace Alchemy\Component\UI\Widget;

use Alchemy\Component\UI\Element\Element;
use Alchemy\Component\UI\ElementInterface;

/**
 * Abstract Class Widget
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
abstract class Widget extends Element implements WidgetInterface
{
    protected $value = '';
    protected $xtype = '';
    protected $fieldLabel = '';

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setXtype($xtype)
    {
        $this->xtype = $xtype;
    }

    public function getXtype()
    {
        return $this->xtype;
    }

    public function setFieldLabel($fieldLabel)
    {
        $this->fieldLabel = $fieldLabel;
    }

    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    public function prepare()
    {
    }
}

