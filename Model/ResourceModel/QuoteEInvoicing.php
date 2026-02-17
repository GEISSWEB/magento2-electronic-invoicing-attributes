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
 * Electronic Invoicing Quote Extension Data Resource Model
 */
class QuoteEInvoicing extends AbstractDb
{
    public const TABLE_NAME = 'geissweb_einvoicing_quote';
    public const ID_FIELD_NAME = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }

    /**
     * Load by quote ID
     *
     * @param \Geissweb\ElectronicInvoicingAttributes\Model\QuoteEInvoicing $object
     * @param int $quoteId
     * @return self
     */
    public function loadByQuoteId(
        \Geissweb\ElectronicInvoicingAttributes\Model\QuoteEInvoicing $object,
        int $quoteId
    ): self {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('quote_id = ?', $quoteId);

        $data = $connection->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        return $this;
    }
}
