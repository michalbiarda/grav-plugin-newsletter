<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\ParamsProcessor;
use PHPUnit\Framework\TestCase;

class ParamsProcessorTest extends TestCase
{
    /** @var ParamsProcessor */
    private $paramsProcessor;

    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->paramsProcessor = $this->container->getParamsProcessor();
    }

    public function testGetStringsArrayParamValueReturnsDefaultValueIfKeyDoesntExistInParams()
    {
        $result = $this->paramsProcessor->getStringsArrayParamValue('key', ['default'], []);
        $this->assertSame(['default'], $result);
    }

    /**
     * @dataProvider getNotArraysOfStrings
     */
    public function testGetStringsArrayParamValueThrowsExceptionIfValueFromParamsIsNotArrayOfStrings($invalidValue)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"key" must be an array of strings.');
        $this->paramsProcessor->getStringsArrayParamValue('key', ['default'], ['key' => $invalidValue]);
    }

    public function testGetStringsArrayParamValueReturnsValueFromParamsIfItExists()
    {
        $result = $this->paramsProcessor->getStringsArrayParamValue('key', ['default'], ['key' => ['foo', 'bar']]);
        $this->assertSame(['foo', 'bar'], $result);
    }

    public function testGetStringParamValueReturnsDefaultValueIfKeyDoesntExistInParams()
    {
        $result = $this->paramsProcessor->getStringParamValue('key', 'default', []);
        $this->assertSame('default', $result);
    }

    /**
     * @dataProvider getNotStrings
     */
    public function testGetStringParamValueThrowsExceptionIfValueFromParamsIsNotAString($invalidValue)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"key" must be a string.');
        $this->paramsProcessor->getStringParamValue('key', 'default', ['key' => $invalidValue]);
    }

    public function testGetStringParamValueReturnsValueFromParamsIfItExists()
    {
        $result = $this->paramsProcessor->getStringParamValue('key', 'default', ['key' => 'value']);
        $this->assertSame('value', $result);
    }

    public function getNotArraysOfStrings()
    {
        return [
            ['not array'],
            [[0, 1]],
            [['foo' => ['bar']]]
        ];
    }

    public function getNotStrings()
    {
        return [
            [['array']],
            [123]
        ];
    }
}