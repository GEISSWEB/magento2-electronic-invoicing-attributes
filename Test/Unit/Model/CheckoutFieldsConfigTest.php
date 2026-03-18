<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Test\Unit\Model;

use Geissweb\ElectronicInvoicingAttributes\Model\CheckoutFieldsConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutFieldsConfigTest extends TestCase
{
    private ScopeConfigInterface&MockObject $scopeConfig;
    private CheckoutFieldsConfig $subject;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->subject = new CheckoutFieldsConfig($this->scopeConfig);
    }

    public function testIsBuyerReferenceEnabledReturnsTrue(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_enabled',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(true);

        $this->assertTrue($this->subject->isBuyerReferenceEnabled());
    }

    public function testIsBuyerReferenceEnabledReturnsFalse(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_enabled',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(false);

        $this->assertFalse($this->subject->isBuyerReferenceEnabled());
    }

    public function testIsBuyerReferenceEnabledPassesStoreId(): void
    {
        $storeId = 5;

        $this->scopeConfig->method('isSetFlag')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_enabled',
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn(true);

        $this->assertTrue($this->subject->isBuyerReferenceEnabled($storeId));
    }

    public function testGetBuyerReferenceTooltipReturnsConfiguredText(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_tooltip',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('Custom tooltip text');

        $this->assertSame('Custom tooltip text', $this->subject->getBuyerReferenceTooltip());
    }

    public function testGetBuyerReferenceTooltipReturnsEmptyStringWhenNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_tooltip',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(null);

        $this->assertSame('', $this->subject->getBuyerReferenceTooltip());
    }

    public function testGetBuyerReferenceTooltipPassesStoreId(): void
    {
        $storeId = 3;

        $this->scopeConfig->method('getValue')
            ->with(
                'electronicinvoicing/checkout_fields/buyer_reference_tooltip',
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn('Store-specific tooltip');

        $this->assertSame('Store-specific tooltip', $this->subject->getBuyerReferenceTooltip($storeId));
    }

    public function testIsProjectReferenceEnabledReturnsTrue(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(
                'electronicinvoicing/checkout_fields/project_reference_enabled',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(true);

        $this->assertTrue($this->subject->isProjectReferenceEnabled());
    }

    public function testIsProjectReferenceEnabledReturnsFalse(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(
                'electronicinvoicing/checkout_fields/project_reference_enabled',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(false);

        $this->assertFalse($this->subject->isProjectReferenceEnabled());
    }

    public function testGetProjectReferenceTooltipReturnsConfiguredText(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(
                'electronicinvoicing/checkout_fields/project_reference_tooltip',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('Project tooltip');

        $this->assertSame('Project tooltip', $this->subject->getProjectReferenceTooltip());
    }

    public function testGetProjectReferenceTooltipReturnsEmptyStringWhenNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(
                'electronicinvoicing/checkout_fields/project_reference_tooltip',
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(null);

        $this->assertSame('', $this->subject->getProjectReferenceTooltip());
    }
}
