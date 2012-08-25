<?php
// Tests/bootstrap.php

$basePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

$loader = include $basePath . 'Tests/tools/autoload.php';
$loader->add('Alchemy', $basePath);
require_once __DIR__ . '/../vendor/autoload.php';

//$loader->add('Symfony', $basePath . 'vendor/symfony/http-foundation/');

//require 'autoload.php';

