<?php
namespace {namespace}\Application\Controller;

use Alchemy\Component\Http\Response;

class SampleController extends \Alchemy\Mvc\Controller
{
    /**
     * test passing the template file name to view() annotation (with complete path)
     *
     * @view()
     */
    function indexAction()
    {
        //1st option.- setting controller attribute
        //$this->view->title = 'Hello Word';

        //2nd option.- returning a associative array
        return array('title' => 'Hello Word (smarty)');
    }

    /**
     * @View()
     */
    function testAction()
    {
        return array();
    }

    /**
     * test passing the template file name to view() annotation (without path)
     * it is mapped with controller and action names without suffix
     */
    function test1Action()
    {
        return new \Alchemy\Component\Http\Response('<h1>Hello World (Plain)</h1>');
    }

    /**
     * test passing the template file name (any) to view() annotation
     *
     * @view("Sample/someTemplate.tpl")
     */
    function test2Action()
    {
        return array('title' => 'Hello Test');
    }

    /**
     * test passing the template file name to view() annotation (without extension)
     * it is provided by configuration (on application.ini)
     *
     * @view("Sample/someTemplate")
     */
    function test3Action()
    {
        return array('title' => 'Hello Test');
    }

    /**
     * test using template engine
     * @view(file="Sample/twigTest.twig", engine="twig")
     */
    function twigTestAction()
    {
        return array('title' => 'Hello Test, using twig template engine');
    }

    /**
     * test using template engine
     * @view(file="Sample/twigTest.twig", engine="haanga")
     */
    function haangaTestAction()
    {
        return array('title' => 'Hello Test, using haanga template engine');
    }

    function helloAction()
    {
        $this->getResponse()->setContent('hello action');
    }

    function action1Action(\Alchemy\Component\Http\Request $request)
    {
        $this->getResponse()->setContent('sample #1 action  - data' . $request->server->get('HTTP_USER_AGENT'));
    }

    function action2Action(\Alchemy\Component\Http\Request $request, $name)
    {
        $this->getResponse()->setContent('sample #2 action - hello ' . $name);
    }

    function action3Action($name, $lastname)
    {
        return new Response('sample #3 action - hello ' . $name . ' ' . $lastname);
    }

    function action4Action($lastname, $name)
    {
        $this->getResponse()->setContent('sample #4 action - hello ' . $name . ' ' . $lastname);
    }

    /**
     * @ServeUi(contact_form = sample/register.yaml)
     */
    public function registerAction()
    {
    }
}
