<?php
use Alchemy\Component\Routing\Route;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-05-30 at 13:04:45.
 */
class RouteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Route
     */
    protected $route;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->route = new Route;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * Testing all Route::match() combinations
     */

    /**
     * @covers Route::match()
     * - pattern '/'
     * - match   '/'
     */
    public function testMatchRoot()
    {
        $route    = new Route('/', array('controller'=>'test', 'action'=>'index'));
        $expected = array('controller'=>'test', 'action'=>'index');

        $this->assertEquals($expected, $route->match('/'));
    }

    /**
     * @covers Route::match()
     * - pattern '/{controller}'
     * - match   '/my_controller'
     */
    public function testMatchOnlyController()
    {
        $route    = new Route('/{controller}', array('action' => 'index'));
        $expected = array('controller' => 'my_controller', 'action' => 'index');

        $this->assertEquals($expected, $route->match('/my_controller'));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}'
     * - match   '/my_controller/my_action'
     */
    public function testMatchOnlyAction()
    {
        $route    = new Route('/my_controller/{action}', array('controller'=>'my_controller'));
        $expected = array('controller'=>'my_controller', 'action'=>'my_action');

        $this->assertEquals($expected, $route->match('/my_controller/my_action'));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}'
     * - match   '/my_controller/my_action'
     */
    public function testMatchBoth()
    {
        $route    = new Route('/{controller}/{action}');
        $expected = array('controller'=>'my_controller', 'action'=>'my_action');

        $this->assertEquals($expected, $route->match('/my_controller/my_action'));
    }

    /**
     * @covers Route::match()
     * - pattern '/{controller}/{action}/{year}/{month}'
     * - match   '/my_controller/my_action/2012/05'
     */
    public function testMatchLong()
    {
        $route    = new Route('/{controller}/{action}/{year}/{month}');
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>'2012', 'month'=>'05');

        $this->assertEquals($expected, $route->match('/my_controller/my_action/2012/05'));
    }

    /**
     * @covers Route::match()
     * - pattern '/{language}-{country}/{controller}/{action}'
     * - match   '/en-us/my_controller/my_action'
     */
    public function testMatchLongComposed()
    {
        $route    = new Route('/{language}-{country}/{controller}/{action}');
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'language'=>'en', 'country'=>'us');

        $this->assertEquals($expected, $route->match('/en-us/my_controller/my_action'));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}/{year}'
     * - match   '/my_controller/my_action/1012'
     */
    public function testMatchWithRequirements()
    {
        $route    = new Route(
            '/my_controller/{action}/{year}',
            array('controller'=>'my_controller'),
            array('year'=>'\d+')
        );
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);

        $this->assertEquals($expected, $route->match('/my_controller/my_action/2012'));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}/{year}'
     * - match   '/my_controller/my_action/1012'
     */
    public function testMatchWithMethodRequirements()
    {
        if (! class_exists('Alchemy\Component\Http\Request')) {
            $this->markTestSkipped(
              'Alchemy\Component\Http\Request is not available.'
            );
        }

        $request = Alchemy\Component\Http\Request::create('/my_controller/my_action/2012', 'GET');

        $route    = new Route(
            '/my_controller/{action}/{year}',
            array(
                'controller' => 'my_controller'
            ),
            array(
                'year' => '\d+',
                '_method' => 'GET'
            )
        );
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);

        $this->assertEquals($expected, $route->match($request));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}/{year}'
     * - match   '/my_controller/my_action/1012'
     */
    public function testMatchWithMethodsRequirements()
    {
        if (! class_exists('Alchemy\Component\Http\Request')) {
            $this->markTestSkipped(
              'Alchemy\Component\Http\Request is not available.'
            );
        }

        $request = Alchemy\Component\Http\Request::create('/my_controller/my_action/2012', 'GET');

        $route    = new Route(
            '/my_controller/{action}/{year}',
            array(
                'controller' => 'my_controller'
            ),
            array(
                'year' => '\d+',
                '_method' => array('GET', 'POST')
            )
        );
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);

        $this->assertEquals($expected, $route->match($request));
    }

    /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}/{year}'
     * - match   '/my_controller/my_action/1012'
     */
    public function testFailMatchWithMethodRequirements()
    {
        if (! class_exists('Alchemy\Component\Http\Request')) {
            $this->markTestSkipped(
              'Alchemy\Component\Http\Request is not available.'
            );
        }

        $request = Alchemy\Component\Http\Request::create('/my_controller/my_action/2012', 'PUT');

        $route    = new Route(
            '/my_controller/{action}/{year}',
            array(
                'controller' => 'my_controller'
            ),
            array(
                'year' => '\d+',
                '_method' => 'GET'
            )
        );
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);

        $this->assertFalse($route->match($request));
    }

       /**
     * @covers Route::match()
     * - pattern '/my_controller/{action}/{year}'
     * - match   '/my_controller/my_action/1012'
     */
    public function testFailMatchWithMethodsRequirements()
    {
        if (! class_exists('Alchemy\Component\Http\Request')) {
            $this->markTestSkipped(
              'Alchemy\Component\Http\Request is not available.'
            );
        }

        $request = Alchemy\Component\Http\Request::create('/my_controller/my_action/2012', 'PUT');

        $route    = new Route(
            '/my_controller/{action}/{year}',
            array(
                'controller' => 'my_controller'
            ),
            array(
                'year' => '\d+',
                '_method' => array('GET', 'POST')
            )
        );
        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);

        $this->assertFalse($route->match($request));
    }

    public function testSetters()
    {
        $route = new Route();
        $route->setPattern('/my_controller/{action}/{year}');
        $route->setDefaults(array('controller'=>'my_controller'));
        $route->setRequirements(array('year'=>'\d+'));

        $expected = array('controller'=>'my_controller', 'action'=>'my_action', 'year'=>2012);
        $this->assertEquals($expected, $route->match('/my_controller/my_action/2012'));
    }

    public function testMapping()
    {
        $route = new Route();
        $route->setPattern("/{_controller}/{_action}");
        $route->setMapping(array(
            "_controller" => array("to" => '\Sandbox\Controller\{_controller}Controller', "transform" => "camelcase"),
            "_action" => array("to" => "{_action}Action", "transform" => "camelcase,lcfirst")
        ));

        $expected = array(
            '_controller'=>'\Sandbox\Controller\UserRoleController',
            '_action'=>'testListAction'
        );

        $this->assertEquals($expected, $route->match('/user_role/test_list'));
    }

    public function testMappingComplex()
    {
        $route = new Route();
        $route->setPattern("/{_module}/{_controller}/{_action}");
        $route->setMapping(array(
            "_controller" => array("to" => 'Sandbox\Controller\{_module}\{_controller}Controller', "transform" => "camelcase"),
            "_action" => array("to" => "{_action}Action", "transform" => "camelcase,lcfirst")
        ));

        $expected = array(
            '_controller'=>'\Sandbox\Controller\Admin\UserRoleController',
            '_action'=>'testListAction'
        );

        $this->assertEquals($expected, $route->match('/admin/user_role/test_list'));
    }
}

