<?php
namespace Alchemy\Adapter;

class HaangaView extends \Alchemy\Mvc\View
{
    public function __construct($tpl = NULL)
    {
        parent::__construct($tpl);

        require "Haanga/lib/Haanga.php";
    }

    //Wrapped

    public function render()
    {
        \Haanga::configure(array(
            'template_dir' => $this->getTemplateDir(),
            'cache_dir' => $this->getCacheDir(),
        ));

        \Haanga::Load($this->getTpl(), $this->data);
    }
}


