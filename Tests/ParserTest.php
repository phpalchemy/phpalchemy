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
        $parser = new Parser(__DIR__ . '/Fixtures/schema/mini-html.genscript');
        //$parser = new Parser(__DIR__ . '/../bundle/twitter-bootstrap/components.genscript');
//        var_dump($parser->getBlocks());
        $this->assertCount(1, $parser->getGlobals());
        $this->assertCount(3, $parser->getBlocks());

        return $parser;
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     */
    public function testGenerateSingle(Parser $parser)
    {
        $data = array(
            'id' => 'id1',
            'value' => 'value1',
            'attributes' => array(
                'size' => 15,
                'placeholder' => 'placeholder text!'
            )
        );

        $expected = array();
        $expected['html'] = '<input type="text" id="id1" value="value1" size="15" placeholder="placeholder text!" />';
        $expected['javascript'] = "alert('textfield with id: id1');";

        $result = $parser->generate('textbox', $data);
        
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Error: Undefined template block: "undefined_block"
     * @depends testConstructor
     */
    public function testGenerateUndefinedTemplateBlockException($parser)
    {
        $result = $parser->generate('undefined_block', array());
    }

    /**
     * @covers Alchemy\Component\UI\Parser::setDefaultBlock
     * @depends testConstructor
     */
    public function testSetDefaultBlock($parser)
    {
        $parser->setDefaultBlock('_default');

        $data = array(
            'id'    => 'checkbox_id',
            'value'  => 'checkbox_value',
            'xtype' => 'checkbox',
            'attributes' => array(
                'value' => 'some_choise'
            )
        );
        $expected = array();
        $expected['html'] = '<input type="checkbox" id="checkbox_id" value="checkbox_value" value="some_choise" />';
        $expected['javascript'] = "alert('checkbox with id: checkbox_id');";

        $result = $parser->generate('checkbox', $data);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::setDefaultBlock
     * @depends testConstructor
     */
    public function testGenerate($parser)
    {
        $data = array(
            'id'    => 'my_select_id',
            'value'  => 'my_select',
            'xtype' => 'slect',
            'attributes' => array(
                'value' => 'some_choise',
                'class' => 'some_class'
            ),
            'items' => array(
                array('value' => 'one',   'label' => '1'),
                array('value' => 'two',   'label' => '2'),
                array('value' => 'three', 'label' => '3')
            )
        );

        $expected = array();
        $expected['javascript'] = "alert('select with id: my_select_id');";
        $expected['html'] = '<select id="my_select_id" value="some_choise" class="some_class"><option name="one">1</option><option name="two">2</option><option name="three">3</option></select>';
        
        $result = $parser->generate('select', $data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     * @expectedException Exception
     * @expectedExceptionMessage Alchemy\Component\UI\Parse:: Undefined variable: value
     */
    public function testGenerateWithException(Parser $parser)
    {
        // this array intentionally has not attribute: 'name'
        $data = array(
            'id' => 'my_text_id',
            'attributes' => array(
                'name'=>'my_value',
                'size' => 25,
                'emptyText' => 'empty text'
            )
        );

        $result = $parser->generate('textbox', $data);
    }

    
}

