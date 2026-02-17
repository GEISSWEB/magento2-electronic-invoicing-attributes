<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Api;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\OrderEInvoicingInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository interface for electronic invoicing order extension data
 */
interface OrderEInvoicingRepositoryInterface
{
    /**
     * Get by entity ID
     *
     * @param int $entityId
     * @return OrderEInvoicingInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): OrderEInvoicingInterface;

    /**
     * Get by order ID
     *
     * @param int $orderId
     * @return OrderEInvoicingInterface|null
     */
    public function getByOrderId(int $orderId): ?OrderEInvoicingInterface;

    /**
     * Save
     *
     * @param OrderEInvoicingInterface $orderEInvoicing
     * @return OrderEInvoicingInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderEInvoicingInterface $orderEInvoicing): OrderEInvoicingInterface;

    /**
     * Delete
     *
     * @param OrderEInvoicingInterface $orderEInvoicing
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OrderEInvoicingInterface $orderEInvoicing): bool;

    /**
     * Delete by order ID
     *
     * @param int $orderId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByOrderId(int $orderId): bool;
}
