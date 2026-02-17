<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\ViewModel\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Psr\Log\LoggerInterface;

class EInvoicingFields implements ArgumentInterface
{
    private CustomerSession $customerSession;

    private CustomerRepositoryInterface $customerRepository;

    private CheckoutSession $checkoutSession;

    private QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository;

    private LoggerInterface $logger;

    public function __construct(
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CheckoutSession $checkoutSession,
        QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteEInvoicingRepository = $quoteEInvoicingRepository;
        $this->logger = $logger;
    }

    public function getInitialBuyerReference(): string
    {
        $quoteId = (int)$this->checkoutSession->getQuoteId();
        if (!$quoteId) {
            return $this->getCustomerBuyerReference();
        }

        try {
            $quoteData = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);
            if ($quoteData && $quoteData->getBuyerReference()) {
                return $quoteData->getBuyerReference();
            }
        } catch (\Exception $e) {
            $this->logger->debug('Could not load quote e-invoicing data: ' . $e->getMessage());
        }

        return $this->getCustomerBuyerReference();
    }

    public function getInitialProjectReference(): string
    {
        $quoteId = (int)$this->checkoutSession->getQuoteId();
        if (!$quoteId) {
            return '';
        }

        try {
            $quoteData = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);
            if ($quoteData && $quoteData->getProjectReference()) {
                return $quoteData->getProjectReference();
            }
        } catch (\Exception $e) {
            $this->logger->debug('Could not load quote e-invoicing data: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * @return bool
     */
    public function customerHasBuyerReference(): bool
    {
        $buyerReference = $this->getCustomerBuyerReference();

        return $buyerReference !== '';
    }

    /**
     * @return string
     */
    private function getCustomerBuyerReference(): string
    {
        try {
            $customerId = $this->customerSession->getCustomerId();
            if (!$customerId) {
                return '';
            }

            $customer = $this->customerRepository->getById($customerId);
            $attribute = $customer->getCustomAttribute('buyer_reference');

            return (string)$attribute?->getValue();

        } catch (\Exception $e) {
            $this->logger->error(
                'Error loading customer buyer_reference attribute: ' . $e->getMessage()
            );

            return '';
        }
    }
}
