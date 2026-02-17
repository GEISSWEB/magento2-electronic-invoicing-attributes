<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model;

use Geissweb\ElectronicInvoicingAttributes\Api\CartEInvoicingManagementInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\GuestCartEInvoicingManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Service for managing e-invoicing data on guest cart
 */
class GuestCartEInvoicingManagement implements GuestCartEInvoicingManagementInterface
{
    public function __construct(
        private readonly CartEInvoicingManagementInterface $cartEInvoicingManagement,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(string $cartId): ?QuoteEInvoicingInterface
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteId = (int)$quoteIdMask->getQuoteId();

        return $this->cartEInvoicingManagement->get($quoteId);
    }

    /**
     * @inheritDoc
     */
    public function save(string $cartId, ?string $buyerReference = null, ?string $projectReference = null): bool
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteId = (int)$quoteIdMask->getQuoteId();

        return $this->cartEInvoicingManagement->save($quoteId, $buyerReference, $projectReference);
    }
}
