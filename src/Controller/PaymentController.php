<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\PaymentMethod;
use App\Repository\OrderRepository;
use App\Service\Payment\StripePaymentService;
use App\Service\Payment\PayPalPaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository,
        private StripePaymentService $stripeService,
        private PayPalPaymentService $paypalService,
        private LoggerInterface $logger
    ) {}

    /**
     * Payment success callback - handles return from payment provider
     */
    #[Route('/payment/success/{orderNumber}', name: 'app_payment_success')]
    #[Route('/{_locale}/payment/success/{orderNumber}', name: 'app_payment_success_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function paymentSuccess(Request $request, string $orderNumber): Response
    {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_homepage');
        }

        $paymentMethod = $order->getPaymentMethod();
        $paymentType = $paymentMethod->getType();

        // Handle Stripe success
        if ($paymentType === PaymentMethod::TYPE_STRIPE) {
            $sessionId = $request->query->get('session_id');
            if ($sessionId) {
                $result = $this->stripeService->verifySession($sessionId);
                if ($result['success']) {
                    $order->setPaymentStatus('paid');
                    $order->setPaymentTransactionId($result['transaction_id']);
                    $order->setPaymentProviderOrderId($sessionId);
                    $order->setPaidAt(new \DateTimeImmutable());
                    $order->setStatus(Order::STATUS_PAYMENT_RECEIVED);
                    $this->em->flush();

                    $this->addFlash('success', 'Payment successful! Thank you for your order.');
                    return $this->redirectToRoute('app_checkout_confirmation', ['orderNumber' => $orderNumber]);
                }
            }
        }

        // Handle PayPal success
        if ($paymentType === PaymentMethod::TYPE_PAYPAL) {
            $paypalOrderId = $request->query->get('token');
            if ($paypalOrderId) {
                // Capture the PayPal payment
                $result = $this->paypalService->captureOrder($paypalOrderId);
                if ($result['success']) {
                    $order->setPaymentStatus('paid');
                    $order->setPaymentTransactionId($result['transaction_id']);
                    $order->setPaymentProviderOrderId($paypalOrderId);
                    $order->setPaidAt(new \DateTimeImmutable());
                    $order->setStatus(Order::STATUS_PAYMENT_RECEIVED);
                    $this->em->flush();

                    $this->addFlash('success', 'Payment successful! Thank you for your order.');
                    return $this->redirectToRoute('app_checkout_confirmation', ['orderNumber' => $orderNumber]);
                }
            }
        }

        // If we get here, something went wrong
        $this->addFlash('warning', 'Payment verification pending. We will confirm your payment shortly.');
        return $this->redirectToRoute('app_checkout_confirmation', ['orderNumber' => $orderNumber]);
    }

    /**
     * Payment cancelled callback
     */
    #[Route('/payment/cancel/{orderNumber}', name: 'app_payment_cancel')]
    #[Route('/{_locale}/payment/cancel/{orderNumber}', name: 'app_payment_cancel_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function paymentCancel(string $orderNumber): Response
    {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if ($order) {
            $order->setPaymentStatus('cancelled');
            $this->em->flush();
        }

        $this->addFlash('warning', 'Payment was cancelled. You can try again or choose a different payment method.');
        return $this->redirectToRoute('app_checkout');
    }

    /**
     * Initiate payment - redirect to payment provider
     */
    #[Route('/payment/initiate/{orderNumber}', name: 'app_payment_initiate')]
    #[Route('/{_locale}/payment/initiate/{orderNumber}', name: 'app_payment_initiate_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function initiatePayment(string $orderNumber): Response
    {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_homepage');
        }

        $paymentMethod = $order->getPaymentMethod();
        $paymentType = $paymentMethod->getType();

        // Stripe payment
        if ($paymentType === PaymentMethod::TYPE_STRIPE) {
            $result = $this->stripeService->createCheckoutSession($order);
            if ($result['success'] && isset($result['checkout_url'])) {
                $order->setPaymentProviderOrderId($result['session_id']);
                $order->setPaymentStatus('pending');
                $this->em->flush();
                return $this->redirect($result['checkout_url']);
            }

            $this->addFlash('error', $result['message'] ?? 'Failed to initialize Stripe payment.');
            return $this->redirectToRoute('app_checkout');
        }

        // PayPal payment
        if ($paymentType === PaymentMethod::TYPE_PAYPAL) {
            $result = $this->paypalService->createOrder($order);
            if ($result['success'] && isset($result['approve_url'])) {
                $order->setPaymentProviderOrderId($result['order_id']);
                $order->setPaymentStatus('pending');
                $this->em->flush();
                return $this->redirect($result['approve_url']);
            }

            $this->addFlash('error', $result['message'] ?? 'Failed to initialize PayPal payment.');
            return $this->redirectToRoute('app_checkout');
        }

        // For non-redirect payment methods (prepayment, cash on delivery, etc.)
        // Just mark as pending and go to confirmation
        $order->setPaymentStatus('pending');
        $this->em->flush();

        return $this->redirectToRoute('app_checkout_confirmation', ['orderNumber' => $orderNumber]);
    }

    /**
     * Stripe webhook handler
     */
    #[Route('/webhook/stripe', name: 'app_webhook_stripe', methods: ['POST'])]
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if (!$webhookSecret) {
            $this->logger->warning('Stripe webhook secret not configured');
            return new JsonResponse(['error' => 'Webhook not configured'], 400);
        }

        $result = $this->stripeService->handleWebhook($payload, $signature, $webhookSecret);

        if (!$result['success'] && !isset($result['event_type'])) {
            $this->logger->error('Stripe webhook error: ' . ($result['message'] ?? 'Unknown error'));
            return new JsonResponse(['error' => $result['message'] ?? 'Webhook error'], 400);
        }

        // Handle checkout.session.completed event
        if (($result['event_type'] ?? '') === 'checkout.session.completed') {
            $orderNumber = $result['order_number'] ?? null;
            if ($orderNumber) {
                $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);
                if ($order && $result['payment_status'] === 'paid') {
                    $order->setPaymentStatus('paid');
                    $order->setPaymentTransactionId($result['payment_intent']);
                    $order->setPaidAt(new \DateTimeImmutable());
                    $order->setStatus(Order::STATUS_PAYMENT_RECEIVED);
                    $this->em->flush();

                    $this->logger->info('Order payment confirmed via webhook', ['order' => $orderNumber]);
                }
            }
        }

        return new JsonResponse(['received' => true]);
    }

    /**
     * PayPal webhook handler
     */
    #[Route('/webhook/paypal', name: 'app_webhook_paypal', methods: ['POST'])]
    public function paypalWebhook(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!$payload) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $result = $this->paypalService->handleWebhook($payload);

        // Handle PAYMENT.CAPTURE.COMPLETED event
        if (($result['event_type'] ?? '') === 'PAYMENT.CAPTURE.COMPLETED') {
            $paypalOrderId = $result['order_id'] ?? null;
            if ($paypalOrderId) {
                $order = $this->orderRepository->findOneBy(['paymentProviderOrderId' => $paypalOrderId]);
                if ($order) {
                    $order->setPaymentStatus('paid');
                    $order->setPaymentTransactionId($result['capture_id']);
                    $order->setPaidAt(new \DateTimeImmutable());
                    $order->setStatus(Order::STATUS_PAYMENT_RECEIVED);
                    $this->em->flush();

                    $this->logger->info('Order payment confirmed via PayPal webhook', ['order' => $order->getOrderNumber()]);
                }
            }
        }

        return new JsonResponse(['received' => true]);
    }

    /**
     * API endpoint to create PayPal order (for JS SDK integration)
     */
    #[Route('/api/payment/paypal/create-order/{orderNumber}', name: 'api_payment_paypal_create', methods: ['POST'])]
    public function createPayPalOrder(string $orderNumber): JsonResponse
    {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        $result = $this->paypalService->createOrder($order);

        if ($result['success']) {
            $order->setPaymentProviderOrderId($result['order_id']);
            $order->setPaymentStatus('pending');
            $this->em->flush();

            return new JsonResponse(['orderID' => $result['order_id']]);
        }

        return new JsonResponse(['error' => $result['message'] ?? 'Failed to create PayPal order'], 500);
    }

    /**
     * API endpoint to capture PayPal order (for JS SDK integration)
     */
    #[Route('/api/payment/paypal/capture-order/{orderNumber}', name: 'api_payment_paypal_capture', methods: ['POST'])]
    public function capturePayPalOrder(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $paypalOrderId = $data['orderID'] ?? $order->getPaymentProviderOrderId();

        if (!$paypalOrderId) {
            return new JsonResponse(['error' => 'PayPal order ID required'], 400);
        }

        $result = $this->paypalService->captureOrder($paypalOrderId);

        if ($result['success']) {
            $order->setPaymentStatus('paid');
            $order->setPaymentTransactionId($result['transaction_id']);
            $order->setPaidAt(new \DateTimeImmutable());
            $order->setStatus(Order::STATUS_PAYMENT_RECEIVED);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'transactionId' => $result['transaction_id'],
                'redirectUrl' => $this->generateUrl('app_checkout_confirmation', ['orderNumber' => $orderNumber]),
            ]);
        }

        return new JsonResponse(['error' => $result['message'] ?? 'Failed to capture payment'], 500);
    }
}
