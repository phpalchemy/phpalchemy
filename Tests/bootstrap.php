<?php
//bootstrap.php
$baseDir = realpath(__DIR__ .'/../../') . DIRECTORY_SEPARATOR;

include 'Bundle.php';
include 'Asset.php';
include 'File.php';
include 'Filter/FilterInterface.php';
include 'Filter/CssMinFilter.php';

