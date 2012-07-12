<?php
use Alchemy\Annotation\Reader\Adapter\NotojReader;

include_once __DIR__ . '/Fixtures/Sample.php';
include_once __DIR__ . '/Fixtures/Base/Annotation/PermissionAnnotation.php';
include_once __DIR__ . '/Fixtures/Base/Annotation/RoleAnnotation.php';

/**
 * NotojReader Unit Test
 */
class NotojReaderBasicGenericTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NotojReader main test object
     */
    protected $dispatcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->reader = new NotojReader();
    }

    /**
     * @covers NotojReader::setCacheDir
     */
    public function testSetStrict()
    {
        // by default strict flag is true
        $this->assertTrue($this->reader->isStrict());

        // disabling
        $this->reader->setStrict(false);
        $this->assertFalse($this->reader->isStrict());

        // enabling
        $this->reader->setStrict(true);
        $this->assertTrue($this->reader->isStrict());

        // setting non boolean value, it should be evaluated/converted to his equivalent boolean
        $this->reader->setStrict(1);
        $this->assertTrue($this->reader->isStrict());

        // setting non boolean value, it should be evaluated/converted to his equivalent boolean
        $this->reader->setStrict(0);
        $this->assertFalse($this->reader->isStrict());

        // setting non boolean value, it should be evaluated/converted to his equivalent boolean
        $this->reader->setStrict('test string');
        $this->assertTrue($this->reader->isStrict());

        // setting non boolean value, it should be evaluated/converted to his equivalent boolean
        $this->reader->setStrict('');
        $this->assertFalse($this->reader->isStrict());
    }

    /**
     * @covers NotojReader::setCacheDir and NotojReader::getCacheDir
     */
    public function testSetGetCacheDir()
    {
        // empty by default
        $this->assertEmpty($this->reader->getCacheDir());

        // setting a cache dir path (this directory should be exists)
        $this->reader->setCacheDir(sys_get_temp_dir());
        $this->assertEquals(sys_get_temp_dir(), $this->reader->getCacheDir());
    }

    /**
     * @covers NotojReader::setCacheDir
     * @expectedException \RuntimeException
     */
    public function testSetCacheDir()
    {
        // setting a non existent cache dir path (a RuntimeException should be thrown)
        $this->reader->setCacheDir('/non/existent/path');
    }

    /**
     * @covers Annotations::setDefaultNamespace
     */
    public function testSetDefaultNamespace()
    {
        // it should be empty by default
        $this->assertEmpty($this->reader->getDefaultNamespace());

        // setting a namespace
        $this->reader->setDefaultNamespace('\Base\Annotation\\');
        $this->assertEquals('\Base\Annotation\\', $this->reader->getDefaultNamespace());
    }

    /**
     * @covers NotojReader::getClassAnnotations
     */
    public function testGetClassAnnotations()
    {
        $result = $this->reader->getClassAnnotations('Sample');
        $expected = array (
            'Foo' => array (
                0 => 'some other strings',
                'some_label' => 'something here'
            ),
            'Bar' => array(
                0 =>  array (
                    'some' => 'array here',
                    'arr' => array (
                        0 => 1,
                        1 => 2,
                        2 => 3,
                    )
                )
            )
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers NotojReader::getClassAnnotations
     */
    public function testGetMethodAnnotations()
    {
        $result = $this->reader->getMethodAnnotations('Sample', 'test');

        $expected = array (
            'Foo' => array (
                'some_label' => array (
                    'some' => 'array here',
                    'arr' => array (
                        0 => 1,
                        1 => 2,
                        2 => 3,
                    ),
                ),
            ),
            'Bar' => array (
                'test_var' => array (
                    0 => 'one',
                    1 => 'two',
                    2 => 'three',
                ),
            ),
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers NotojReader::getMethodAnnotationsObjects
     */
    public function testGetMethodAnnotationsObjects()
    {
        $this->reader->setDefaultNamespace('\Base\Annotation\\');
        $result = $this->reader->getMethodAnnotationsObjects('Sample', 'app');

        $this->assertInstanceOf('Base\Annotation\PermissionAnnotation', array_pop($result));
        $this->assertInstanceOf('Base\Annotation\RoleAnnotation', array_pop($result));
    }
}

