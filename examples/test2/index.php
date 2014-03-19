<?php

include "../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$data = empty($_POST) ? array() : $_POST;

$engine->setMetaFile("test_datatable1.yaml");
$engine->setTargetBundle("html");
$element = $engine->build($data);

$generated = $element->getGenerated();

echo $generated["html"];
//echo $generated["js"];














