<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Plugin\Quote;

use Geissweb\ElectronicInvoicingAttributes\Api\OrderEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\OrderEInvoicingFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to copy e-invoicing data from quote to order during order placement
 */
class QuoteManagementPlugin
{
    public function __construct(
        private readonly QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository,
        private readonly OrderEInvoicingRepositoryInterface $orderEInvoicingRepository,
        private readonly OrderEInvoicingFactory $orderEInvoicingFactory,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Copy e-invoicing data from quote to order after the quote is submitted
     *
     * @param QuoteManagement $subject
     * @param OrderInterface|null $result
     * @param Quote $quote
     * @return OrderInterface|null
     */
    public function afterSubmit(QuoteManagement $subject, ?OrderInterface $result, Quote $quote): ?OrderInterface
    {
        if ($result === null) {
            return null;
        }

        $orderId = (int)$result->getEntityId();

        try {
            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId((int)$quote->getId());

            $buyerReference = null;
            $projectReference = null;

            if ($quoteEInvoicing !== null) {
                $buyerReference = $quoteEInvoicing->getBuyerReference();
                $projectReference = $quoteEInvoicing->getProjectReference();
            }

            if (empty($buyerReference)) {
                $buyerReference = $this->getCustomerBuyerReference((int)$quote->getCustomerId());
            }

            if ($buyerReference === null && $projectReference === null) {
                return $result;
            }

            $orderEInvoicing = $this->orderEInvoicingFactory->create();
            $orderEInvoicing->setOrderId($orderId);
            $orderEInvoicing->setBuyerReference($buyerReference);
            $orderEInvoicing->setProjectReference($projectReference);

            $this->orderEInvoicingRepository->save($orderEInvoicing);
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Failed to copy e-invoicing data from quote to order',
                ['quote_id' => $quote->getId(), 'order_id' => $orderId, 'exception' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Get buyer_reference from customer account
     *
     * @param int $customerId
     * @return string|null
     */
    private function getCustomerBuyerReference(int $customerId): ?string
    {
        if ($customerId === 0) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            $attribute = $customer->getCustomAttribute('buyer_reference');

            if ($attribute === null) {
                return null;
            }

            $value = $attribute->getValue();

            return !empty($value) ? (string)$value : null;
        } catch (NoSuchEntityException) {
            return null;
        }
    }
}
