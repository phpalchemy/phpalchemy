<?php
return array(
    "textbox" => array(
        "xtype" => 'input',
        "_callback" => function($widget) {
            $attributes = array();

            if ($widget->multiline) {
                $attributes["xtype"] = "textarea";
                $attributes["rows"]  = "rows1";
            }

            return $attributes;
        }
    ),
    "checbox" => array(
        "label" => "label1"
    )
);