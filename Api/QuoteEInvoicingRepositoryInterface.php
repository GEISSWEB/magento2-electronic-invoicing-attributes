<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Api;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository interface for electronic invoicing quote extension data
 */
interface QuoteEInvoicingRepositoryInterface
{
    /**
     * Get by entity ID
     *
     * @param int $entityId
     * @return QuoteEInvoicingInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): QuoteEInvoicingInterface;

    /**
     * Get by quote ID
     *
     * @param int $quoteId
     * @return QuoteEInvoicingInterface|null
     */
    public function getByQuoteId(int $quoteId): ?QuoteEInvoicingInterface;

    /**
     * Save
     *
     * @param QuoteEInvoicingInterface $quoteEInvoicing
     * @return QuoteEInvoicingInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteEInvoicingInterface $quoteEInvoicing): QuoteEInvoicingInterface;

    /**
     * Delete
     *
     * @param QuoteEInvoicingInterface $quoteEInvoicing
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuoteEInvoicingInterface $quoteEInvoicing): bool;

    /**
     * Delete by quote ID
     *
     * @param int $quoteId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByQuoteId(int $quoteId): bool;
}
