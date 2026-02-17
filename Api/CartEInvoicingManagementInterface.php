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
 * Interface for managing e-invoicing data on customer cart
 *
 * @api
 */
interface CartEInvoicingManagementInterface
{
    /**
     * Get e-invoicing data for cart
     *
     * @param int $cartId
     * @return \Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface|null
     */
    public function get(int $cartId): ?Data\QuoteEInvoicingInterface;

    /**
     * Save e-invoicing data for cart
     *
     * @param int $cartId
     * @param string|null $buyerReference
     * @param string|null $projectReference
     * @return bool
     */
    public function save(int $cartId, ?string $buyerReference = null, ?string $projectReference = null): bool;
}
