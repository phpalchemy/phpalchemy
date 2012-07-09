<?php
return array(
    "widget_mapping" => array(
        "checkbox" => array(
            "label" => array(
                "name" => "boxlabel",
            ),
            "disabled" => array(
                "name" => 'deshabilitar',
                'value' => array(
                    'true' => 'si',
                    'false' => 'no'
                )
            )
        ),
        "textbox" => array(
            "xtype" => array(
                "value" => function($widget) {
                    return $widget->multiline ? 'input' : 'textarea';
                }
            )
        ),

    ),
    ":defaults:" => array(
        "disabled" => array(
            "true" => "disabled",
            "false" => ""
        )
    )
);

/*return array(
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
);*/