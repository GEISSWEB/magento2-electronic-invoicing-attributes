<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Electronic Invoicing Order Extension Data Resource Model
 */
class OrderEInvoicing extends AbstractDb
{
    public const TABLE_NAME = 'geissweb_einvoicing_order';
    public const ID_FIELD_NAME = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }

    /**
     * Load by order ID
     *
     * @param \Geissweb\ElectronicInvoicingAttributes\Model\OrderEInvoicing $object
     * @param int $orderId
     * @return self
     */
    public function loadByOrderId(
        \Geissweb\ElectronicInvoicingAttributes\Model\OrderEInvoicing $object,
        int $orderId
    ): self {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('order_id = ?', $orderId);

        $data = $connection->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        return $this;
    }
}
