<?php
use Alchemy\Component\WebAssets\Bundle;
use Alchemy\Component\WebAssets\Asset;

use Alchemy\Component\WebAssets\Filter\JsMinFilter;

/**
 * Bundle Unit Test
 */
class BundleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Bundle
     */
    protected $bundle;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->bundle = new Bundle;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Bundle::setOutputFilename
     * @todo   Implement testSetOutputFilename().
     */
    public function testSetOutputFilename()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Bundle::setFilter
     * @todo   Implement testSetFilter().
     */
    public function testSetFilter()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Bundle::getOutput
     * @todo   Implement testGetOutput().
     */
    public function testGetOutput()
    {
        $bundle = new Bundle(
            array(__DIR__.'/fixtures/js/before.js', New JsMinFilter),
            __DIR__.'/fixtures/js/issue74.js'
        );

        $bundle->setCacheDir(__DIR_.'/cache');
        echo $bundle->getOutput();
    }
}
