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
class Textbox extends Widget
{
    public $disabled;
    public $editable;
    public $emptytext;
    public $maxlength;
    public $multiline;
    public $placeholder;
    public $readonly;
    public $required;
    public $type;
    public $wrap;
    public $rows;
    public $cols;

    public $size;

    protected $datemask = 'YYYY-MM-DD';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->setXtype('textbox');
    }
}

