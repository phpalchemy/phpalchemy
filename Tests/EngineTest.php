<?php

use Alchemy\Component\UI\Engine;
use Alchemy\Component\UI\ReaderFactory;
use Alchemy\Component\UI\Parser;

/**
 * Alchemy\Component\UI\Engine - Unit Test File
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/UI
 */
class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @covers Alchemy\Component\UI\Engine::__construct()
     */
    public function testConstructorWithHtml()
    {
        $engine = new Engine(new ReaderFactory(), new Parser());

        $engine->setTargetBundle('html');
        $engine->setMetaFile(__DIR__ . '/Fixtures/meta-wui/form1.xml');
        $engine->build();

        //$engine->build('html', __DIR__ . '/Fixtures/meta-wui/form1.xml');

        print_r($engine->getWidgetsCollection());

        return $engine;
    }

    // public function testConstructorWithHtml()
    // {
    //     $engine = new Engine(
    //         'html',
    //         ReaderFactory::loadReader(__DIR__ . '/Fixtures/meta-wui/form1.xml'),
    //         new Parser()
    //     );

    //     $engine->build();
    //     print_r($engine->getWidgetsCollection());

    //     return $engine;
    // }

    /**
     * @covers Alchemy\Component\UI\Parser::generate
     * @depends testConstructorWithHtml
     */
    public function testSingle(Engine $engine)
    {

    }
}

