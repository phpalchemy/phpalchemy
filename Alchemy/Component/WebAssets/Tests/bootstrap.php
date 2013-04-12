<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//bootstrap.php
$baseDir = realpath(__DIR__ .'/../../') . DIRECTORY_SEPARATOR;

include 'Bundle.php';
include 'Asset.php';
include 'File.php';
include 'Filter/FilterInterface.php';
include 'Filter/CssMinFilter.php';
include 'Filter/JsMinPlusFilter.php';
include 'Filter/JsMinFilter.php';

