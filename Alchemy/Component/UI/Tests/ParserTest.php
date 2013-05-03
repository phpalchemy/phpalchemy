<?php
use Alchemy\Component\UI\Parser;

/*include_once 'bootstrap.php';
$parser = new Parser(__DIR__ . '/Fixtures/schema/mini-html.genscript');
print_r($parser->getBlocks()); die;*/



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
                'value' => 'some_choise'
            ),
            'items' => array(
                array('value' => 'one',   'label' => '1'),
                array('value' => 'two',   'label' => '2'),
                array('value' => 'three', 'label' => '3')
            )
        );

        $expected = array();
        $expected['javascript'] = "alert('my_select_id');";
        $expected['html'] = <<<EOT
<select id="my_select_id" value="some_choise">
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
            'id' => 'id1',
            'value' => 'value1',
            'attributes' => array(
                'size' => 25,
                'emptyText' => 'write your text here!'
            )
        );

        $expected = '<input type="text" id="my_text_id" name="my_text" size="25" emptyText="write your text here!"/>';
        $result = $parser->generate('textbox', $data);
        $formItems[] = $result['html'];

        $data = array(
            'id'    => 'checkbox_id',
            'value'  => 'checkbox_value',
            'xtype' => 'checkbox',
            'attributes' => array(
                'value' => 'some_choise'
            )
        );
        $expected = '<input type="checkbox" id="my_checkbox_id" name="my_checkbox" value="some_choise"/>';

        $result = $parser->generate('checkbox', $data);
        $formItems[] = $result['html'];

        $data = array(
            'id'    => 'my_select_id',
            'value'  => 'my_select',
            'attributes' => array(
                'value' => 'some_choise'
            ),
            'items' => array(
                array('value' => 'one',   'label' => '1'),
                array('value' => 'two',   'label' => '2'),
                array('value' => 'three', 'label' => '3')
            )
        );

        $result = $parser->generate('select', $data);
        $formItems[] = $result['html'];

        $data = array(
            'attributes' => array(
                'id'    => 'my_form_id',
                'name'  => 'my_form',
                'action' => 'process.php',
                'method' => 'POST'
            ),
            'items' => $formItems
        );

        $expected = array();
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
        var_dump($result);

        $this->assertEquals($expected, $result['html']);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     * @expectedException RuntimeException
     */
    public function testGenerate1(Parser $parser)
    {
        $expected = array(
            'html' => '<input type="text" id="my_text_id" name="my_text" size="25" emptyText="empty text"/>'
        );

        // this array intentionally has not attribute: 'name'
        $data = array(
            'id' => 'my_text_id',
            'attributes' => array(
                'size' => 25,
                'emptyText' => 'empty text'
            )
        );

        //So, when trying the textbox generation an exception (RuntimeException) will trown
        $result = $parser->generate('textbox', $data);
    }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructor
     */
    public function testGenerate2(Parser $parser)
    {
        $expected = array(
            'html' => '<input type="text" id="my_text_id" name="" size="25" emptyText="empty text"/>'
        );

        // this array intentionally has not attribute: 'name'
        $data = array(
            'id' => 'my_text_id',
            'attributes' => array(
                'size' => 25,
                'emptyText' => 'empty text'
            )
        );

        // disabling strict variables.
        $parser->setStrictVariables(false);

        //So, when trying the textbox generation an exception (RuntimeException) will trown
        $result = $parser->generate('textbox', $data);
    }
}

