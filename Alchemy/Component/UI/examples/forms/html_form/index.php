<?php

include "../../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$data = empty($_POST) ? array() : $_POST;

$engine->setMetaFile("form2.yaml");
$engine->setTargetBundle("html");
$element = $engine->build($data);

$generated = $element->getGenerated();

echo $generated["html"];

if (! empty($_POST)) {
    echo "<pre>";
    echo "POST DATA" . PHP_EOL;
    print_r($_POST);
}














