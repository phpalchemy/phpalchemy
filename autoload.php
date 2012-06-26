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

require_once __DIR__ . '/Alchemy/Lib/Util/ClassLoader.php';
require_once __DIR__ . '/Alchemy/Lib/Util/DependencyInjectionContainer.php';

$classLoader = Alchemy\Lib\Util\ClassLoader::getInstance();
$classLoader->register('Alchemy', __DIR__ . DIRECTORY_SEPARATOR);

$classLoader->register('Notoj', __DIR__ . '/vendor/crodas/Notoj/lib/');

// if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
//     throw new Exception("Vendors for phpalchemy are missing, please execute:\n\$php composer.phar install");
// }
// require_once __DIR__ . '/vendor/autoload.php';

//var_dump(__DIR__.DIRECTORY_SEPARATOR.'vendor' .PATH_SEPARATOR. get_include_path()); die;
set_include_path(__DIR__.DIRECTORY_SEPARATOR.'vendor' .PATH_SEPARATOR. get_include_path());