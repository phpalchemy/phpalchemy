#!/usr/bin/env php
<?php
$components = array("UI", "Routing", "ClassLoader", "Yaml", "WebAssets", "Http", "EventDispatcher", "DiContainer");

if (isset($argv[1])) {
    $target = $argv[1];
    if (in_array($target, $components)) {
        $components = array($target);
    } else{
        echo "ERROR: component: '$target' not found!";
        die();
    }
}

foreach ($components as $component) {
    echo `git pull -s subtree git@github.com:phpalchemy/$component.git master`;
    echo "-> tracked subtree: $component .... ok ".PHP_EOL;
}


