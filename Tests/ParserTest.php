<?php
use Alchemy\Component\UI\Parser;

/**
 * Alchemy\Component\UI\Parser - Unit Test File
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Alchemy\Component\UI\Parser::__construct()
     */
    public function testConstructor()
    {
        $parser = new Parser(__DIR__ . '/Fixtures/schema/mini-html.schema.uigen');

        $this->assertCount(2, $parser->getGlobals());
        $this->assertCount(2, $parser->getIterators());
        $this->assertCount(3, $parser->getBlocks());

        return $parser;
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     */
    public function testGenerateMini(Parser $parser)
    {
        $data = array(
            'id' => 'my_text_id',
            'name' => 'my_text',
            'attributes' => array(
                'size' => 25,
                'emptyText' => 'write your text here!'
            )
        );

        $expected = '<input type="text" id="my_text_id" name="my_text" size="25" emptyText="write your text here!"/>';
        $result = $parser->generate('textbox', $data);
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Error: Undefined template block: "undefined_tpl_block"
     * @depends testConstructor
     */
    public function testGenerateUndefinedTemplateBlockException($parser)
    {
        $result = $parser->generate('undefined_tpl_block', array());
    }

    /**
     * @covers Alchemy\Component\UI\Parser::setDefaultBlock
     * @depends testConstructor
     */
    public function testSetDefaultBlock($parser)
    {
        $parser->setDefaultBlock('_default');

        $data = array(
            'id'    => 'my_checkbox_id',
            'name'  => 'my_checkbox',
            'xtype' => 'checkbox',
            'attributes' => array(
                'value' => 'some_choise'
            )
        );
        $expected = '<input type="checkbox" id="my_checkbox_id" name="my_checkbox" value="some_choise"/>';

        $result = $parser->generate('checkbox', $data);
        $this->assertEquals($expected, $result);
    }
}
