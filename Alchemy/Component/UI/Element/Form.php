<?php
namespace Alchemy\Component\UI\Element;

/**
 * Class Form
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
class Form extends Element
{
    public function render()
    {
        //var_dump($this); die;
        echo $this->build();

        //foreach ($this->all() as $i => $widget) {

            //TODO make event driven this
            //$widget->prepare();

            //$attributes = $widget->getAttributes();

            //var_dump($attributes);
            //
            //echo $this->buildUiWidget($widget);
        //}
    }
}