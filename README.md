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


![PhpAlchemy UI Form](hhttps://photos-2.dropbox.com/t/0/AABMwStnlb3aEakK5exLEBzoHZ225HqnYdSuVrl_uk9VYA/12/77870584/png/2048x1536/3/1397433600/0/2/phpalchemy_ui_html_form1_1.png/4Ex_kHxWxfgq2llg1GX71uwczplbTla0BO-g55LV1Hg "PhpAlchemy UI Form")

A bootstrap Form

![PhpAlchemy UI Bootstrap Form](https://photos-6.dropbox.com/t/0/AABVSAc62S9Ommp4TJLOPzcXn277fMnGhAUlDxI__LuN2Q/12/77870584/png/1024x768/3/1397430000/0/2/phpalchemy_ui_bootstrap_form1_1.png/CfbVQoPsu4cJ-jFKGvOQBLwdqbs9YxrrJC5jXZ6J0SQ "PhpAlchemy UI Bootstrap Form")



