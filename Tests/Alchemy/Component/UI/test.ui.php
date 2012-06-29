<?php
$basePath = realpath(__DIR__ . '/../../../../') . DIRECTORY_SEPARATOR;
$loader = include $basePath . 'Tests/tools/autoload.php';
$loader->add('Alchemy', $basePath);

// $textbox = new Alchemy\UI\Widget\TextboxWidget();
// $at = $textbox->getAttributes();
//print_r($at);

require_once $basePath . 'Alchemy/Lib/Util/DependencyInjectionContainer.php';
require $basePath . "vendor/crodas/Haanga/lib/Haanga.php";

Alchemy\Component\UI\Engine::setSchema('html');
Alchemy\Component\UI\Engine::setCacheDir(__DIR__ . '/cache/');

$form = new Alchemy\Component\UI\Element\Form('form1');

$text1 = new Alchemy\Component\UI\Widget\Textbox();
$text1->name = 'text1';
$text1->size = 25;

$form->add($text1);

$text2 = new Alchemy\Component\UI\Widget\Textbox();
$text2->id = 'myTextId';
$text2->name = 'text2';
$text2->size = 50;

$form->add($text2);


$form->render();
