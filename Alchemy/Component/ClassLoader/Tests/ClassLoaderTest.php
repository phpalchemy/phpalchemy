<?php
define('HOME_PATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
require_once HOME_PATH . 'ClassLoader.php';

use Alchemy\Component\ClassLoader\ClassLoader;

/**
 * ClassLoader Unit Test
 */
class ClassLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * @covers ClassLoader::getInstance
     */
    public function testGetInstance()
    {
        $classLoader = ClassLoader::getInstance();
        $this->assertInstanceOf('Alchemy\Component\ClassLoader\ClassLoader', $classLoader);
    }

    /**
     * @covers ClassLoader::getIncludePaths
     */
    public function testGetIncludePaths()
    {
        $classLoader = ClassLoader::getInstance();
        $this->assertCount(0, $classLoader->getIncludePaths());

        $classLoader->register('classes', HOME_PATH . 'classes/');
        $this->assertCount(1, $classLoader->getIncludePaths());
    }

    /**
     * @covers ClassLoader::register
     */
    public function testRegister()
    {
        $classLoader = ClassLoader::getInstance();

        // registering with end dir. separator
        $classLoader->register('classes1', HOME_PATH . 'Tests/Fixtures/classes/');

        // registering without end dir. separator
        $classLoader->register('classes2', HOME_PATH . 'Tests/Fixtures/classes2');

        $this->assertCount(3, $classLoader->getIncludePaths());
        $this->assertTrue(class_exists('\Lib\Util\Net\Smtp'));
        $this->assertTrue(class_exists('\Bin\ConsoleApp'));

        $obj = new \Lib\Util\Net\Smtp();

        $this->assertInstanceOf('\Lib\Util\Net\Smtp', $obj);

        $obj = new \Bin\ConsoleApp();
        $this->assertInstanceOf('\Bin\ConsoleApp', $obj);

        return $classLoader;
    }

    /**
     * @covers ClassLoader::registerClass
     */
    public function testRegisterClass()
    {
        $classLoader = ClassLoader::getInstance();
        $classLoader->registerClass('MyClass', HOME_PATH . 'Tests/Fixtures/classes3/class.myclass.php');

        $myClass = new \MyClass();

        $this->assertTrue(class_exists('\MyClass'));
        $this->assertInstanceOf('\MyClass', $myClass);
    }

    /**
     * Testing when a class is registered with a relative path and is supposed that it is on standard classpath
     *
     * @covers ClassLoader::registerClass
     */
    public function testRegisterClassInClassPath()
    {
        set_include_path(HOME_PATH . 'Tests/Fixtures/classes4' . PATH_SEPARATOR . get_include_path());

        $classLoader = ClassLoader::getInstance();
        $classLoader->registerClass('SampleClass', 'sample/class.sampleclass.php');

        $this->assertTrue(class_exists('SampleClass'));
    }

    /**
     * @covers ClassLoader::unregister
     */
    public function testUnregister()
    {
        $classLoader = ClassLoader::getInstance();
        $classLoader->unregister();

        $this->assertFalse(class_exists('\Lib\Util\Net\Pop3'));
    }
}

