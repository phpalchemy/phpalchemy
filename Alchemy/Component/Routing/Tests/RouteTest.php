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
     * @covers Route::setPattern
     * @todo   Implement testSetPattern().
     */
    public function testSetPattern()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::getPattern
     * @todo   Implement testGetPattern().
     */
    public function testGetPattern()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::setDefaults
     * @todo   Implement testSetDefaults().
     */
    public function testSetDefaults()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::getDefaults
     * @todo   Implement testGetDefaults().
     */
    public function testGetDefaults()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::setRequirements
     * @todo   Implement testSetRequirements().
     */
    public function testSetRequirements()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::getRequirements
     * @todo   Implement testGetRequirements().
     */
    public function testGetRequirements()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::getType
     * @todo   Implement testGetType().
     */
    public function testGetType()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::getVars
     * @todo   Implement testGetVars().
     */
    public function testGetVars()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::prepare
     * @todo   Implement testPrepare().
     */
    public function testPrepare()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Route::match
     * @todo   Implement testMatch().
     */
    public function testMatch()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
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
}
















