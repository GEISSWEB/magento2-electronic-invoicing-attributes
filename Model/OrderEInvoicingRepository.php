<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\OrderEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\OrderEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\ResourceModel\OrderEInvoicing as ResourceModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository for electronic invoicing order extension data
 */
class OrderEInvoicingRepository implements OrderEInvoicingRepositoryInterface
{
    public function __construct(
        private readonly ResourceModel $resourceModel,
        private readonly OrderEInvoicingFactory $orderEInvoicingFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): OrderEInvoicingInterface
    {
        $orderEInvoicing = $this->orderEInvoicingFactory->create();
        $this->resourceModel->load($orderEInvoicing, $entityId);

        if (!$orderEInvoicing->getEntityId()) {
            throw new NoSuchEntityException(
                __('Electronic invoicing data with ID "%1" does not exist.', $entityId)
            );
        }

        return $orderEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId(int $orderId): ?OrderEInvoicingInterface
    {
        $orderEInvoicing = $this->orderEInvoicingFactory->create();
        $this->resourceModel->loadByOrderId($orderEInvoicing, $orderId);

        if (!$orderEInvoicing->getEntityId()) {
            return null;
        }

        return $orderEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function save(OrderEInvoicingInterface $orderEInvoicing): OrderEInvoicingInterface
    {
        try {
            $this->resourceModel->save($orderEInvoicing);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save electronic invoicing data: %1', $exception->getMessage()),
                $exception
            );
        }

        return $orderEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function delete(OrderEInvoicingInterface $orderEInvoicing): bool
    {
        try {
            $this->resourceModel->delete($orderEInvoicing);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete electronic invoicing data: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteByOrderId(int $orderId): bool
    {
        $orderEInvoicing = $this->getByOrderId($orderId);

        if ($orderEInvoicing === null) {
            return true;
        }

        return $this->delete($orderEInvoicing);
    }
}
