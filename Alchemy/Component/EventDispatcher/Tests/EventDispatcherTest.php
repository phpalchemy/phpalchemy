<?php
//require_once 'EventDispatcher.php';
use Alchemy\Component\EventDispatcher\EventDispatcher;
use Alchemy\Component\EventDispatcher\Event;

require_once dirname(__FILE__) . '/Fixtures/mix_events.php';
require_once dirname(__FILE__) . '/Fixtures/SampleListener.php';
require_once dirname(__FILE__) . '/Fixtures/Sample2Listener.php';



/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-05-24 at 23:48:37.
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->dispatcher);
    }

    /**
     * @covers EventDispatcher::testAddListener
     */
    public function testAddListener()
    {
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin');
        $this->assertTrue($this->dispatcher->hasListeners('system.before_login'));
        $this->assertFalse($this->dispatcher->hasListeners('system.after_login'));

        $this->dispatcher->addListener('system.after_login', 'onAfterLogin');
        $this->assertTrue($this->dispatcher->hasListeners('system.before_login'));
        $this->assertCount(1, $this->dispatcher->getListeners('system.before_login'));
        $this->assertCount(1, $this->dispatcher->getListeners('system.after_login'));
        $this->assertCount(2, $this->dispatcher->getListeners());
    }

    /**
     * @covers EventDispatcher::removeListener
     */
    public function testRemoveListener()
    {
        $this->dispatcher->addListener('system.before_login', 'dummy');
        $this->assertCount(1, $this->dispatcher->getListeners('system.before_login'));

        $this->dispatcher->removeListener('system.before_login', 'dummy');
        $this->assertCount(0, $this->dispatcher->getListeners('system.before_login'));
        $this->assertCount(0, $this->dispatcher->getListeners());
    }

    /**
     * @covers EventDispatcher::dispatch
     */
    public function testDispatch()
    {
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin');

        $this->expectOutputString('executed before login');
        $this->dispatcher->dispatch('system.before_login', new Event());
    }

    /**
     * @covers EventDispatcher::dispatchUntil
     */
    public function testDispatchUntil()
    {
        $this->expectOutputString('executed before login');

        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin');
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin2'); //this callback returns true
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin3');

        // 2nd callback returns a non null value, so the 3rd callback shouldn't be executed
        $expected  = 'executed before login';
        $expected .= 'executed before login #2';

        $this->expectOutputString($expected);
        $this->dispatcher->dispatchUntil('system.before_login', new Event());
    }

    /**
     * @covers EventDispatcher::hasListeners
     */
    public function testHasListeners()
    {
        $this->assertFalse($this->dispatcher->hasListeners());
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin');

        $this->assertTrue($this->dispatcher->hasListeners());
        $this->assertTrue($this->dispatcher->hasListeners('system.before_login'));

        $this->dispatcher->addListener('system.after_login', 'onAfterLogin');

        $this->assertTrue($this->dispatcher->hasListeners('system.after_login'));
        $this->assertFalse($this->dispatcher->hasListeners('system.non_existent'));
    }

    /**
     * @covers EventDispatcher::getListeners
     */
    public function testGetListeners()
    {
        $this->assertCount(0, $this->dispatcher->getListeners());
        $this->dispatcher->addListener('system.before_login', 'onBeforeLogin');

        $this->assertCount(1, $this->dispatcher->getListeners());
        $this->assertCount(1, $this->dispatcher->getListeners('system.before_login'));
    }

    public function testCompleteCallingFunc()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('system.before_login', 'onBeforeLogin');
        $dispatcher->addListener('system.after_login', 'onAfterLogin');

        $event = new Event();
        $event->setParameters(array('param1' => 'value1', 'param2' => 'value2'));

        ob_start();
        $dispatcher->dispatch('system.before_login', new Event(array(1, 2, 3)));
        $obtained1 = ob_get_contents();
        ob_end_clean();

        ob_start();
        $dispatcher->dispatch('system.after_login', $event);
        $obtained2 = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('executed before login', $obtained1, 'Fatal!!! basic test is not working');
        $this->assertEquals(
            'executed after login, with params: value1, value2',
            $obtained2,
            'Fatal!!! basic test is not working'
        );
    }

    public function testCompleteCallingClassFunc()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('system.before_login', 'onBeforeLogin');
        $dispatcher->addListener('system.before_login', array(new SampleListener(), 'onAction'));
        $dispatcher->addListener('system.after_login', 'onAfterLogin');

        $event = new Event();
        $event->setParameters(array('param1' => 'value1', 'param2' => 'value2'));

        // result of onBeforeLogin callback func.
        $expected  = 'executed before login';
        // result of SampleListener::onAction() callback func.
        $expected .= 'exec: SampleListener::onAction(value1, value2)';

        ob_start();
        $dispatcher->dispatch('system.before_login', $event);
        $obtained1 = ob_get_contents();
        ob_end_clean();

        ob_start();
        $dispatcher->dispatch('system.after_login', new Event(array(1, 2, 3)));
        $obtained2 = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($expected, $obtained1);
        $this->assertEquals('executed after login, with params: 1, 2, 3', $obtained2);
    }

    /**
     * @covers addSubscriber
     */
    public function testSuscriber()
    {
        $this->dispatcher->addSubscriber(new Sample2Listener());
        $expected  = 'executed Sample2Listener::onBeforeLogin(1, 2, 3)';
        $expected .= 'executed Sample2Listener::onBeforeLogin2(1, 2, 3)';
        $expected .= 'executed Sample2Listener::onAfterLogin(1, 2, 3)';

        $this->expectOutputString($expected);
        $this->dispatcher->dispatch('system.before_login', new Event(array(1, 2, 3)));
        $this->dispatcher->dispatch('system.after_login', new Event(array(1, 2, 3)));
    }
}

