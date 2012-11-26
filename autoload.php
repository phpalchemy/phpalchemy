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
$classLoader->register('Zend\Filter\\', __DIR__ . '/vendor/zendframework/zend-filter/');

set_exception_handler(array(new Alchemy\Exception\Handler(), 'handle'));

if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception(sprintf(
        "Vendors for phpalchemy are missing.\nPlease execute those commands on phpalchemy home dir.:\n" .
        "\$>cd /path/to/phpalchemy\n\$>curl -s http://getcomposer.org/installer | php\n\$>php composer.phar install"
    ));
}

//TODO this should't be loading evertime, just when it is used.
require_once __DIR__ . '/vendor/crodas/Haanga/lib/Haanga.php';

set_include_path(
    __DIR__ . DIRECTORY_SEPARATOR . 'vendor'  . PATH_SEPARATOR .
    __DIR__ . '/vendor/pear-pear.php.net'      .  PATH_SEPARATOR .
    __DIR__ . '/vendor/pear-pear.php.net/PEAR' .  PATH_SEPARATOR .
    get_include_path()
);

