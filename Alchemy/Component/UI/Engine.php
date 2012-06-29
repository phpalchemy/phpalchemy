<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Widget\WidgetInterface;

class Engine
{
    protected static $schema = 'html';
    protected static $cacheDir = './';

    public function __construct()
    {

    }

    public static function setSchema($schema)
    {
        self::$schema = $schema;
    }

    public function buildWidget(WidgetInterface $widget)
    {
        //$content = file_get_contents($this->schema.'.ui');
        $tpl   = self::$schema . '.schema.djt';
        $cache = true;
        $debug = true;

        $config = array(
            'template_dir' => __DIR__ . '/schema/',
            'cache_dir' => self::$cacheDir,
            'debug' => $debug,
        );

        if ($cache && is_callable('xcache_isset')) {
            /* don't check for changes in the template for the next 5 min */
            $config['check_ttl'] = 300;
            $config['check_get'] = 'xcache_get';
            $config['check_set'] = 'xcache_set';
        }

        $data = array('element' => $widget->getProperties());

        \Haanga::configure($config);
        $output = '';
        \ob_start();
        \Haanga::Load($tpl, $data);
        $output = \ob_get_contents();
        \ob_end_clean();

        return $output;
    }

    public static function setCacheDir($path)
    {
        self::$cacheDir = $path;
    }
}