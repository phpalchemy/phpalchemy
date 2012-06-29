<?php
namespace Alchemy\Component\UI\Element;

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