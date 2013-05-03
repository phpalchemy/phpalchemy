<?php
// Alchemy\Component\UI Tests Bootstrap
$basePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
//var_dump($basePath); die;
include $basePath . 'Tests/tools/ClassLoader.php';
$loader = new Alchemy\Component\ClassLoader\ClassLoader();
$loader->register('Alchemy\Component\UI', $basePath, 'Alchemy/Component/UI/');

require_once $basePath . 'vendor/crodas/haanga/lib/Haanga.php';

//require_once 'Parser.php';

