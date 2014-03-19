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
require $rootDir . "/Tests/tools/ClassLoader.php";
$loader = new \Alchemy\Component\ClassLoader\ClassLoader();
$loader->register("Alchemy", $rootDir, '\Alchemy\Component\UI\\');
$loader->register("Yaml", $rootDir . "/vendor/phpalchemy/Yaml");
$loader->register("Haanga", $rootDir . "/vendor/crodas/haanga/lib");

