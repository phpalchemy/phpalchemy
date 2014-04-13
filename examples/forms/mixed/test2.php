<?php
include "../../boot.php";

//use Symfony\Component\Yaml\Parser;
//$yaml = new Parser();
//$value = $yaml->parse(file_get_contents("test1.yaml"));
//echo "<pre>";
//print_r($value);
//die;

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);

$engine->setMetaFile("test1.yaml");
$engine->setTargetBundle("bootstrap");

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

echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />';
echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />';
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>';
echo '<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>';

echo '<div class="container">';
echo '<div class="col-xs-8">';
echo '<h1>',$form->title,'</h1>';

echo '<div class="row"><div class="pull-right"><div class="btn-group" role="toolbar">';
foreach ($form->getToolbar() as $i => $tbItem) {
    echo $tbItem->getGenerated('html') . ' ';
}
echo '</div></div></div>';

echo '<div class="row">';
echo '<form role="form" ';
    foreach($form->getAttributes() as $k=>$v)
        echo " ",$k,'="',$v,'"';
    echo '>';

foreach ($form->getItems() as $item) {
    if ($item->getXtype() != "title" && $item->getXtype() != "hidden") {
        echo '<div class="form-group">';
        echo '<label for="' . $item->getId() . '">' . $item->getFieldLabel() . '</label>';
        echo $item->getGenerated('html');
        echo '</div>';
    } else {
        echo $item->getGenerated('html');
    }
}

foreach ($form->getButtons() as $button) {
    echo $button->getGenerated('html') . ' ';
}

echo '</form>';
echo '</div></div></div>';