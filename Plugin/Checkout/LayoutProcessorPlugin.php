<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

class LayoutProcessorPlugin
{
    /**
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param LayoutProcessor $subject
     * @param array<string, mixed> $jsLayout
     * @return array<string, mixed>
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout): array
    {
        $einvoicingComponent = &$jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['afterMethods']['children']['einvoicing-fields'] ?? null;

        if ($einvoicingComponent !== null) {
            $einvoicingComponent['config']['customerHasBuyerReference'] = $this->customerHasBuyerReference();
        }

        return $jsLayout;
    }

    /**
     * @return bool
     */
    private function customerHasBuyerReference(): bool
    {
        try {
            $customerId = $this->customerSession->getCustomerId();

            if (!$customerId) {
                return false;
            }

            $customer = $this->customerRepository->getById($customerId);
            $attribute = $customer->getCustomAttribute('buyer_reference');

            return $attribute !== null && !empty($attribute->getValue());
        } catch (\Exception $e) {
            $this->logger->error('Error checking customer buyer_reference: ' . $e->getMessage());

            return false;
        }
    }
}
