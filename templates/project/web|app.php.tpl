<?php
/**
 * {namespace} Application Bootstrap
 *
 * This applaication is using PHPAlchemy Web Framework
 */

$conf = include __DIR__ . '/../autoload.php';
$app = new Alchemy\Application();
$app->init($conf);

//Sample, registering a event subscriber
$app['dispatcher']->addSubscriber(new {namespace}\Application\EventListener\BeforeResponse());
//$app['dispatcher']->addSubscriber(new {namespace}\Event\FilterRequestListener());

//$app->register(new {namespace}\Application\Service\SampleServiceProvider());

$app->run();

