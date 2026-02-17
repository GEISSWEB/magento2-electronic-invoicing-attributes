<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Block\Adminhtml\Order\View;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\OrderEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\OrderEInvoicingRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;

class EInvoicing extends Template
{
    private ?OrderEInvoicingInterface $eInvoicingData = null;

    private bool $dataLoaded = false;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param OrderEInvoicingRepositoryInterface $orderEInvoicingRepository
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly Registry $registry,
        private readonly OrderEInvoicingRepositoryInterface $orderEInvoicingRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getBuyerReference(): ?string
    {
        $data = $this->getEInvoicingData();

        return $data?->getBuyerReference();
    }

    /**
     * @return string|null
     */
    public function getProjectReference(): ?string
    {
        $data = $this->getEInvoicingData();

        return $data?->getProjectReference();
    }

    /**
     * @return bool
     */
    public function hasEInvoicingData(): bool
    {
        $data = $this->getEInvoicingData();

        if ($data === null) {
            return false;
        }

        return $data->getBuyerReference() !== null || $data->getProjectReference() !== null;
    }

    /**
     * @return OrderEInvoicingInterface|null
     */
    private function getEInvoicingData(): ?OrderEInvoicingInterface
    {
        if ($this->dataLoaded) {
            return $this->eInvoicingData;
        }

        $this->dataLoaded = true;
        $order = $this->getOrder();

        if ($order === null) {
            return null;
        }

        $orderId = (int)$order->getEntityId();

        if ($orderId === 0) {
            return null;
        }

        $this->eInvoicingData = $this->orderEInvoicingRepository->getByOrderId($orderId);

        return $this->eInvoicingData;
    }

    /**
     * @return OrderInterface|null
     */
    private function getOrder(): ?OrderInterface
    {
        $order = $this->registry->registry('current_order');

        if ($order !== null) {
            return $order;
        }

        $invoice = $this->registry->registry('current_invoice');

        if ($invoice !== null) {
            return $invoice->getOrder();
        }

        $creditmemo = $this->registry->registry('current_creditmemo');

        return $creditmemo?->getOrder();

    }
}
