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

        return $classLoader;
    }

    /**
     * @covers ClassLoader::getIncludePaths
     * @depends testGetInstance
     */
    public function testGetIncludePaths($classLoader)
    {
        $this->assertCount(0, $classLoader->getIncludePaths());

        $classLoader->register('classes', HOME_PATH . 'classes' . DIRECTORY_SEPARATOR);
        $this->assertCount(1, $classLoader->getIncludePaths());
    }

    /**
     * @covers ClassLoader::register
     * @depends testGetInstance
     */
    public function testRegister($classLoader)
    {
        // registering with end dir. separator
        $classLoader->register('classes', HOME_PATH . 'Tests/Fixtures/classes' . DIRECTORY_SEPARATOR);

        // registering without end dir. separator
        $classLoader->register('classes2', HOME_PATH . 'Tests/Fixtures/classes2');

        $obj = new \Lib\Util\Net\Smtp();
        $obj2 = new \Lib\Util\Net\Smtp();
        $obj3= new \Lib\Util\Net\Smtp();
        $obj4 = new \Lib\Util\Net\Smtp();
        $this->assertInstanceOf('\Lib\Util\Net\Smtp', $obj);

        $obj = new \Bin\ConsoleApp();
        $this->assertInstanceOf('\Bin\ConsoleApp', $obj);

        return $classLoader;
    }

    /**
     * @covers ClassLoader::unregister
     * @depends testRegister
     */
    public function testUnregister($classLoader)
    {
        $classLoader->unregister();

        $this->assertFalse(class_exists('\Lib\Util\Net\Pop3'));
    }
}

