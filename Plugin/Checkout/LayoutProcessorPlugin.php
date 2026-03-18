<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Plugin\Checkout;

use Geissweb\ElectronicInvoicingAttributes\Model\CheckoutFieldsConfig;
use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPlugin
{
    /**
     * @param CheckoutFieldsConfig $checkoutFieldsConfig
     */
    public function __construct(
        private readonly CheckoutFieldsConfig $checkoutFieldsConfig
    ) {
    }

    /**
     * @param LayoutProcessor $subject
     * @param array<string, mixed> $jsLayout
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout): array
    {
        if (!isset($jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['einvoicing-fields'])) {
            return $jsLayout;
        }

        $config = &$jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['einvoicing-fields']['config'];

        $config['isBuyerReferenceEnabled'] = $this->checkoutFieldsConfig->isBuyerReferenceEnabled();
        $config['buyerReferenceTooltip'] = $this->checkoutFieldsConfig->getBuyerReferenceTooltip();
        $config['isProjectReferenceEnabled'] = $this->checkoutFieldsConfig->isProjectReferenceEnabled();
        $config['projectReferenceTooltip'] = $this->checkoutFieldsConfig->getProjectReferenceTooltip();

        return $jsLayout;
    }
}
