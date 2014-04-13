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


![PhpAlchemy UI Form](https://photos-6.dropbox.com/t/0/AABH78m5OpW6aYl_jHRlssSj3LHqRgIPm0C2x56dElviGw/12/77870584/png/1024x768/3/1397408400/0/2/phpalchemy_ui_html_form1.png/gNb0pv6GWx0OV0UPiQ2oIT0qCoJdFyFB4yG41QTh_Aw "PhpAlchemy UI Form")

A bootstrap Form

![PhpAlchemy UI Form](https://photos-4.dropbox.com/t/0/AAABz7x8VdqwSEA9qOyUvaQRcnSKg44-OJ6m7gVBD7Gx7w/12/77870584/png/1024x768/3/1397430000/0/2/phpalchemy_ui_bootstrap_form1.png/JxXOWlFzBpzfBYq8erdX62LdZuWdQSe4AZGyIIxPBrY "PhpAlchemy UI Bootstrap Form")



