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

/*
 * PHPALCHEMY FRAMEWORK AUTOLOADER
 */

require_once __DIR__ . '/Alchemy/Component/ClassLoader/ClassLoader.php';
require_once __DIR__ . '/Alchemy/Component/DiContainer/DiContainer.php';

$classLoader = Alchemy\Component\ClassLoader\ClassLoader::getInstance();
$classLoader->register('Alchemy', __DIR__ . DIRECTORY_SEPARATOR);
$classLoader->register('Notoj', __DIR__ . '/vendor/crodas/Notoj/lib/');
$classLoader->register('Symfony', __DIR__ . '/vendor/symfony/console/');
//$classLoader->register('Zend\Filter\\', __DIR__ . '/vendor/zendframework/zend-filter/');

set_exception_handler(array(new Alchemy\Exception\Handler(), 'handle'));

//TODO this should't be loading everytime, just when it is used.
require_once __DIR__ . '/vendor/crodas/haanga/lib/Haanga.php';
