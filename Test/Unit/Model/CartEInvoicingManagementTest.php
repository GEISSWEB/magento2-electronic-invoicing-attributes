<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Test\Unit\Model;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\CartEInvoicingManagement;
use Geissweb\ElectronicInvoicingAttributes\Model\QuoteEInvoicingFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CartEInvoicingManagementTest extends TestCase
{
    private CartRepositoryInterface&MockObject $cartRepository;
    private QuoteEInvoicingRepositoryInterface&MockObject $quoteEInvoicingRepository;
    private QuoteEInvoicingFactory&MockObject $quoteEInvoicingFactory;
    private CustomerRepositoryInterface&MockObject $customerRepository;
    private LoggerInterface&MockObject $logger;
    private CartEInvoicingManagement $subject;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->quoteEInvoicingRepository = $this->createMock(QuoteEInvoicingRepositoryInterface::class);
        $this->quoteEInvoicingFactory = $this->createMock(QuoteEInvoicingFactory::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->subject = new CartEInvoicingManagement(
            $this->cartRepository,
            $this->quoteEInvoicingRepository,
            $this->quoteEInvoicingFactory,
            $this->customerRepository,
            $this->logger
        );
    }

    public function testGetReturnsExistingQuoteRecord(): void
    {
        $cartId = 42;
        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');

        $quoteEInvoicing = $this->createMock(QuoteEInvoicingInterface::class);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn($quoteEInvoicing);

        $result = $this->subject->get($cartId);

        $this->assertSame($quoteEInvoicing, $result);
    }

    public function testGetReturnsPrefilledFromCustomerWhenNoQuoteRecord(): void
    {
        $cartId = 42;
        $customerId = 10;
        $buyerRef = 'LEITWEG-123';

        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn($customerId);

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');
        $quote->method('getCustomer')->willReturn($quoteCustomer);

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getValue')->willReturn($buyerRef);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with('buyer_reference')->willReturn($attribute);

        $prefilled = $this->createMock(QuoteEInvoicingInterface::class);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn(null);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $this->quoteEInvoicingFactory->method('create')->willReturn($prefilled);

        $prefilled->expects($this->once())->method('setBuyerReference')->with($buyerRef);

        $result = $this->subject->get($cartId);

        $this->assertSame($prefilled, $result);
    }

    public function testGetReturnsNullForGuestWhenNoQuoteRecord(): void
    {
        $cartId = 42;

        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn(null);

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');
        $quote->method('getCustomer')->willReturn($quoteCustomer);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn(null);

        $result = $this->subject->get($cartId);

        $this->assertNull($result);
    }

    public function testGetReturnsNullWhenCustomerHasNoBuyerReference(): void
    {
        $cartId = 42;
        $customerId = 10;

        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn($customerId);

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');
        $quote->method('getCustomer')->willReturn($quoteCustomer);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with('buyer_reference')->willReturn(null);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn(null);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);

        $result = $this->subject->get($cartId);

        $this->assertNull($result);
    }

    public function testGetReturnsNullWhenCustomerBuyerReferenceIsEmpty(): void
    {
        $cartId = 42;
        $customerId = 10;

        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn($customerId);

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');
        $quote->method('getCustomer')->willReturn($quoteCustomer);

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getValue')->willReturn('');

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with('buyer_reference')->willReturn($attribute);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn(null);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);

        $result = $this->subject->get($cartId);

        $this->assertNull($result);
    }

    public function testGetReturnsNullWhenCustomerNotFound(): void
    {
        $cartId = 42;
        $customerId = 999;

        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn($customerId);

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn('42');
        $quote->method('getCustomer')->willReturn($quoteCustomer);

        $this->cartRepository->method('get')->with($cartId)->willReturn($quote);
        $this->quoteEInvoicingRepository->method('getByQuoteId')->with(42)->willReturn(null);
        $this->customerRepository->method('getById')->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $result = $this->subject->get($cartId);

        $this->assertNull($result);
    }
}
