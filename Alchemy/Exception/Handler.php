<?php
namespace Alchemy\Exception;

use Alchemy\Mvc\Adapter\HaangaView;
use Alchemy\Mvc\Adapter\PhtmlView;
use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;

use Alchemy\Exception\ExceptionInterface;

class Handler
{
    public function __construct()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        $this->request = Request::createFromGlobals();
    }

    public function handle($exception)
    {
        echo $this->getOutput($exception);
    }

    public function getOutput($exception)
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
        $view->assign('line', $exception->getLine());
        $view->assign('file', $exception->getFile());
        $view->assign('trace', $exception->getTraceAsString());
        $view->assign('query', print_r($this->request->query->all(), true));
        $baseurl = $this->request->getBaseUrl();

        $baseurl = $this->request->getHttpHost() . $this->request->getBaseUrl();
        if (substr($baseurl, -4) == '.php') {
            $baseurl = substr($baseurl, 0, strrpos($baseurl, '/') + 1);
        } elseif (substr($baseurl, -1) !== '/') {
            $baseurl .= '/';
        }

        $view->assign('baseurl', 'http://' . $baseurl);

        return $view->getOutput();
    }

}

