<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\Widget\WidgetInterface;

/**
 * Class Parser
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/Routing
 */
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