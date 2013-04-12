<?php
// Alchemy\Component\UI Tests Bootstrap
$basePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

include $basePath . 'Tests/tools/ClassLoader.php';
$loader = new Alchemy\Component\ClassLoader\ClassLoader();
$loader->register('Alchemy\Component\UI', $basePath, 'Alchemy/Component/UI/');

//require_once 'Parser.php';

