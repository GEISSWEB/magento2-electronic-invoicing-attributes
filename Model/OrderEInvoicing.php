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
use Geissweb\ElectronicInvoicingAttributes\Model\ResourceModel\OrderEInvoicing as ResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Electronic Invoicing Order Extension Data Model
 */
class OrderEInvoicing extends AbstractModel implements OrderEInvoicingInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): ?int
    {
        $entityId = $this->getData(self::ENTITY_ID);

        return $entityId !== null ? (int)$entityId : null;
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): ?int
    {
        $orderId = $this->getData(self::ORDER_ID);

        return $orderId !== null ? (int)$orderId : null;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(int $orderId): self
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getBuyerReference(): ?string
    {
        return $this->getData(self::BUYER_REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setBuyerReference(?string $buyerReference): self
    {
        return $this->setData(self::BUYER_REFERENCE, $buyerReference);
    }

    /**
     * @inheritDoc
     */
    public function getProjectReference(): ?string
    {
        return $this->getData(self::PROJECT_REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setProjectReference(?string $projectReference): self
    {
        return $this->setData(self::PROJECT_REFERENCE, $projectReference);
    }
}
