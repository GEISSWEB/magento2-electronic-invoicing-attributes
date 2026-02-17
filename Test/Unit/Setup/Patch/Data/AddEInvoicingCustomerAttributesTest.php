<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Test\Unit\Setup\Patch\Data;

use Geissweb\ElectronicInvoicingAttributes\Setup\Patch\Data\AddEInvoicingCustomerAttributes;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddEInvoicingCustomerAttributesTest extends TestCase
{
    private AddEInvoicingCustomerAttributes $patch;

    private MockObject $moduleDataSetupMock;

    private MockObject $customerSetupFactoryMock;

    private MockObject $attributeSetFactoryMock;

    private MockObject $customerSetupMock;

    private MockObject $eavConfigMock;

    private MockObject $entityTypeMock;

    private MockObject $attributeSetMock;

    protected function setUp(): void
    {
        $this->moduleDataSetupMock = $this->createMock(ModuleDataSetupInterface::class);
        $this->customerSetupFactoryMock = $this->createMock(CustomerSetupFactory::class);
        $this->attributeSetFactoryMock = $this->createMock(AttributeSetFactory::class);
        $this->customerSetupMock = $this->createMock(CustomerSetup::class);
        $this->eavConfigMock = $this->createMock(EavConfig::class);
        $this->entityTypeMock = $this->createMock(Type::class);
        $this->attributeSetMock = $this->createMock(AttributeSet::class);

        $this->patch = new AddEInvoicingCustomerAttributes(
            $this->moduleDataSetupMock,
            $this->customerSetupFactoryMock,
            $this->attributeSetFactoryMock
        );
    }

    public function testGetDependenciesReturnsEmptyArray(): void
    {
        $this->assertEquals([], AddEInvoicingCustomerAttributes::getDependencies());
    }

    public function testGetAliasesReturnsEmptyArray(): void
    {
        $this->assertEquals([], $this->patch->getAliases());
    }

    public function testGetVersionReturnsCorrectVersion(): void
    {
        $this->assertEquals('1.0.0', AddEInvoicingCustomerAttributes::getVersion());
    }

    public function testApplyCreatesAttributes(): void
    {
        $connectionMock = $this->createMock(AdapterInterface::class);
        $connectionMock->expects($this->once())->method('startSetup');
        $connectionMock->expects($this->once())->method('endSetup');

        $this->moduleDataSetupMock->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->customerSetupFactoryMock->expects($this->once())
            ->method('create')
            ->with(['setup' => $this->moduleDataSetupMock])
            ->willReturn($this->customerSetupMock);

        $this->customerSetupMock->expects($this->any())
            ->method('getEavConfig')
            ->willReturn($this->eavConfigMock);

        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($this->entityTypeMock);

        $this->entityTypeMock->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->willReturn(1);

        $this->attributeSetFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeSetMock);

        $this->attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->with(1)
            ->willReturn(1);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->exactly(2))
            ->method('addData')
            ->willReturnSelf();
        $attributeMock->expects($this->exactly(2))
            ->method('save');

        $addAttributeCalls = [];
        $this->customerSetupMock->expects($this->exactly(2))
            ->method('addAttribute')
            ->willReturnCallback(function ($entityType, $attributeCode, $config) use (&$addAttributeCalls) {
                $addAttributeCalls[] = [
                    'entityType' => $entityType,
                    'attributeCode' => $attributeCode,
                    'config' => $config,
                ];
            });

        $this->eavConfigMock->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $result = $this->patch->apply();

        $this->assertSame($this->patch, $result);

        $this->assertCount(2, $addAttributeCalls);

        $this->assertEquals(Customer::ENTITY, $addAttributeCalls[0]['entityType']);
        $this->assertEquals('buyer_reference', $addAttributeCalls[0]['attributeCode']);
        $this->assertEquals('Buyer Reference', $addAttributeCalls[0]['config']['label']);
        $this->assertEquals('varchar', $addAttributeCalls[0]['config']['type']);
        $this->assertEquals('text', $addAttributeCalls[0]['config']['input']);
        $this->assertTrue($addAttributeCalls[0]['config']['visible_on_front']);

        $this->assertEquals(Customer::ENTITY, $addAttributeCalls[1]['entityType']);
        $this->assertEquals('buyer_registration', $addAttributeCalls[1]['attributeCode']);
        $this->assertEquals('Company Registration', $addAttributeCalls[1]['config']['label']);
        $this->assertEquals('varchar', $addAttributeCalls[1]['config']['type']);
        $this->assertEquals('text', $addAttributeCalls[1]['config']['input']);
        $this->assertTrue($addAttributeCalls[1]['config']['visible_on_front']);
    }
}
