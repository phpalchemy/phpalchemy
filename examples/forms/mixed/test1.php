<?php

include "../../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);

$engine->setMetaFile("test1.yaml");
$engine->setTargetBundle("html");

$data = array(
    "id" => "00000000000001",
    "username" => "eriknyk",
    "password" => "admin",
    "first_name" => "Erik",
    "last_name" => "Amaru Ortiz",
    "address" => "Park Avenue, N. 277.",
    "genre" => "M"
);

$_GET["mode"] = isset($_GET["mode"]) ? $_GET["mode"] : "";
$form = $engine->build($data, array("mode" => $_GET["mode"]));

echo '<form action=',$form->action,'>';
echo '<table width="400">';
echo '<tr><th colspan="2">',$form->title,'</th></tr>';

echo '<tr><td colspan="2" align="center">';
foreach ($form->getToolbar() as $tbItem) {
    echo $tbItem->getGenerated('html') . ' ';
}
echo '</td><td>';


foreach ($form->getItems() as $item) {
    echo '<tr><td>',$item->getFieldLabel(),'</td><td>',$item->getGenerated('html'),'</td><td>';
}

echo '<tr><td colspan="2" align="center">';
foreach ($form->getButtons() as $button) {
    echo $button->getGenerated('html') . ' ';
}
echo '</td><td>';















