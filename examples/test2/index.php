<?php

include "../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$data = array("data" => array());
$data["data"] = array(
    array(
        "name" => "John",
        "lastname" => "Doe",
        "options" => '<a href="#">edit</a>'
    ),
    array(
        "name" => "Mery",
        "lastname" => "Smith",
        "options" => '<a href="#">edit</a>'
    ),
    array(
        "name" => "Ian",
        "lastname" => "Tomson",
        "options" => '<a href="#">edit</a>'
    ),
    array(
        "name" => "Mats",
        "lastname" => "Carrey",
        "options" => '<a href="#">edit</a>'
    ),
    array(
        "name" => "sample",
        "lastname" => "sample2",
        "options" => '<a href="#">edit</a>'
    )
);

$engine->setMetaFile("test_datatable1.yaml");
$engine->setTargetBundle("html");
$element = $engine->build($data);

$generated = $element->getGenerated();

echo '<link rel="stylesheet" href="http://datatables.net/release-datatables/media/css/demo_table.css">'.PHP_EOL;
echo '<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>'.PHP_EOL;
echo '<script type="text/javascript" language="javascript" src="http://datatables.net/release-datatables/media/js/jquery.dataTables.js"></script>'.PHP_EOL;

echo $generated["html"].PHP_EOL;
echo "<script>".$generated["js"]."</script>";














