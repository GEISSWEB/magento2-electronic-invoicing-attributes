<?php
/**
 * ||GEISSWEB| Electronic Invoicing Attributes
 *
 * @copyright   Copyright (c) 2025 GEISS Weblösungen (https://www.geissweb.de)
 * @license     PolyForm-Noncommercial-1.0.0
 */

declare(strict_types=1);

namespace Geissweb\ElectronicInvoicingAttributes\Plugin\Order;

use Geissweb\ElectronicInvoicingAttributes\Api\OrderEInvoicingRepositoryInterface;
use Geissweb\ElectronicInvoicingAttributes\Model\OrderEInvoicingFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to load and save electronic invoicing extension attributes on orders
 */
class OrderRepositoryPlugin
{
    public function __construct(
        private readonly OrderExtensionFactory $orderExtensionFactory,
        private readonly OrderEInvoicingRepositoryInterface $orderEInvoicingRepository,
        private readonly OrderEInvoicingFactory $orderEInvoicingFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Load extension attributes after getting a single order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        return $this->loadExtensionAttributes($order);
    }

    /**
     * Load extension attributes after getting order list
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ): OrderSearchResultInterface {
        foreach ($searchResult->getItems() as $order) {
            $this->loadExtensionAttributes($order);
        }

        return $searchResult;
    }

    /**
     * Save extension attributes after order save
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            return $result;
        }

        $buyerReference = $extensionAttributes->getEinvoicingBuyerReference();
        $projectReference = $extensionAttributes->getEinvoicingProjectReference();

        if ($buyerReference === null && $projectReference === null) {
            return $result;
        }

        try {
            $orderId = (int)$result->getEntityId();
            $orderEInvoicing = $this->orderEInvoicingRepository->getByOrderId($orderId);

            if ($orderEInvoicing === null) {
                $orderEInvoicing = $this->orderEInvoicingFactory->create();
                $orderEInvoicing->setOrderId($orderId);
            }

            if ($buyerReference !== null) {
                $orderEInvoicing->setBuyerReference($buyerReference);
            }

            if ($projectReference !== null) {
                $orderEInvoicing->setProjectReference($projectReference);
            }

            $this->orderEInvoicingRepository->save($orderEInvoicing);
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to save electronic invoicing extension attributes for order',
                ['order_id' => $result->getEntityId(), 'exception' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Load extension attributes for an order
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function loadExtensionAttributes(OrderInterface $order): OrderInterface
    {
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }

        try {
            $orderId = (int)$order->getEntityId();
            $orderEInvoicing = $this->orderEInvoicingRepository->getByOrderId($orderId);

            if ($orderEInvoicing !== null) {
                $extensionAttributes->setEinvoicingBuyerReference($orderEInvoicing->getBuyerReference());
                $extensionAttributes->setEinvoicingProjectReference($orderEInvoicing->getProjectReference());
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to load electronic invoicing extension attributes for order',
                ['order_id' => $order->getEntityId(), 'exception' => $e->getMessage()]
            );
        }

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
