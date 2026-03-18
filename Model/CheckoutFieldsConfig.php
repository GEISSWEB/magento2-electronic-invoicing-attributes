<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CheckoutFieldsConfig
{
    private const CONFIG_PATH_PREFIX = 'electronicinvoicing/checkout_fields/';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isBuyerReferenceEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_PREFIX . 'buyer_reference_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getBuyerReferenceTooltip(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'buyer_reference_tooltip',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isProjectReferenceEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_PREFIX . 'project_reference_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getProjectReferenceTooltip(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PREFIX . 'project_reference_tooltip',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
