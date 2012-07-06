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
        $this->assertCount(3, $parser->getIterators());
        $this->assertCount(4, $parser->getBlocks());

        return $parser;
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     */
    public function testGetIterators(Parser $parser)
    {
        $result = $parser->getIterators();
        $this->assertCount(3, $result);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     */
    public function testGenerateSingle(Parser $parser)
    {
        $data = array(
            'id' => 'my_text_id',
            'name' => 'my_text',
            'attributes' => array(
                'size' => 25,
                'emptyText' => 'write your text here!'
            )
        );

        $expected = array();
        $expected['template'] = '<input type="text" id="my_text_id" name="my_text" ' .
                                'size="25" emptyText="write your text here!"/>';

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
        $expected = array();
        $expected['template'] = '<input type="checkbox" id="my_checkbox_id" name="my_checkbox" value="some_choise"/>';

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
            'name'  => 'my_select',
            'xtype' => 'slect',
            'attributes' => array(
                'value' => 'some_choise'
            ),
            'options' => array(
                array('name' => 'one',   'value' => '1'),
                array('name' => 'two',   'value' => '2'),
                array('name' => 'three', 'value' => '3')
            )
        );

        $expected = array();
        $expected['javascript'] = "alert('my_select_id');";
        $expected['template'] = <<<EOT
<select type="slect" id="my_select_id" value="some_choise">
  <option name="one">1</option>
  <option name="two">2</option>
  <option name="three">3</option>
</select>
EOT;
        $result = $parser->generate('select', $data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::setDefaultBlock
     * @depends testConstructor
     */
    public function testGenerateForm($parser)
    {
        $parser->setDefaultBlock('_default');

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
        $formItems[] = $result['template'];

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
        $formItems[] = $result['template'];

        $data = array(
            'id'    => 'my_select_id',
            'name'  => 'my_select',
            'xtype' => 'slect',
            'attributes' => array(
                'value' => 'some_choise'
            ),
            'options' => array(
                array('name' => 'one',   'value' => '1'),
                array('name' => 'two',   'value' => '2'),
                array('name' => 'three', 'value' => '3')
            )
        );

        $result = $parser->generate('select', $data);
        $formItems[] = $result['template'];
        $data = array(
            'attributes' => array(
                'id'    => 'my_form_id',
                'name'  => 'my_form',
                'action' => 'process.php',
                'method' => 'POST'
            ),
            'items' => $formItems
        );

        $expected = <<<EOT
<form id="my_form_id" name="my_form" action="process.php" method="POST">
<input type="text" id="my_text_id" name="my_text" size="25" emptyText="write your text here!"/>
<input type="checkbox" id="my_checkbox_id" name="my_checkbox" value="some_choise"/>
<select type="slect" id="my_select_id" value="some_choise">
  <option name="one">1</option>
  <option name="two">2</option>
  <option name="three">3</option>
</select>
</form>
EOT;
        $result = $parser->generate('form', $data);

        $this->assertEquals($expected, $result['template']);
    }
}

