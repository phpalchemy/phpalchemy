<?php
/**
 * DiContainer Test
 */
require_once __DIR__ . '/../DiContainer.php';

class Service
{
}

class DiContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testWithString()
    {
        $diContainer = new DiContainer();
        $diContainer['param'] = 'value';

        $this->assertEquals('value', $diContainer['param']);
    }

    public function testWithClosure()
    {
        $diContainer = new DiContainer();
        $diContainer['service'] = function () {
            return new Service();
        };

        $this->assertInstanceOf('Service', $diContainer['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $diContainer = new DiContainer();
        $diContainer['service'] = function () {
            return new Service();
        };

        $serviceOne = $diContainer['service'];
        $this->assertInstanceOf('Service', $serviceOne);

        $serviceTwo = $diContainer['service'];
        $this->assertInstanceOf('Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $diContainer = new DiContainer();
        $diContainer['service'] = function () {
            return new Service();
        };
        $diContainer['container'] = function ($container) {
            return $container;
        };

        $this->assertNotSame($diContainer, $diContainer['service']);
        $this->assertSame($diContainer, $diContainer['container']);
    }

    public function testIsset()
    {
        $diContainer = new DiContainer();
        $diContainer['param'] = 'value';
        $diContainer['service'] = function () {
            return new Service();
        };

        $diContainer['null'] = null;

        $this->assertTrue(isset($diContainer['param']));
        $this->assertTrue(isset($diContainer['service']));
        $this->assertTrue(isset($diContainer['null']));
        $this->assertFalse(isset($diContainer['non_existent']));
    }

    public function testConstructorInjection ()
    {
        $params = array("param" => "value");
        $diContainer = new DiContainer($params);

        $this->assertSame($params['param'], $diContainer['param']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $diContainer = new DiContainer();
        echo $diContainer['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $diContainer = new DiContainer();
        $diContainer['foo'] = null;
        $this->assertNull($diContainer['foo']);
    }

    public function testUnset()
    {
        $diContainer = new DiContainer();
        $diContainer['param'] = 'value';
        $diContainer['service'] = function () {
            return new Service();
        };

        unset($diContainer['param'], $diContainer['service']);
        $this->assertFalse(isset($diContainer['param']));
        $this->assertFalse(isset($diContainer['service']));
    }

    public function testShare()
    {
        $diContainer = new DiContainer();
        $diContainer['shared_service'] = $diContainer->share(function () {
            return new Service();
        });

        $serviceOne = $diContainer['shared_service'];
        $this->assertInstanceOf('Service', $serviceOne);

        $serviceTwo = $diContainer['shared_service'];
        $this->assertInstanceOf('Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    public function testProtect()
    {
        $diContainer = new DiContainer();
        $callback = function () {
            return 'foo';
        };
        $diContainer['protected'] = $diContainer->protect($callback);

        $this->assertSame($callback, $diContainer['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $diContainer = new DiContainer();
        $diContainer['global_function'] = 'strlen';
        $this->assertSame('strlen', $diContainer['global_function']);
    }

    public function testRaw()
    {
        $diContainer = new DiContainer();
        $diContainer['service'] = $definition = function () {
            return 'foo';
        };
        $this->assertSame($definition, $diContainer->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $diContainer = new DiContainer();
        $diContainer['foo'] = null;
        $this->assertNull($diContainer->raw('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testRawValidatesKeyIsPresent()
    {
        $diContainer = new DiContainer();
        $diContainer->raw('foo');
    }

    public function testExtend()
    {
        $diContainer = new DiContainer();
        $diContainer['shared_service'] = $diContainer->share(function () {
            return new Service();
        });

        $value = 12345;

        $diContainer->extend('shared_service', function($sharedService) use ($value) {
            $sharedService->value = $value;
            return $sharedService;
        });

        $serviceOne = $diContainer['shared_service'];
        $this->assertInstanceOf('Service', $serviceOne);
        $this->assertEquals($value, $serviceOne->value);

        $serviceTwo = $diContainer['shared_service'];
        $this->assertInstanceOf('Service', $serviceTwo);
        $this->assertEquals($value, $serviceTwo->value);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testExtendValidatesKeyIsPresent()
    {
        $diContainer = new DiContainer();
        $diContainer->extend('foo', function () {
        });
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" does not contain an object definition.
     */
    public function testExtendValidatesKeyYieldsObjectDefinition()
    {
        $diContainer = new DiContainer();
        $diContainer['foo'] = 123;
        $diContainer->extend('foo', function () {
        });
    }

    public function testKeys()
    {
        $diContainer = new DiContainer();
        $diContainer['foo'] = 123;
        $diContainer['bar'] = 123;

        $this->assertEquals(array('foo', 'bar'), $diContainer->keys());
    }
}

