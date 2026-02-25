<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Magewire\Checkout;

use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\QuoteEInvoicingFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;

/**
 * Magewire component for e-invoicing checkout fields in Hyvä Checkout
 */
class EInvoicingFields extends Component
{
    public ?string $buyerReference = null;

    public ?string $projectReference = null;

    public bool $showBuyerReference = true;

    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly QuoteEInvoicingRepositoryInterface $quoteEInvoicingRepository,
        private readonly QuoteEInvoicingFactory $quoteEInvoicingFactory
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteId = (int) $quote->getId();

        $customerBuyerReference = $this->getCustomerBuyerReference();

        if ($customerBuyerReference !== '') {
            $this->showBuyerReference = false;
        }

        if ($quoteId === 0) {
            $this->buyerReference = $customerBuyerReference ?: null;

            return;
        }

        $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);

        if ($quoteEInvoicing !== null) {
            $this->buyerReference = $quoteEInvoicing->getBuyerReference();
            $this->projectReference = $quoteEInvoicing->getProjectReference();
        }

        if (empty($this->buyerReference) && $customerBuyerReference !== '') {
            $this->buyerReference = $customerBuyerReference;
        }
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public function updatingBuyerReference(?string $value): ?string
    {
        $this->saveToQuote($value, $this->projectReference);

        return $value;
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public function updatingProjectReference(?string $value): ?string
    {
        $this->saveToQuote($this->buyerReference, $value);

        return $value;
    }

    /**
     * @param string|null $buyerReference
     * @param string|null $projectReference
     */
    private function saveToQuote(?string $buyerReference, ?string $projectReference): void
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $quoteId = (int) $quote->getId();

            if ($quoteId === 0) {
                return;
            }

            $quoteEInvoicing = $this->quoteEInvoicingRepository->getByQuoteId($quoteId);

            if ($quoteEInvoicing === null) {
                $quoteEInvoicing = $this->quoteEInvoicingFactory->create();
                $quoteEInvoicing->setQuoteId($quoteId);
            }

            $quoteEInvoicing->setBuyerReference($buyerReference);
            $quoteEInvoicing->setProjectReference($projectReference);

            $this->quoteEInvoicingRepository->save($quoteEInvoicing);
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage(
                'Something went wrong while saving the e-invoicing data. Please try again.'
            );
        }
    }

    /**
     * @return string
     */
    private function getCustomerBuyerReference(): string
    {
        $customerId = (int) $this->customerSession->getCustomerId();

        if ($customerId === 0) {
            return '';
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            $attribute = $customer->getCustomAttribute('buyer_reference');

            return (string) ($attribute?->getValue() ?? '');
        } catch (NoSuchEntityException) {
            return '';
        }
    }
}
