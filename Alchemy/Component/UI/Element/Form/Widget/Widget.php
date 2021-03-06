<?php
namespace Alchemy\Component\UI\Element\Form\Widget;

use Alchemy\Component\UI\Element;

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
abstract class Widget extends Element\Element implements Element\WidgetInterface
{
    protected $value = '';
    protected $xtype = '';
    protected $fieldLabel = '';
    protected $hint = '';
    /**
     * @var string contains Form mode, it onl accepts values: [edit|view]
     */
    protected $mode = 'edit';

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

    public function setHint($hint)
    {
        $this->hint = $hint;
    }

    public function getHint()
    {
        return $this->hint;
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

    public function prepare()
    {
    }
}

