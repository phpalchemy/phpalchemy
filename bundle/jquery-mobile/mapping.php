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
                    if ($widget->multiline)
                        return 'textarea';
                    elseif ($widget->type == 'password')
                        return 'password';
                    else
                        return 'text';
                }
            )
        ),
        "listbox" => array(
            "xtype" => array(
                "value" => 'select'
            ),
            "multiple" => array(
                "value" => "multiple"
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
