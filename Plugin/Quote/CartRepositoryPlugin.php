<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Plugin\Quote;

use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\QuoteEInvoicingFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to handle e-invoicing extension attributes on quote
 */
class CartRepositoryPlugin
{
    public function __construct(
        private readonly QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository,
        private readonly QuoteEInvoicingFactory $quoteEInvoicingFactory,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Load extension attributes after getting a quote
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        return $this->loadExtensionAttributes($quote);
    }

    /**
     * Load extension attributes after getting active quote
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGetActiveForCustomer(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        return $this->loadExtensionAttributes($quote);
    }

    /**
     * Load extension attributes for quote list
     *
     * @param CartRepositoryInterface $subject
     * @param CartSearchResultsInterface $searchResults
     * @return CartSearchResultsInterface
     */
    public function afterGetList(
        CartRepositoryInterface $subject,
        CartSearchResultsInterface $searchResults
    ): CartSearchResultsInterface {
        foreach ($searchResults->getItems() as $quote) {
            $this->loadExtensionAttributes($quote);
        }

        return $searchResults;
    }

    /**
     * Save extension attributes after saving a quote
     *
     * @param CartRepositoryInterface $subject
     * @param mixed $result
     * @param CartInterface $quote
     * @return mixed
     */
    public function afterSave(CartRepositoryInterface $subject, mixed $result, CartInterface $quote): mixed
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $result;
        }

        $quoteId = (int)$quote->getId();
        if ($quoteId === 0) {
            return $result;
        }

        $buyerReference = $extensionAttributes->getEinvoicingBuyerReference();
        $projectReference = $extensionAttributes->getEinvoicingProjectReference();

        if ($buyerReference === null && $projectReference === null) {
            return $result;
        }

        try {
            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);

            if ($quoteEInvoicing === null) {
                $quoteEInvoicing = $this->quoteEInvoicingFactory->create();
                $quoteEInvoicing->setQuoteId($quoteId);
            }

            $quoteEInvoicing->setBuyerReference($buyerReference);
            $quoteEInvoicing->setProjectReference($projectReference);

            $this->quoteEInvoicingRepository->save($quoteEInvoicing);
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Failed to save e-invoicing quote data',
                ['quote_id' => $quoteId, 'exception' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Load extension attributes for a quote
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    private function loadExtensionAttributes(CartInterface $quote): CartInterface
    {
        $extensionAttributes = $quote->getExtensionAttributes();

        $quoteId = (int)$quote->getId();
        if ($quoteId === 0) {
            return $quote;
        }

        try {
            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);

            if ($quoteEInvoicing !== null) {
                $extensionAttributes->setEinvoicingBuyerReference($quoteEInvoicing->getBuyerReference());
                $extensionAttributes->setEinvoicingProjectReference($quoteEInvoicing->getProjectReference());
            } else {
                $this->prefillFromCustomer($quote, $extensionAttributes);
            }
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Failed to load e-invoicing quote data',
                ['quote_id' => $quoteId, 'exception' => $e->getMessage()]
            );
        }

        return $quote;
    }

    /**
     * Prefill extension attributes from customer data
     *
     * @param CartInterface $quote
     * @param mixed $extensionAttributes
     * @return void
     */
    private function prefillFromCustomer(CartInterface $quote, mixed $extensionAttributes): void
    {
        $customerId = $quote->getCustomerId();
        if ($customerId === null) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById((int)$customerId);
            $buyerReference = $customer->getCustomAttribute('buyer_reference');

            if ($buyerReference !== null) {
                $extensionAttributes->setEinvoicingBuyerReference((string)$buyerReference->getValue());
            }
        } catch (LocalizedException $e) {
            // Customer not found or attribute not set - ignore
        }
    }
}
