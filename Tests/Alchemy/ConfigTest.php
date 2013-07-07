hh
<?php
use Alchemy\Config;

/**
 * Config Unit Test
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NotojReader main test object
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->config = new Config();
    }

    /**
     * @covers Config::loadFromFile
     */
    public function testLoadFromFile()
    {
        $this->config->set('app.root_dir', '/Users/erik/devel');
        $this->config->load(HOME_DIR . '/Tests/Fixtures/conf.ini');

        $config = $this->config->all('app');
        $expected = array(
            'app.root_dir' => '/Users/erik/devel',
            "app" => Array
                (
                    "name" => "Sandbox",
                    "version" => "1.0",
                    "namespace" => "Sandbox",
                    "app_root_dir" => "%app.root_dir%/application",
                    "app_dir" => "%app.app_root_dir%/%app.namespace%",
                    "bundle_dir" => "%app.root_dir%/bundle",
                    "cache_dir" => "%app.root_dir%/cache",
                    "config_dir" => "%app.root_dir%/config",
                    "web_dir" => "%app.root_dir%/web",
                    "controllers_dir" => "%app.app_dir%/Controller",
                    "event_dir" => "%app.app_dir%/EventListener",
                    "model_dir" => "%app.app_dir%/Model",
                    "service_dir" => "%app.app_dir%/Service",
                    "view_dir" => "%app.app_dir%/View",
                    "view_templates_dir" => "%app.view_dir%/templates",
                    "view_layouts_dir" => "%app.view_dir%/layouts",
                    "view_scripts_dir" => "%app.view_dir%/scripts",
                    "meta_dir" => "%app.view_dir%/meta",
                    "vendor_dir" => "%app.root_dir%/vendor"
                ),

            "app.name" => "Sandbox",
            "app.version" => "1.0",
            "app.namespace" => "Sandbox",
            "env" => Array
                (
                    "type" => "dev",
                    "name" => "env",
                ),

            "env.type" => "dev",
            "env.name" => "env",
            "templating" => Array
                (
                    "default_engine" => "smarty",
                    "extension" => "",
                    "cache_enabled" => "",
                    "cache_dir" => "%app.cache_dir%/smarty",
                    "charset" => "UTF-8",
                    "debug" => ""
                ),

            "templating.default_engine" => "smarty",
            "templating.extension" => "",
            "templating.cache_enabled" =>"",
            "templating.charset" => "UTF-8",
            "templating.debug" =>'',
            "asset_resolv" => Array
                (
                    "current" =>"",
                    "fallback" => "framework",
                ),

            "asset_resolv.current" =>"",
            "asset_resolv.fallback" => "framework",
            "phpalchemy" => Array
                (
                    "root_dir" =>""
                ),

            "phpalchemy.root_dir" => "",
            "dev_appserver" => Array
                (
                    "name" => "built-in",
                    "host" => "127.0.0.1",
                    "port" => "3000",
                    "lighttpd_bin" =>"",
                    "php-cgi_bin" =>""
                ),

            "dev_appserver.name" => "built-in",
            "dev_appserver.host" => "127.0.0.1",
            "dev_appserver.port" => "3000",
            "dev_appserver.lighttpd_bin" =>"",
            "dev_appserver.php-cgi_bin" =>"",
            "app.app_root_dir" => "/Users/erik/devel/application",
            "app.app_dir" => "/Users/erik/devel/application/Sandbox",
            "app.bundle_dir" => "/Users/erik/devel/bundle",
            "app.cache_dir" => "/Users/erik/devel/cache",
            "app.config_dir" => "/Users/erik/devel/config",
            "app.web_dir" => "/Users/erik/devel/web",
            "app.controllers_dir" => "/Users/erik/devel/application/Sandbox/Controller",
            "app.event_dir" => "/Users/erik/devel/application/Sandbox/EventListener",
            "app.model_dir" => "/Users/erik/devel/application/Sandbox/Model",
            "app.service_dir" => "/Users/erik/devel/application/Sandbox/Service",
            "app.view_dir" => "/Users/erik/devel/application/Sandbox/View",
            "app.view_templates_dir" => "/Users/erik/devel/application/Sandbox/View/templates",
            "app.view_layouts_dir" => "/Users/erik/devel/application/Sandbox/View/layouts",
            "app.view_scripts_dir" => "/Users/erik/devel/application/Sandbox/View/scripts",
            "app.meta_dir" => "/Users/erik/devel/application/Sandbox/View/meta",
            "app.vendor_dir" => "/Users/erik/devel/vendor",
            "templating.cache_dir" => "/Users/erik/devel/cache/smarty"
        );

        $this->assertEquals($expected, $config);
    }
}

