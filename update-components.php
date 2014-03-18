#!/usr/bin/env php
<?php
$components = array("UI", "Routing", "ClassLoader", "Yaml", "WebAssets", "Http", "EventDispatcher", "DiContainer");

if (isset($argv[1])) {
    $target = $argv[1];
    if (isset($components[$target])) {
        $components = array($target);
    } else{
        echo "ERROR: component: '$target' not found!";
        die();
    }
}

foreach ($components as $component) {
    echo `git pull -s subtree git@github.com:phpalchemy/$component.git master`;
    echo PHP_EOL." -> tracked subtree: $component .... ok ".PHP_EOL;
}


