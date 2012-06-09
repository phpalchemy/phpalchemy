<?php
// Tests/bootstrap.php

$basePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

$loader = include $basePath . 'Tests/tools/autoload.php';
$loader->add('IronG', $basePath);

//$loader->add('Symfony', $basePath . 'vendor/symfony/http-foundation/');


