<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Service\EmailService;
use App\Service\ActivityLogService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Order::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Order::class)]
class OrderEventListener
{
    public function __construct(
        private EmailService $emailService,
        private LoggerInterface $logger,
        private ActivityLogService $activityLogService
    ) {
    }

    public function postPersist(Order $order, LifecycleEventArgs $event): void
    {
        // Log order creation
        $this->activityLogService->logOrderCreated($order->getId(), $order->getOrderNumber());

        // Send order confirmation email for new orders
        $this->sendOrderEmail($order, 'order-confirmation');
    }

    public function postUpdate(Order $order, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($order);

        // Log order update with changes
        if (!empty($changeSet)) {
            $changes = [];
            foreach ($changeSet as $field => $values) {
                $changes[$field] = [
                    'old' => $values[0],
                    'new' => $values[1]
                ];
            }
            $this->activityLogService->logOrderUpdated($order->getId(), $order->getOrderNumber(), $changes);
        }

        // Check if status changed
        if (isset($changeSet['status'])) {
            $oldStatus = $changeSet['status'][0];
            $newStatus = $changeSet['status'][1];

            $this->logger->info('Order status changed', [
                'orderId' => $order->getId(),
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus
            ]);

            // Send status update email
            $this->sendOrderEmail($order, 'order-status-change');
        }
    }

    private function sendOrderEmail(Order $order, string $templateSlug): void
    {
        try {
            // Get email from customer or guest
            $email = $order->getCustomer()
                ? $order->getCustomer()->getEmail()
                : $order->getGuestEmail();

            // Get customer name from customer or guest
            $customerName = $order->getCustomer()
                ? $order->getCustomer()->getUsername()
                : $order->getGuestName();

            if (!$email) {
                $this->logger->warning('Cannot send order email - no email address', [
                    'orderId' => $order->getId()
                ]);
                return;
            }

            // Prepare order items data
            $items = [];
            foreach ($order->getItems() as $item) {
                $items[] = [
                    'name' => $item->getArticleName(),
                    'sku' => $item->getArticleSku(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                    'subtotal' => $item->getSubtotal(),
                ];
            }

            // Prepare addresses
            $shippingAddress = [
                'street' => $order->getShippingAddress(),
                'line2' => $order->getShippingAddressLine2(),
                'city' => $order->getShippingCity(),
                'postcode' => $order->getShippingPostcode(),
                'country' => $order->getShippingCountry(),
            ];

            $billingAddress = [
                'street' => $order->getBillingAddress(),
                'line2' => $order->getBillingAddressLine2(),
                'city' => $order->getBillingCity(),
                'postcode' => $order->getBillingPostcode(),
                'country' => $order->getBillingCountry(),
            ];

            $this->emailService->sendTemplatedEmail(
                $templateSlug,
                $email,
                [
                    'order' => [
                        'number' => $order->getOrderNumber(),
                        'id' => $order->getId(),
                        'status' => $order->getStatus(),
                        'subtotal' => $order->getSubtotal(),
                        'shippingCost' => $order->getShippingCost(),
                        'discount' => $order->getDiscount(),
                        'taxAmount' => $order->getTaxAmount(),
                        'total' => $order->getTotal(),
                        'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                        'trackingNumber' => $order->getTrackingNumber(),
                        'items' => $items,
                    ],
                    'customer' => [
                        'name' => $customerName ?? 'Customer',
                        'email' => $email,
                    ],
                    'shippingAddress' => $shippingAddress,
                    'billingAddress' => $billingAddress,
                    'paymentMethod' => $order->getPaymentMethod()->getName(),
                    'shippingMethod' => $order->getShippingMethod()->getName(),
                    'orderUrl' => $_ENV['SITE_URL'] ?? 'http://localhost'
                ],
                $customerName
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send order email', [
                'orderId' => $order->getId(),
                'templateSlug' => $templateSlug,
                'error' => $e->getMessage()
            ]);
        }
    }
}
