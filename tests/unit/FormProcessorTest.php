<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Plugin\Form\Form;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\FormProcessor;
use Grav\Plugin\Newsletter\SubscribeHandlerFactory;
use Grav\Plugin\Newsletter\SubscribeHandlerInterface;
use Grav\Plugin\Newsletter\UnsubscribeHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormProcessorTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var FormProcessor */
    private $formProcessor;

    /** @var MockObject|SubscribeHandlerFactory */
    private $handlerFactoryMock;

    /** @var MockObject|Form */
    private $formMock;

    public function setUp()
    {
        $this->handlerFactoryMock = $this->getMockBuilder(SubscribeHandlerFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();

        $this->container = new Container();
        $this->container->extend('subscribeHandlerFactory', function() {
            return $this->handlerFactoryMock;
        });

        $this->formProcessor = $this->container->getFormProcessor();
    }

    public function testGetHandlersWillReturnDefaultHandlerIfHandlerNotSetInParams()
    {
        $handlerMock = $this->getSubscribeHandlerMock();
        $this->handlerFactoryMock->expects($this->once())->method('create')
            ->with('local', $this->formMock, [])->willReturn($handlerMock);
        $result = $this->formProcessor->getHandlers($this->formMock);
        $this->assertSame([$handlerMock], $result);
    }

    public function testGetHandlersWillReturnSpecificHandlerIfHandlerSetInParams()
    {
        $handlerMock = $this->getSubscribeHandlerMock();
        $this->handlerFactoryMock->expects($this->once())->method('create')
            ->with('local', $this->formMock, ['some_params'])->willReturn($handlerMock);
        $result = $this->formProcessor->getHandlers($this->formMock, ['handlers' => ['local' => ['some_params']]]);
        $this->assertSame([$handlerMock], $result);
    }

    public function testGetHandlersWillReturnMultipleHandlersIfHandlersSetInParams()
    {
        $localHandlerMock = $this->getSubscribeHandlerMock();
        $mailchimpHandlerMockLocal = $this->getSubscribeHandlerMock();
        $this->handlerFactoryMock->expects($this->exactly(2))->method('create')
            ->withConsecutive(
                ['local', $this->formMock, ['some_params']],
                ['mailchimp', $this->formMock, ['other_params']]
            )
            ->willReturnOnConsecutiveCalls($localHandlerMock, $mailchimpHandlerMockLocal);
        $result = $this->formProcessor->getHandlers($this->formMock, ['handlers' => [
            'local' => ['some_params'],
            'mailchimp' => ['other_params']
        ]]);
        $this->assertSame([$localHandlerMock, $mailchimpHandlerMockLocal], $result);
    }

    public function testProcessSubscribeWillThrowExceptionForEmptyHandlersArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty handlers array');
        $this->formProcessor->processSubscribe([]);
    }

    /**
     * @dataProvider getInvalidSubscribeHandlers
     */
    public function testProcessSubscribeWillThrowExceptionForInvalidHandlerObject($invalidHandler)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Each handler must be an instance of \Grav\Plugin\Newsletter\SubscribeHandlerInterface'
        );
        $this->formProcessor->processSubscribe([$invalidHandler]);
    }

    public function testProcessSubscribeWillRunEachHandler()
    {
        $handlerMock = $this->getSubscribeHandlerMock();
        $handlerMock->expects($this->once())->method('subscribe');
        $handlerMock2 = $this->getSubscribeHandlerMock();
        $handlerMock2->expects($this->once())->method('subscribe');
        $this->formProcessor->processSubscribe([$handlerMock, $handlerMock2]);
    }

    public function getInvalidSubscribeHandlers()
    {
        return [
            ['string'],
            [$this->getMockBuilder(\SomeClass::class)->getMock()]
        ];
    }

    public function testProcessUnsubscribeWillThrowExceptionForEmptyHandlersArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty handlers array');
        $this->formProcessor->processUnsubscribe([], ['data']);
    }

    /**
     * @dataProvider getInvalidSubscribeHandlers
     */
    public function testProcessUnubscribeWillThrowExceptionForInvalidHandlerObject($invalidHandler)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Each handler must be an instance of \Grav\Plugin\Newsletter\SubscribeHandlerInterface'
        );
        $this->formProcessor->processUnsubscribe([$invalidHandler], ['data']);
    }

    public function testProcessUnubscribeWillThrowExceptionForZeroUnsubscribeHandlerObjects()
    {
        $handlerMock = $this->getSubscribeHandlerMock();
        $handlerMock2 = $this->getSubscribeHandlerMock();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'At least one handler must be and instance of \Grav\Plugin\Newsletter\UnsubscribeHandlerInterface'
        );
        $this->formProcessor->processUnsubscribe([$handlerMock, $handlerMock2], ['data']);
    }

    public function testProcessUnsubscribeWillRunEachUnsubscribeHandler()
    {
        $subscribeHandlerMock = $this->getSubscribeHandlerMock();
        $unsubscribeHandlerMock = $this->getUnsubscribeHandlerMock();
        $unsubscribeHandlerMock->expects($this->once())->method('unsubscribe');
        $this->formProcessor->processUnsubscribe([$subscribeHandlerMock, $unsubscribeHandlerMock], ['data']);
    }

    public function testProcessUnsubscribeWillReturnFalseIfAnyHandlerReturnedFalse()
    {
        $handlerMock = $this->getUnsubscribeHandlerMock(true);
        $handlerMock2 = $this->getUnsubscribeHandlerMock(false);
        $result = $this->formProcessor->processUnsubscribe([$handlerMock, $handlerMock2], ['data']);
        $this->assertFalse($result);
    }

    public function testProcessUnsubscribeWillReturnTrueIfAllHandlersReturnedTrue()
    {
        $handlerMock = $this->getUnsubscribeHandlerMock(true);
        $handlerMock2 = $this->getUnsubscribeHandlerMock(true);
        $result = $this->formProcessor->processUnsubscribe([$handlerMock, $handlerMock2], ['data']);
        $this->assertTrue($result);
    }

    /**
     * @return SubscribeHandlerInterface|MockObject
     */
    private function getSubscribeHandlerMock(): MockObject
    {
        return $this->getMockBuilder(SubscribeHandlerInterface::class)->getMockForAbstractClass();
    }

    /**
     * @return UnsubscribeHandlerInterface|SubscribeHandlerInterface|MockObject
     */
    private function getUnsubscribeHandlerMock(bool $result = true): MockObject
    {
        $handlerMock = $this->getMockBuilder([SubscribeHandlerInterface::class, UnsubscribeHandlerInterface::class])
            ->getMock();
        $handlerMock->expects($this->once())->method('unsubscribe')->willReturn($result);
        return $handlerMock;
    }
}