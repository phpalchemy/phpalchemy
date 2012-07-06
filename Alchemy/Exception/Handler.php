<?php
namespace Alchemy\Exception;

use Alchemy\Adapter\HaangaView;
use Alchemy\Adapter\PhtmlView;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;

class Handler
{
    public function __construct()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        $this->request = Request::createFromGlobals();
        $this->response = new Response();
    }

    public function handle($exception)
    {
        $tplDir = realpath(__DIR__ . '/../../') . DS . 'templates' . DS;
        $data = array();
        $data['message'] = $exception->getMessage();

        if (class_exists('\Haanga')) {
            $view = new HaangaView();
            $tplFile = 'Exception.djt';
            $view->setTemplateDir($tplDir . 'djt' . DS . 'Exception');
        } else {
            $view = new PhtmlView();
            $tplFile = 'Exception.phtml';
            $view->setTemplateDir($tplDir . 'phtml' . DS . 'Exception');
        }

        $view->setCacheDir(sys_get_temp_dir() . DS . '_phpalchemy');
        $view->setTpl($tplFile);

        // setting data
        $view->assign('message', $exception->getMessage());
        $view->assign('line', $exception->getFile());
        $view->assign('file', $exception->getLine());
        $view->assign('trace', $exception->getTraceAsString());
        $view->assign('query', print_r($this->request->query->all(), true));

        $view->assign('baseurl', $this->request->getBaseUrl() . '/../');

        $view->render();
    }
}

