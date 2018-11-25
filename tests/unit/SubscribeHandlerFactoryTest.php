<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Common\Config\Config;
use Grav\Plugin\Form\Form;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\ObjectCreator;
use Grav\Plugin\Newsletter\SubscribeHandlerFactory;
use Grav\Plugin\Newsletter\SubscribeHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscribeHandlerFactoryTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var SubscribeHandlerFactory */
    private $handlerFactory;

    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|Form */
    private $formMock;

    /** @var MockObject|ObjectCreator */
    private $objectCreatorMock;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->setMethods(['value'])
            ->disableOriginalConstructor()->getMock();
        $this->objectCreatorMock = $this->getMockBuilder(ObjectCreator::class)->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();

        $this->container = new Container();
        $this->container['config'] = function() {
            return $this->configMock;
        };
        $this->container['objectCreator'] = function() {
            return $this->objectCreatorMock;
        };

        $this->handlerFactory = $this->container->getSubscribeHandlerFactory();
    }

    public function testCreateThrowsExceptionIfHandlerNotDefinedInConfig()
    {
        $this->configMock->expects($this->once())->method('value')
            ->with('plugins.newsletter.subscribe.handlers.undefined')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Handler "undefined" is not defined in config.');
        $this->handlerFactory->create('undefined', $this->formMock);
    }

    public function testCreateReturnsHandlerIfItsDefinedInConfig()
    {
        $className = \Grav\Plugin\Newsletter\DefinedSubscribeHandler::class;
        $handlerMock = $this->getMockBuilder(SubscribeHandlerInterface::class)->getMockForAbstractClass();
        $this->configMock->expects($this->once())->method('value')
            ->with('plugins.newsletter.subscribe.handlers.defined')
            ->willReturn($className);
        $this->objectCreatorMock->expects($this->once())->method('create')
            ->with($className, [$this->container, $this->formMock, ['params']])->willReturn($handlerMock);

        $result = $this->handlerFactory->create('defined', $this->formMock, ['params']);
        $this->assertSame($handlerMock, $result);
    }

}