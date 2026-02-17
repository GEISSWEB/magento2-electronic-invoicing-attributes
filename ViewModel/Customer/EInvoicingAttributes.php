<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\ViewModel\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class EInvoicingAttributes implements ArgumentInterface
{
    private CustomerSession $customerSession;

    private CustomerRepositoryInterface $customerRepository;

    private LoggerInterface $logger;

    public function __construct(
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    public function getBuyerReference(): string
    {
        return $this->getCustomerAttributeValue('buyer_reference');
    }

    public function getBuyerRegistration(): string
    {
        return $this->getCustomerAttributeValue('buyer_registration');
    }

    private function getCustomerAttributeValue(string $attributeCode): string
    {
        try {
            $customerId = $this->customerSession->getCustomerId();
            if (!$customerId) {
                return '';
            }

            $customer = $this->customerRepository->getById($customerId);
            $attribute = $customer->getCustomAttribute($attributeCode);

            return (string)$attribute?->getValue();

        } catch (\Exception $e) {
            $this->logger->error(
                "Error loading customer attribute {$attributeCode}: " . $e->getMessage()
            );
            return '';
        }
    }
}
