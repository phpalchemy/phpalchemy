<?php
use Alchemy\Component\WebAssets\Asset;
use Alchemy\Component\WebAssets\Filter\CssMinFilter;

/**
 * Asset Class - Unit Test
 */
class AssetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Asset
     */
    protected $asset;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * @covers Asset::__constructor().
     */
    public function testConstructorWithCss()
    {
        $asset = new Asset(__DIR__ . '/fixtures/css/styles.css');

        return $asset;
    }

    /**
     * @covers Asset::setFilter
     * @depends testConstructorWithCss
     */
    public function testSetFilter(Asset $asset)
    {
        $asset->setFilter(new CssMinFilter());
        $this->assertTrue($asset->hasFilter());
    }

    /**
     * @covers Asset::getOutput
     * @todo   Implement testGetOutput().
     */
    public function testGetOutput()
    {
        // without filter
        $asset = new Asset(__DIR__ . '/fixtures/css/styles.css');
        $expected = file_get_contents(__DIR__ . '/fixtures/css/styles.css');
        $result = $asset->getOutput();
        $this->assertEquals($expected, $result);

        // with CssMin filter
        $asset = new Asset(__DIR__ . '/fixtures/css/styles.css', new CssMinFilter());
        $expected = file_get_contents(__DIR__ . '/fixtures/css/styles.min.css');
        $result = $asset->getOutput();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Asset::getFilename
     * @depends testConstructorWithCss
     */
    public function testGetFilename(Asset $asset)
    {
        $this->assertEquals('styles.css', $asset->getFilename());
    }
}
