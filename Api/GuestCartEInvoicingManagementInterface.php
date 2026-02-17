<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Api;

/**
 * Interface for managing e-invoicing data on guest cart
 *
 * @api
 */
interface GuestCartEInvoicingManagementInterface
{
    /**
     * Get e-invoicing data for guest cart
     *
     * @param string $cartId
     * @return \Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface|null
     */
    public function get(string $cartId): ?Data\QuoteEInvoicingInterface;

    /**
     * Save e-invoicing data for guest cart
     *
     * @param string $cartId
     * @param string|null $buyerReference
     * @param string|null $projectReference
     * @return bool
     */
    public function save(string $cartId, ?string $buyerReference = null, ?string $projectReference = null): bool;
}
