<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Model;

use Geissweb\ElectronicInvoicingAttributes\Api\CartEInvoicingManagementInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for managing e-invoicing data on customer cart
 */
class CartEInvoicingManagement implements CartEInvoicingManagementInterface
{
    /**
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository
     * @param QuoteEInvoicingFactory $quoteEInvoicingFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository,
        private readonly QuoteEInvoicingFactory $quoteEInvoicingFactory,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(int $cartId): ?QuoteEInvoicingInterface
    {
        try {
            $quote = $this->cartRepository->get($cartId);
            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId((int)$quote->getId());

            if ($quoteEInvoicing !== null) {
                return $quoteEInvoicing;
            }

            return $this->createPrefilledFromCustomer($quote);
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Failed to get e-invoicing data for cart',
                ['cart_id' => $cartId, 'exception' => $e->getMessage()]
            );

            return null;
        }
    }

    /**
     * @param CartInterface $quote
     * @return QuoteEInvoicingInterface|null
     */
    private function createPrefilledFromCustomer(CartInterface $quote): ?QuoteEInvoicingInterface
    {
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();

        if ($customerId === null) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById((int)$customerId);
            $attribute = $customer->getCustomAttribute('buyer_reference');

            if ($attribute === null || $attribute->getValue() === '' || $attribute->getValue() === null) {
                return null;
            }

            $prefilled = $this->quoteEInvoicingFactory->create();
            $prefilled->setBuyerReference((string)$attribute->getValue());

            return $prefilled;
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function save(int $cartId, ?string $buyerReference = null, ?string $projectReference = null): bool
    {
        if ($buyerReference !== null && mb_strlen($buyerReference) > 255) {
            throw new LocalizedException(__('Buyer reference must not exceed 255 characters.'));
        }

        if ($projectReference !== null && mb_strlen($projectReference) > 255) {
            throw new LocalizedException(__('Project reference must not exceed 255 characters.'));
        }

        try {
            $quote = $this->cartRepository->get($cartId);
            $quoteId = (int)$quote->getId();

            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);

            if ($quoteEInvoicing === null) {
                $quoteEInvoicing = $this->quoteEInvoicingFactory->create();
                $quoteEInvoicing->setQuoteId($quoteId);
            }

            $quoteEInvoicing->setBuyerReference($buyerReference);
            $quoteEInvoicing->setProjectReference($projectReference);

            $this->quoteEInvoicingRepository->save($quoteEInvoicing);

            return true;
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Failed to save e-invoicing data for cart',
                ['cart_id' => $cartId, 'exception' => $e->getMessage()]
            );

            return false;
        }
    }
}
