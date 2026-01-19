<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use App\Service\Payment\PaymentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/checkout')]
class CheckoutApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PaymentManager $paymentManager
    ) {}

    #[Route('/methods', name: 'api_checkout_methods', methods: ['GET'])]
    public function getMethods(): JsonResponse
    {
        $paymentMethods = $this->em->getRepository(PaymentMethod::class)
            ->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        $shippingMethods = $this->em->getRepository(ShippingMethod::class)
            ->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        return $this->json([
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
            'payment_methods' => array_map(fn($pm) => [
                'id' => $pm->getId(),
                'name' => $pm->getName(),
                'type' => $pm->getType(),
                'description' => $pm->getDescription(),
                'fee' => $pm->getFee(),
            ], $paymentMethods),
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
            'shipping_methods' => array_map(fn($sm) => [
                'id' => $sm->getId(),
                'name' => $sm->getName(),
                'description' => $sm->getDescription(),
                'price' => $sm->getPrice(),
                'delivery_time' => $sm->getDeliveryTime(),
            ], $shippingMethods),
        ]);
    }

    #[Route('/process', name: 'api_checkout_process', methods: ['POST'])]
    public function processCheckout(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $sessionId = $request->cookies->get('cart_session');
        if (!$sessionId) {
            return $this->json(['error' => 'No cart found'], 400);
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy(['sessionId' => $sessionId]);
        if (!$cart || $cart->getItems()->isEmpty()) {
            return $this->json(['error' => 'Cart is empty'], 400);
        }

        // Get payment and shipping methods
        $paymentMethod = $this->em->getRepository(PaymentMethod::class)->find($data['payment_method_id'] ?? null);
        $shippingMethod = $this->em->getRepository(ShippingMethod::class)->find($data['shipping_method_id'] ?? null);

        if (!$paymentMethod || !$shippingMethod) {
            return $this->json(['error' => 'Invalid payment or shipping method'], 400);
        }

        // Create order
        $order = new Order();
        $order->setCustomer($this->getUser());
        $order->setStatus('pending');
        $order->setPaymentMethod($paymentMethod);
        $order->setShippingMethod($shippingMethod);

        // Set customer info
        $order->setEmail($data['email'] ?? '');
        $order->setCustomerName($data['name'] ?? '');
        $order->setShippingAddress($data['shipping_address'] ?? '');
        $order->setBillingAddress($data['billing_address'] ?? $data['shipping_address'] ?? '');

        // Add order items from cart
        $subtotal = 0;
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setArticle($cartItem->getArticle());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getPrice());
            $orderItem->setOrder($order);
            $order->addItem($orderItem);
            $this->em->persist($orderItem);

            $subtotal += $cartItem->getPrice() * $cartItem->getQuantity();
        }

        // Calculate totals
        $shippingCost = (float)$shippingMethod->getPrice();
        $paymentFee = (float)$paymentMethod->getFee();
        $totalPrice = $subtotal + $shippingCost + $paymentFee;

        $order->setSubtotal((string)$subtotal);
        $order->setShippingCost((string)$shippingCost);
        $order->setTotal((string)$totalPrice);

        $this->em->persist($order);
        $this->em->flush();

        // Process payment
        $paymentResult = $this->paymentManager->processPayment(
            $order, 
            $paymentMethod, 
            $data['payment_data'] ?? []
        );

        if ($paymentResult['success']) {
            // Payment successful - update order status
            $order->setStatus('payment_received');

            // Clear cart after successful order
            foreach ($cart->getItems() as $item) {
                $this->em->remove($item);
            }
            $this->em->remove($cart);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => $paymentResult['message'],
                'order_id' => $order->getId(),
                'transaction_id' => $paymentResult['transaction_id'] ?? null,
                'instructions' => $paymentResult['instructions'] ?? null,
            ]);
        } else {
            $order->setStatus('failed');
            $this->em->flush();

            return $this->json([
                'success' => false,
                'error' => $paymentResult['message'],
            ], 400);
        }
    }
}
