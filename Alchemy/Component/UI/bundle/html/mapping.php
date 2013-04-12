<?php
return array(
    "widget_mapping" => array(
        "checkbox" => array(
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
                    return $widget->multiline ? 'textarea' : 'text';
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
