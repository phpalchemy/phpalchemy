README
=========================
[![Build Status](https://secure.travis-ci.org/eriknyk/UI.png?branch=master)](http://travis-ci.org/eriknyk/UI)

UI generator for PHP
Currently is generating Forms and DataTables
===

Using
===

First install vendors

    curl -sS https://getcomposer.org/installer | php

a you can use it.
Example:

    $engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
    $engine->setMetaFile("form1.yaml");
    $engine->setTargetBundle("html");
    $element = $engine->build();
    $generated = $element->getGenerated();

    echo $generated["html"];

So easy!


[![HTML Form](https://www.dropbox.com/s/7psalcn662jlpkz/phpalchemy_ui_html_form1.png)](https://www.dropbox.com/s/7psalcn662jlpkz/phpalchemy_ui_html_form1.png)



