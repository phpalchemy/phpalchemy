<?php
// autoload.php
/**
 * "PHP Alchemy" Framework -- Autoloader script
 * @version 1.0
 * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package phpalchemy
 */

require_once __DIR__ . '/Alchemy/Component/ClassLoader/ClassLoader.php';

$classLoader = Alchemy\Component\ClassLoader::getInstance();
$classLoader->register('Alchemy', __DIR__ . '/');

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception("Vendors for phpalchemy are missing, please execute:\n\$php composer.phar install");
}

require_once __DIR__ . '/vendor/autoload.php';

set_include_path(__DIR__ . '/vendor/' . get_include_path());