<?php
// Tests/bootstrap.php
define('HOME_DIR', realpath(__DIR__ . '/../'));

$loader = include HOME_DIR . '/Tests/tools/autoload.php';
$loader->add('Alchemy', HOME_DIR . DIRECTORY_SEPARATOR);
require_once __DIR__ . '/../vendor/autoload.php';

require HOME_DIR . '/autoload.php';

