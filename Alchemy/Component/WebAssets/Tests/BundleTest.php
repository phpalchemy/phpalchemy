<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
    public function __destruct()
    {
        foreach(glob(__DIR__.'/cache/*.*') as $file){
            unlink($file);
        }
        @unlink(__DIR__.'/cache/.webassets.cacheinf');
    }

    /**
     * @covers Bundle::getOutput
     * @todo   Implement testGetOutput().
     */
    public function testGetOutput()
    {
        $bundle = new Bundle(
            array('before.js', New JsMinFilter),
            'issue74.js'
        );

        $bundle->setLocateDir(array(
            __DIR__.'/fixtures/js2/',
            __DIR__.'/fixtures/js/',
        ));
        $bundle->setCacheDir(__DIR__.'/cache');
        $bundle->setOutputDir(__DIR__.'/cache');
        $bundle->handle();

        $expected = file_get_contents(__DIR__.'/fixtures/js/result1.js');
        $result = file_get_contents($bundle->getPath());

        $this->assertEquals($expected, $result);
        $this->assertFalse($bundle->isFromCache());
    }

    /**
     * @covers Bundle::getOutput
     * @todo   Implement testGetOutput().
     */
    public function testGetOutputFromCache()
    {
        $bundle = new Bundle(
            array('before.js', New JsMinFilter),
            'issue74.js'
        );

        $bundle->setLocateDir(array(
            __DIR__.'/fixtures/js2/',
            __DIR__.'/fixtures/js/',
        ));
        $bundle->setCacheDir(__DIR__.'/cache');
        $bundle->setOutputDir(__DIR__.'/cache');
        $bundle->handle();

        $expected = file_get_contents(__DIR__.'/fixtures/js/result1.js');
        $result = file_get_contents($bundle->getPath());

        $this->assertEquals($expected, $result);
        $this->assertTrue($bundle->isFromCache());
    }

    /**
     * @covers Bundle::handle
     */
    public function testHandleSingleFileWithoutFilter()
    {
        $bundle = new Bundle('before.js');

        $bundle->setLocateDir(array(
            __DIR__.'/fixtures/js2/',
            __DIR__.'/fixtures/js/',
        ));
        $bundle->setCacheDir(__DIR__.'/cache');
        $bundle->setOutputDir(__DIR__.'/cache');
        $bundle->handle();

        $this->assertEquals(__DIR__.'/fixtures/js2/before.js', $bundle->getPath());
    }

}
