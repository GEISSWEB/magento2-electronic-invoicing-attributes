<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\ResourceModel\QuoteEInvoicing as ResourceModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository for electronic invoicing quote extension data
 */
class QuoteEInvoicingRepository implements QuoteEInvoicingRepositoryInterface
{
    public function __construct(
        private readonly ResourceModel $resourceModel,
        private readonly QuoteEInvoicingFactory $quoteEInvoicingFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): QuoteEInvoicingInterface
    {
        $quoteEInvoicing = $this->quoteEInvoicingFactory->create();
        $this->resourceModel->load($quoteEInvoicing, $entityId);

        if (!$quoteEInvoicing->getEntityId()) {
            throw new NoSuchEntityException(
                __('Electronic invoicing data with ID "%1" does not exist.', $entityId)
            );
        }

        return $quoteEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function getByQuoteId(int $quoteId): ?QuoteEInvoicingInterface
    {
        $quoteEInvoicing = $this->quoteEInvoicingFactory->create();
        $this->resourceModel->loadByQuoteId($quoteEInvoicing, $quoteId);

        if (!$quoteEInvoicing->getEntityId()) {
            return null;
        }

        return $quoteEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function save(QuoteEInvoicingInterface $quoteEInvoicing): QuoteEInvoicingInterface
    {
        try {
            $this->resourceModel->save($quoteEInvoicing);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save electronic invoicing data: %1', $exception->getMessage()),
                $exception
            );
        }

        return $quoteEInvoicing;
    }

    /**
     * @inheritDoc
     */
    public function delete(QuoteEInvoicingInterface $quoteEInvoicing): bool
    {
        try {
            $this->resourceModel->delete($quoteEInvoicing);
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
    public function deleteByQuoteId(int $quoteId): bool
    {
        $quoteEInvoicing = $this->getByQuoteId($quoteId);

        if ($quoteEInvoicing === null) {
            return true;
        }

        return $this->delete($quoteEInvoicing);
    }
}
