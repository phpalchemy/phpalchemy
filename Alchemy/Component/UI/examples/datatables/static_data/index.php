<?php

include "../../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$data = json_decode(file_get_contents(__DIR__."/array_data.json"), true);
$engine->setMetaFile("list2.yaml");
$engine->setTargetBundle("html");
$element = $engine->build($data);

$generated = $element->getGenerated();

echo '<link rel="stylesheet" href="//cdn.datatables.net/1.10.0-beta.2/css/jquery.dataTables.css">'.PHP_EOL;
echo '<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>'.PHP_EOL;
echo '<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.0-beta.2/js/jquery.dataTables.js"></script>'.PHP_EOL;
echo '<h1>Jquery - DataTables 1.10 - Static Data Example</h1>';
echo $generated["html"].PHP_EOL;
echo "<script>".$generated["js"]."</script>";














