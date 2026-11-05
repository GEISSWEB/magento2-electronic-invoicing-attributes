<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Test\Unit\Plugin\Quote;

use Geissweb\ElectronicInvoicingAttributes\Api\Data\OrderEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\Data\QuoteEInvoicingInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\OrderEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Api\QuoteEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\OrderEInvoicingFactory;
use Geissweb\ElectronicInvoicingAttributes\Plugin\Quote\QuoteManagementPlugin;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class QuoteManagementPluginTest extends TestCase
{
    private const QUOTE_ID = 4038;
    private const ORDER_ID = 112;
    private const CUSTOMER_ID = 21;

    private QuoteEInvoicingRepositoryInterface&Stub $quoteEInvoicingRepository;
    private OrderEInvoicingRepositoryInterface&MockObject $orderEInvoicingRepository;
    private OrderEInvoicingFactory&Stub $orderEInvoicingFactory;
    private CustomerRepositoryInterface&Stub $customerRepository;
    private QuoteManagement&Stub $quoteManagement;
    private QuoteManagementPlugin $subject;

    protected function setUp(): void
    {
        $this->quoteEInvoicingRepository = $this->createStub(QuoteEInvoicingRepositoryInterface::class);
        $this->orderEInvoicingRepository = $this->createMock(OrderEInvoicingRepositoryInterface::class);
        $this->orderEInvoicingFactory = $this->createStub(OrderEInvoicingFactory::class);
        $this->customerRepository = $this->createStub(CustomerRepositoryInterface::class);
        $this->quoteManagement = $this->createStub(QuoteManagement::class);

        $this->subject = new QuoteManagementPlugin(
            $this->quoteEInvoicingRepository,
            $this->orderEInvoicingRepository,
            $this->orderEInvoicingFactory,
            $this->customerRepository,
            $this->createStub(LoggerInterface::class)
        );
    }

    public function testAfterSubmitCopiesCheckoutValuesToOrder(): void
    {
        $this->givenQuoteValues('DEMO-BR-0001', 'DEMO-PRJ-0001');
        $this->expectSavedOrderValues('DEMO-BR-0001', 'DEMO-PRJ-0001');
        $order = $this->createOrder();

        $result = $this->subject->afterSubmit($this->quoteManagement, $order, $this->createQuote(null));

        $this->assertSame($order, $result);
    }

    public function testAfterSubmitPrefersCheckoutBuyerReferenceOverCustomerAccount(): void
    {
        $this->givenQuoteValues('DEMO-BR-0001', null);
        $this->givenCustomerBuyerReference('DEMO-BR-CUSTOMER');
        $this->expectSavedOrderValues('DEMO-BR-0001', null);

        $this->subject->afterSubmit($this->quoteManagement, $this->createOrder(), $this->createQuote(self::CUSTOMER_ID));
    }

    public function testAfterSubmitUsesCustomerBuyerReferenceWhenCheckoutFieldIsEmpty(): void
    {
        $this->givenQuoteValues(null, 'DEMO-PRJ-0001');
        $this->givenCustomerBuyerReference('DEMO-BR-CUSTOMER');
        $this->expectSavedOrderValues('DEMO-BR-CUSTOMER', 'DEMO-PRJ-0001');

        $this->subject->afterSubmit($this->quoteManagement, $this->createOrder(), $this->createQuote(self::CUSTOMER_ID));
    }

    public function testAfterSubmitSavesNothingWithoutValues(): void
    {
        $this->quoteEInvoicingRepository->method('getByQuoteId')->willReturn(null);
        $this->orderEInvoicingRepository->expects($this->never())->method('save');

        $this->subject->afterSubmit($this->quoteManagement, $this->createOrder(), $this->createQuote(null));
    }

    public function testAfterSubmitReturnsNullWithoutOrder(): void
    {
        $this->orderEInvoicingRepository->expects($this->never())->method('save');

        $result = $this->subject->afterSubmit($this->quoteManagement, null, $this->createStub(Quote::class));

        $this->assertNull($result);
    }

    private function createQuote(?int $customerId): Quote
    {
        $quote = $this->createPartialMock(Quote::class, ['getId']);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn(self::QUOTE_ID);
        $quote->setData('customer_id', $customerId);

        return $quote;
    }

    private function createOrder(): OrderInterface
    {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getEntityId')->willReturn(self::ORDER_ID);

        return $order;
    }

    private function givenQuoteValues(?string $buyerReference, ?string $projectReference): void
    {
        $quoteEInvoicing = $this->createStub(QuoteEInvoicingInterface::class);
        $quoteEInvoicing->method('getBuyerReference')->willReturn($buyerReference);
        $quoteEInvoicing->method('getProjectReference')->willReturn($projectReference);

        $this->quoteEInvoicingRepository->method('getByQuoteId')->willReturnMap([[self::QUOTE_ID, $quoteEInvoicing]]);
    }

    private function givenCustomerBuyerReference(string $buyerReference): void
    {
        $attribute = $this->createStub(AttributeInterface::class);
        $attribute->method('getValue')->willReturn($buyerReference);

        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getCustomAttribute')->willReturnMap([['buyer_reference', $attribute]]);

        $this->customerRepository->method('getById')->willReturnMap([[self::CUSTOMER_ID, $customer]]);
    }

    private function expectSavedOrderValues(string $buyerReference, ?string $projectReference): void
    {
        $orderEInvoicing = $this->createMock(OrderEInvoicingInterface::class);
        $orderEInvoicing->expects($this->once())->method('setOrderId')->with(self::ORDER_ID)->willReturnSelf();
        $orderEInvoicing->expects($this->once())->method('setBuyerReference')->with($buyerReference)->willReturnSelf();
        $orderEInvoicing->expects($this->once())->method('setProjectReference')->with($projectReference)->willReturnSelf();

        $this->orderEInvoicingFactory->method('create')->willReturn($orderEInvoicing);
        $this->orderEInvoicingRepository->expects($this->once())->method('save')->with($orderEInvoicing);
    }
}
