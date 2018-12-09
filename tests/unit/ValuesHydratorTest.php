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
    protected $valuesHydrator;

    /** @var Container */
    protected $container;

    /** @var MockObject|Form */
    protected $formMock;

    /** @var MockObject|Data */
    protected $dataMock;

    public function setUp()
    {
        $this->dataMock = $this->getMockBuilder(Data::class)->setMethods(['toArray'])
            ->disableOriginalConstructor()->getMock();
        $this->container = $this->getContainer();
        $this->valuesHydrator = $this->container->getValuesHydrator();
    }

    public function testGetValuesReturnsEmptyArrayIfFieldsAreEmpty()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => ['foo' => 'bar']]);
        $result = $this->valuesHydrator->getValues($this->getFormMock(), []);
        $this->assertSame([], $result);
    }

    public function testGetValuesPutsEmptyValueIfFieldIsNotPresentInForm()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => []]);
        $result = $this->valuesHydrator->getValues($this->getFormMock(), ['foo']);
        $this->assertSame(['foo' => ''], $result);
    }

    public function testGetValuesReturnsValuesFromFormIfFieldsMatches()
    {
        $this->dataMock->expects($this->once())->method('toArray')->willReturn(['data' => ['foo' => 'bar']]);
        $result = $this->valuesHydrator->getValues($this->getFormMock(), ['foo']);
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function testGetValuesOmitsValueIfItsNotDefinedInFieldsArray()
    {
        $this->dataMock->expects($this->once())->method('toArray')
            ->willReturn(['data' => ['foo' => 'bar', 'boo' => 'far']]);
        $result = $this->valuesHydrator->getValues($this->getFormMock(), ['foo']);
        $this->assertSame(['foo' => 'bar'], $result);
    }

    protected function getContainer(): Container
    {
        return new Container();
    }

    /**
     * @return Form|MockObject
     */
    protected function getFormMock(array $fields = []): MockObject
    {
        $formMock = $this->getMockBuilder(Form::class)->setMethods(['getValues', 'fields'])
            ->disableOriginalConstructor()->getMock();
        $formMock->expects($this->any())->method('fields')->willReturn($fields);
        $formMock->expects($this->once())->method('getValues')->willReturn($this->dataMock);
        return $formMock;
    }
}