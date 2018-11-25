<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Common\Config\Config;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\FileProcessorFactory;
use Grav\Plugin\Newsletter\FileProcessorInterface;
use Grav\Plugin\Newsletter\ObjectCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileProcessorFactoryTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var FileProcessorFactory */
    private $fileProcessorFactory;

    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|ObjectCreator */
    private $objectCreatorMock;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->setMethods(['value'])
            ->disableOriginalConstructor()->getMock();
        $this->objectCreatorMock = $this->getMockBuilder(ObjectCreator::class)->disableOriginalConstructor()
            ->getMock();

        $this->container = new Container();
        $this->container['config'] = function() {
            return $this->configMock;
        };
        $this->container['objectCreator'] = function() {
            return $this->objectCreatorMock;
        };

        $this->fileProcessorFactory = $this->container->getFileProcessorFactory();
    }

    public function testCreateThrowsExceptionIfTypeNotDefinedInConfig()
    {
        $this->configMock->expects($this->once())->method('value')
            ->with('plugins.newsletter.subscribe.fileProcessors.undefined')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File processor "undefined" is not defined in config.');
        $this->fileProcessorFactory->create('undefined');
    }

    public function testCreateReturnsFileProcessorIfItsDefinedInConfig()
    {
        $className = \Grav\Plugin\Newsletter\DefinedFileProcessor::class;
        $fileProcessorMock = $this->getMockBuilder(FileProcessorInterface::class)->getMockForAbstractClass();
        $this->configMock->expects($this->once())->method('value')
            ->with('plugins.newsletter.subscribe.fileProcessors.defined')
            ->willReturn($className);
        $this->objectCreatorMock->expects($this->once())->method('create')
            ->with($className, [$this->container])->willReturn($fileProcessorMock);

        $result = $this->fileProcessorFactory->create('defined');
        $this->assertSame($fileProcessorMock, $result);
    }

}