<?php
$appIniFile = dirname(__FILE__) . '/application.ini';

if (!file_exists($appIniFile)) {
    throw new Exception("File '$appIniFile' is missing.");
}

$config = @parse_ini_file($appIniFile, true);

if (empty($config)) {
    throw new Exception("Parse Error: '$appIniFile' is empty or has errors.");
}

if (empty($config['phpalchemy']['root_dir'])) {
    throw new Exception("Configuration Missing: 'phpalchemy.root_dir' conf. is missing");
}

if (substr($config['phpalchemy']['root_dir'], 0, 2) === '..') { // is relative path
    $phpalchemyRootDir = realpath(
        dirname(__FILE__) . DIRECTORY_SEPARATOR . $config['phpalchemy']['root_dir']
    );
} else {
    $phpalchemyRootDir = $config['phpalchemy']['root_dir'];
}

if (! isset($phpalchemyRootDir) || ! is_dir($phpalchemyRootDir)) {
    throw new Exception(
        "Configuration Error: phpalchemy root directory not found on: " .
        "'{$config['phpalchemy']['root_dir']}'"
    );
}

empty($phpalchemyRootDir) || $config['phpalchemy']['root_dir'] = $phpalchemyRootDir;

if (!file_exists($config['phpalchemy']['root_dir'] . DIRECTORY_SEPARATOR.'autoload.php')) {
    throw new Exception(
        "PhpAlchemy File Not Found: Autoloader is missing, maybe phpalchemy " .
        "is not installed property on: '{$config['phpalchemy']['root_dir']}'"
    );
}

if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception(sprintf(
        "Vendors for project '{appName}' are missing.\nPlease execute the following commands:\n\n" .
        "~\$curl -s http://getcomposer.org/installer | php\n\$>php composer.phar install"
    ));
}

function pr($v){echo "<pre>".print_r($v, true)."</pre>";}

$config['app']['root_dir'] = realpath(__DIR__);

require_once $config['phpalchemy']['root_dir'] . '/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . PATH_SEPARATOR . get_include_path());

return $config;
