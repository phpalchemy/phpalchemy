<?php
if (! is_dir(__DIR__.'/../vendor')) {
    throw new Exception(
        "ERROR: Vendors are missing!" . PHP_EOL .
        "Please execute the following commands to prepare/install vendors:" .PHP_EOL.PHP_EOL.
        "$>curl -sS https://getcomposer.org/installer | php" . PHP_EOL .
        "$>php composer.phar install"
    );
}
$rootDir = realpath(__DIR__ . "/../");

require $rootDir . "/Element/Element.php";
require $rootDir . "/Element/Form.php";
require $rootDir . "/Widget/WidgetInterface.php";
require $rootDir . "/Widget/Widget.php";

require $rootDir . "/Widget/Checkbox.php";
require $rootDir . "/Widget/Checkgroup.php";
require $rootDir . "/Widget/Flipswitch.php";
require $rootDir . "/Widget/Listbox.php";
require $rootDir . "/Widget/Radiogroup.php";
require $rootDir . "/Widget/Textbox.php";

require $rootDir . "/vendor/phpalchemy/Yaml/Yaml.php";
require $rootDir . "/vendor/crodas/haanga/lib/Haanga.php";

require $rootDir . "/Engine.php";
require $rootDir . "/Parser.php";
require $rootDir . "/Reader.php";
require $rootDir . "/ReaderFactory.php";
require $rootDir . "/XmlReader.php";
require $rootDir . "/YamlReader.php";
