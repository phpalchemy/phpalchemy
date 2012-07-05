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
class Textbox extends Widget
{
    public $disabled  = false;
    public $editable  = true;
    public $emptytext = '';
    public $maxlength = '';
    public $multiline = false;
    public $placeholder = '';
    public $readonly = '';
    public $size = '20';
    public $type = '';
    public $wrap = '';
    public $rows = '';
    public $cols = '';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->setXtype('textbox');
    }
}
