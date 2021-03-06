<?php
namespace Alchemy\Mvc\Adapter;

use \Alchemy\Mvc\View;

class HaangaView extends View
{
    public function __construct($tpl = '')
    {
        parent::__construct($tpl);

        require_once "crodas/Haanga/lib/Haanga.php";
    }

    //Wrapped

    public function render()
    {
        $config = array(
            'template_dir' => $this->getTemplateDir(),
            'cache_dir' => $this->getCacheDir(),
            'debug' => $this->debug,
            'compiler' => array(
                'allow_exec'  => true,
            ),
        );

        if ($this->cache && is_callable('xcache_isset')) {
            /* don't check for changes in the template for the next 5 min */
            $config['check_ttl'] = 300;
            $config['check_get'] = 'xcache_get';
            $config['check_set'] = 'xcache_set';
        }

        \Haanga::configure($config);

        \Haanga::Load($this->getTpl(), $this->data);
    }
}

