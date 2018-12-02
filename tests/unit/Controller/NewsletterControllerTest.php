<?php

namespace Grav\Plugin\Newsletter\Test\Unit\Controller;

use Grav\Common\Config\Config;
use Grav\Common\Grav;
use Grav\Common\Uri;
use Grav\Plugin\Form\Form;
use Grav\Plugin\FormPlugin;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\Controller\NewsletterController;
use Grav\Plugin\Newsletter\FormProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewsletterControllerTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var NewsletterController */
    private $controller;

    /** @var Grav|MockObject */
    private $gravMock;

    /** @var Uri|MockObject */
    private $uriMock;

    /** @var FormPlugin|MockObject */
    private $formPluginMock;

    /** @var FormProcessor|MockObject */
    private $formProcessorMock;

    /** @var Config|MockObject */
    private $configMock;

    public function setUp()
    {
        $this->container = new Container();
        $this->gravMock = $this->getMockBuilder(Grav::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->uriMock = $this->getMockBuilder(Uri::class)->disableOriginalConstructor()->getMock();
        $this->formPluginMock = $this->getMockBuilder(FormPlugin::class)->disableOriginalConstructor()
            ->setMethods(['getForm'])->getMock();
        $this->formProcessorMock = $this->getMockBuilder(FormProcessor::class)->disableOriginalConstructor()
            ->getMock();
        $this->container['grav'] = function() {
            return $this->gravMock;
        };
        $this->container['config'] = function() {
            return $this->configMock;
        };
        $this->container['uri'] = function() {
            return $this->uriMock;
        };
        $this->container['formPlugin'] = function() {
            return $this->formPluginMock;
        };
        $this->container['formProcessor'] = function() {
            return $this->formProcessorMock;
        };
        $this->controller = new NewsletterController($this->container);
    }

    public function testUnsubscribeActionWillFireOnPageNotFoundEventIfNameParamDoesntExist()
    {
        $this->uriMock->expects($this->once())->method('query')->with('name')
            ->willReturn(null);
        $this->gravMock->expects($this->once())->method('fireEvent')->with('onPageNotFound');
        $this->controller->unsubscribeAction();
    }

    public function testUnsubscribeActionWillFireOnPageNotFoundEventIfHashParamDoesntExist()
    {
        $this->uriMock->expects($this->at(0))->method('query')->with('name')
            ->willReturn('form_name');
        $this->uriMock->expects($this->at(1))->method('query')->with('hash')
            ->willReturn(null);
        $this->gravMock->expects($this->once())->method('fireEvent')->with('onPageNotFound');
        $this->controller->unsubscribeAction();
    }

    public function testUnsubscribeActionWillFireOnPageNotFoundEventIfFormDoesntExist()
    {
        $this->uriMock->expects($this->at(0))->method('query')->with('name')
            ->willReturn('form_name');
        $this->uriMock->expects($this->at(1))->method('query')->with('hash')
            ->willReturn('s0m3h45h');
        $this->formPluginMock->expects($this->once())->method('getForm')->with(['name' => 'form_name'])
            ->willReturn(null);
        $this->gravMock->expects($this->once())->method('fireEvent')->with('onPageNotFound');
        $this->controller->unsubscribeAction();
    }

    /**
     * @dataProvider getNotNewsletterFormProcesses
     */
    public function testUnsubscribeActionWillFireOnPageNotFoundEventIfFormIsNotANewsletterForm($notNewsletterFormProcess)
    {
        $this->uriMock->expects($this->at(0))->method('query')->with('name')
            ->willReturn('form_name');
        $this->uriMock->expects($this->at(1))->method('query')->with('hash')
            ->willReturn('s0m3h45h');
        $formMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formProcessorMock->expects($this->once())->method('getFormProcesses')->with($formMock)
            ->willReturn($notNewsletterFormProcess);
        $this->formPluginMock->expects($this->once())->method('getForm')->with(['name' => 'form_name'])
            ->willReturn($formMock);
        $this->gravMock->expects($this->once())->method('fireEvent')->with('onPageNotFound');
        $this->controller->unsubscribeAction();
    }

    public function getNotNewsletterFormProcesses()
    {
        return [
            [[]],
            [[['something' => []]]],
            [[['something' => ['some_data']]]]
        ];
    }

    /**
     * @dataProvider getNewsletterFormProcesses
     */
    public function testUnsubscribeActionWillProcessFormIfFormIsANewsletterForm($newsletterFormProcess)
    {
        $this->uriMock->expects($this->at(0))->method('query')->with('name')
            ->willReturn('form_name');
        $this->uriMock->expects($this->at(1))->method('query')->with('hash')
            ->willReturn('s0m3h45h');
        $formMock = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formProcessorMock->expects($this->once())->method('getFormProcesses')->with($formMock)
            ->willReturn($newsletterFormProcess);
        $this->formPluginMock->expects($this->once())->method('getForm')->with(['name' => 'form_name'])
            ->willReturn($formMock);
        $this->formProcessorMock->expects($this->once())->method('getHandlers')
            ->with($formMock, ['foo'])->willReturn(['handlers']);
        $this->formProcessorMock->expects($this->once())->method('processUnsubscribe')
            ->with(['handlers'], ['hash' => 's0m3h45h']);
        $this->controller->unsubscribeAction();
    }

    public function getNewsletterFormProcesses()
    {
        return [
            [[['subscribe' => ['foo']]]],
            [[['something' => []], ['subscribe' => ['foo']]]]
        ];
    }
}