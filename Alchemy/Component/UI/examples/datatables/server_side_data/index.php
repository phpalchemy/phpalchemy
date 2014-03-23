<?php

include "../../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$engine->setMetaFile("list1.yaml");
$engine->setTargetBundle("html");
$element = $engine->build();

$generated = $element->getGenerated();

echo '<link rel="stylesheet" href="//cdn.datatables.net/1.10.0-beta.2/css/jquery.dataTables.css">'.PHP_EOL;
echo '<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>'.PHP_EOL;
echo '<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.0-beta.2/js/jquery.dataTables.js"></script>'.PHP_EOL;
echo '<h1>Jquery - DataTables 1.10 - Async Object Data Example</h1>';
echo $generated["html"].PHP_EOL;
echo "<script>".$generated["js"]."</script>";














