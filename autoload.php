<?php
/*
 * autoload.php
 *
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* PHPALCHEMY FRAMEWORK AUTOLOADER */

require_once __DIR__ . '/Alchemy/Component/ClassLoader/ClassLoader.php';

$classLoader = Alchemy\Component\ClassLoader::getInstance();
$classLoader->register('Alchemy', __DIR__ . DIRECTORY_SEPARATOR);

// if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
//     throw new Exception("Vendors for phpalchemy are missing, please execute:\n\$php composer.phar install");
// }

// require_once __DIR__ . '/vendor/autoload.php';

set_include_path(__DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR . get_include_path());