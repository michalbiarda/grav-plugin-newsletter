<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Common\Config\Config;
use Grav\Plugin\Form\Form;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\FormProcessor;
use Grav\Plugin\Newsletter\SubscribeHandlerFactory;
use Grav\Plugin\Newsletter\SubscribeHandlerInterface;
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

    public function testProcessWillRunDefaultHandlerIfHandlerNotSetInParams()
    {
        $handlerMock = $this->getHandlerMock();
        $this->handlerFactoryMock->expects($this->once())->method('create')
            ->with('local', $this->formMock, [])->willReturn($handlerMock);
        $this->formProcessor->process($this->formMock);
    }

    public function testProcessWillRunSpecificHandlerIfHandlerSetInParams()
    {
        $handlerMock = $this->getHandlerMock();
        $this->handlerFactoryMock->expects($this->once())->method('create')
            ->with('local', $this->formMock, ['some_params'])->willReturn($handlerMock);
        $this->formProcessor->process($this->formMock, ['handlers' => ['local' => ['some_params']]]);
    }

    public function testProcessWillRunMultipleHandlersIfHandlersSetInParams()
    {
        $localHandlerMock = $this->getHandlerMock();
        $mailchimpHandlerMockLocal = $this->getHandlerMock();
        $this->handlerFactoryMock->expects($this->exactly(2))->method('create')
            ->withConsecutive(
                ['local', $this->formMock, ['some_params']],
                ['mailchimp', $this->formMock, ['other_params']]
            )
            ->willReturnOnConsecutiveCalls($localHandlerMock, $mailchimpHandlerMockLocal);
        $this->formProcessor->process($this->formMock, ['handlers' => [
            'local' => ['some_params'],
            'mailchimp' => ['other_params']
        ]]);
    }

    /**
     * @return MockObject
     */
    private function getHandlerMock(): MockObject
    {
        $handlerMock = $this->getMockBuilder(SubscribeHandlerInterface::class)->getMockForAbstractClass();
        $handlerMock->expects($this->once())->method('run');
        return $handlerMock;
    }
}