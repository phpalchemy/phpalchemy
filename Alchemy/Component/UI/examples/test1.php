<?php

if (! is_dir(__DIR__.'/../vendor')) {
    throw new Exception(
        "ERROR: Vendors are missing!" . PHP_EOL .
        "Please execute the following commands to prepare/install vendors:" .PHP_EOL.PHP_EOL.
        "$>curl -sS https://getcomposer.org/installer | php" . PHP_EOL .
        "$>php composer.phar install"
    );
}

require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkgroup.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";
require __DIR__."/../Widget/Checkbox.php";

require __DIR__."/../Element/Element.php";
require __DIR__."/../Element/Form.php";

require __DIR__."/../Engine.php";
require __DIR__."/../Parser.php";
require __DIR__."/../Reader.php";
require __DIR__."/../ReaderFactory.php";
require __DIR__."/../XmlReader.php";
require __DIR__."/../YamlReader.php";



$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
