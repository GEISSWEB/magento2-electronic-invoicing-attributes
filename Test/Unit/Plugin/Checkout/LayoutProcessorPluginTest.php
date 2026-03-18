<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Test\Unit\Plugin\Checkout;

use Geissweb\ElectronicInvoicingAttributes\Model\CheckoutFieldsConfig;
use Geissweb\ElectronicInvoicingAttributes\Plugin\Checkout\LayoutProcessorPlugin;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutProcessorPluginTest extends TestCase
{
    private CheckoutFieldsConfig&MockObject $checkoutFieldsConfig;
    private LayoutProcessor&MockObject $layoutProcessor;
    private LayoutProcessorPlugin $subject;

    protected function setUp(): void
    {
        $this->checkoutFieldsConfig = $this->createMock(CheckoutFieldsConfig::class);
        $this->layoutProcessor = $this->createMock(LayoutProcessor::class);
        $this->subject = new LayoutProcessorPlugin($this->checkoutFieldsConfig);
    }

    public function testAfterProcessInjectsBuyerReferenceEnabled(): void
    {
        $this->checkoutFieldsConfig->method('isBuyerReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getBuyerReferenceTooltip')->willReturn('Buyer tooltip');
        $this->checkoutFieldsConfig->method('isProjectReferenceEnabled')->willReturn(false);
        $this->checkoutFieldsConfig->method('getProjectReferenceTooltip')->willReturn('Project tooltip');

        $result = $this->subject->afterProcess($this->layoutProcessor, $this->buildJsLayout());
        $config = $this->getEInvoicingConfig($result);

        $this->assertTrue($config['isBuyerReferenceEnabled']);
    }

    public function testAfterProcessInjectsBuyerReferenceTooltip(): void
    {
        $this->checkoutFieldsConfig->method('isBuyerReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getBuyerReferenceTooltip')->willReturn('Enter Leitweg-ID');
        $this->checkoutFieldsConfig->method('isProjectReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getProjectReferenceTooltip')->willReturn('');

        $result = $this->subject->afterProcess($this->layoutProcessor, $this->buildJsLayout());
        $config = $this->getEInvoicingConfig($result);

        $this->assertSame('Enter Leitweg-ID', $config['buyerReferenceTooltip']);
    }

    public function testAfterProcessInjectsProjectReferenceEnabled(): void
    {
        $this->checkoutFieldsConfig->method('isBuyerReferenceEnabled')->willReturn(false);
        $this->checkoutFieldsConfig->method('getBuyerReferenceTooltip')->willReturn('');
        $this->checkoutFieldsConfig->method('isProjectReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getProjectReferenceTooltip')->willReturn('');

        $result = $this->subject->afterProcess($this->layoutProcessor, $this->buildJsLayout());
        $config = $this->getEInvoicingConfig($result);

        $this->assertTrue($config['isProjectReferenceEnabled']);
    }

    public function testAfterProcessInjectsProjectReferenceTooltip(): void
    {
        $this->checkoutFieldsConfig->method('isBuyerReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getBuyerReferenceTooltip')->willReturn('');
        $this->checkoutFieldsConfig->method('isProjectReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getProjectReferenceTooltip')->willReturn('Contract ref');

        $result = $this->subject->afterProcess($this->layoutProcessor, $this->buildJsLayout());
        $config = $this->getEInvoicingConfig($result);

        $this->assertSame('Contract ref', $config['projectReferenceTooltip']);
    }

    public function testAfterProcessInjectsAllFourConfigValues(): void
    {
        $this->checkoutFieldsConfig->method('isBuyerReferenceEnabled')->willReturn(true);
        $this->checkoutFieldsConfig->method('getBuyerReferenceTooltip')->willReturn('Buyer tip');
        $this->checkoutFieldsConfig->method('isProjectReferenceEnabled')->willReturn(false);
        $this->checkoutFieldsConfig->method('getProjectReferenceTooltip')->willReturn('Project tip');

        $result = $this->subject->afterProcess($this->layoutProcessor, $this->buildJsLayout());
        $config = $this->getEInvoicingConfig($result);

        $this->assertTrue($config['isBuyerReferenceEnabled']);
        $this->assertSame('Buyer tip', $config['buyerReferenceTooltip']);
        $this->assertFalse($config['isProjectReferenceEnabled']);
        $this->assertSame('Project tip', $config['projectReferenceTooltip']);
    }

    public function testAfterProcessReturnsUnmodifiedLayoutWhenComponentMissing(): void
    {
        $jsLayout = ['components' => ['checkout' => ['children' => []]]];
        $result = $this->subject->afterProcess($this->layoutProcessor, $jsLayout);

        $this->assertSame($jsLayout, $result);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJsLayout(): array
    {
        return [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                'billing-step' => [
                                    'children' => [
                                        'payment' => [
                                            'children' => [
                                                'afterMethods' => [
                                                    'children' => [
                                                        'einvoicing-fields' => [
                                                            'component' => 'Geissweb_ElectronicInvoicingAttributes/js/view/checkout/einvoicing-fields',
                                                            'config' => [],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $jsLayout
     * @return array<string, mixed>
     */
    private function getEInvoicingConfig(array $jsLayout): array
    {
        return $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['einvoicing-fields']['config'];
    }
}
