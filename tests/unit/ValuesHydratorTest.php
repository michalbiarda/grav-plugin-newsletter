<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Common\Data\Data;
use Grav\Plugin\Form\Form;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\ValuesHydrator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValuesHydratorTest extends TestCase
{
    /** @var ValuesHydrator */
    private $valuesHydrator;

    /** @var Container */
    private $container;

    /** @var MockObject|Form */
    private $formMock;

    /** @var MockObject|Data */
    private $dataMock;

    public function setUp()
    {
        $this->dataMock = $this->getMockBuilder(Data::class)->setMethods(['toArray'])
            ->disableOriginalConstructor()->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)->setMethods(['getValues'])
            ->disableOriginalConstructor()->getMock();
        $this->formMock->expects($this->once())->method('getValues')->willReturn($this->dataMock);

        $this->container = new Container();
        $this->valuesHydrator = $this->container->getValuesHydrator();
    }

    public function testGetValuesReturnsEmptyArrayIfFieldsAreEmpty()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => ['foo' => 'bar']]);
        $result = $this->valuesHydrator->getValues($this->formMock, []);
        $this->assertSame([], $result);
    }

    public function testGetValuesPutsEmptyValueIfFieldIsNotPresentInForm()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => []]);
        $result = $this->valuesHydrator->getValues($this->formMock, ['foo']);
        $this->assertSame(['foo' => ''], $result);
    }

    public function testGetValuesReturnsValuesFromFormIfFieldsMatches()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => ['foo' => 'bar']]);
        $result = $this->valuesHydrator->getValues($this->formMock, ['foo']);
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function testGetValuesOmitsValueIfItsNotDefinedInFieldsArray()
    {
        $this->dataMock->expects($this->once())->method('toArray')
            ->willReturn(['data' => ['foo' => 'bar', 'boo' => 'far']]);
        $result = $this->valuesHydrator->getValues($this->formMock, ['foo']);
        $this->assertSame(['foo' => 'bar'], $result);
    }
}