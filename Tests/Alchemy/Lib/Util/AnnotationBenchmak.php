<?php
require_once 'Benchmark/Timer.php';
require_once __DIR__ . '/Fixtures/User.php';
include_once __DIR__ . '/../../../Alchemy/Util/Annotations.php';


use Alchemy\Util\Annotations;

$timeStart = floatval(microtime(true));
$memStart  = memory_get_usage(true);

$timer = new Benchmark_Timer(TRUE);
$timer->start();

$a = new annotations();
$r = $a->getClassAnnotations('\User');
print_r($r);
$r = $a->getMethodAnnotations('\User', 'build');
print_r($r);
$r = $a->getMethodAnnotations('\User', 'getAll');
print_r($r);
$r = $a->getMethodAnnotations('\User', 'shouldworks');
print_r($r);

$timer->stop();
$timer->display(true);
