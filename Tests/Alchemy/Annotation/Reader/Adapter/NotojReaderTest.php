<?php
use Alchemy\Annotation\Reader\Adapter\NotojReader;

include_once __DIR__ . '/Fixtures/Test.php';
include_once __DIR__ . '/Fixtures/Base/Annotation/PermissionAnnotation.php';
include_once __DIR__ . '/Fixtures/Base/Annotation/RoleAnnotation.php';

/**
 * NotojReader Unit Test
 */
class NotojReaderTest extends PHPUnit_Framework_TestCase
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
        $this->reader->setDefaultNamespace('\Base\Annotation');
    }

    /**
     * @covers NotojReader::getAnnotations
     */
    public function testClassGetAnnotations()
    {
        $this->reader->setTarget('Test');
        $result = $this->reader->getAnnotations();

        $this->assertCount(1, $result);
        $this->assertInstanceOf('Base\Annotation\RoleAnnotation', array_pop($result));
    }

    /**
     * @covers NotojReader::getAnnotation
     */
    public function testClassGetAnnotation()
    {
        $this->reader->setTarget('Test');
        $this->assertInstanceOf('Base\Annotation\RoleAnnotation', $this->reader->getAnnotation('Role'));
        $this->assertEquals(null, $this->reader->getAnnotation('Permission'));
    }

    /**
     * @covers NotojReader::getAnnotations
     */
    public function testGetAnnotations()
    {
        $this->reader->setTarget('Test', 'app');
        $result = $this->reader->getAnnotations();

        $this->assertCount(2, $result);

        $this->assertInstanceOf('Base\Annotation\PermissionAnnotation', array_pop($result));
        $this->assertInstanceOf('Base\Annotation\RoleAnnotation', array_pop($result));
    }

    /**
     * @covers NotojReader::getAnnotation
     */
    public function testGetAnnotation()
    {
        $this->reader->setTarget('Test', 'app');

        $this->assertInstanceOf('Base\Annotation\PermissionAnnotation', $this->reader->getAnnotation('Permission'));
        $this->assertInstanceOf('Base\Annotation\RoleAnnotation', $this->reader->getAnnotation('Role'));
    }
}

