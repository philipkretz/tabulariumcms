<?php

namespace App\Service\Payment;

use App\Entity\Order;
use App\Entity\PaymentMethod;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripePaymentService implements PaymentServiceInterface
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripePublicKey,
        private UrlGeneratorInterface $urlGenerator
    ) {
        if ($this->stripeSecretKey && $this->stripeSecretKey !== 'sk_test_123456789') {
            Stripe::setApiKey($this->stripeSecretKey);
        }
    }

    public function getPublicKey(): string
    {
        return $this->stripePublicKey;
    }

    /**
     * Create a Stripe Checkout Session for the order
     */
    public function createCheckoutSession(Order $order): array
    {
        if (!$this->stripeSecretKey || $this->stripeSecretKey === 'sk_test_123456789') {
            return [
                'success' => false,
                'message' => 'Stripe is not configured. Please set STRIPE_SECRET_KEY in .env',
            ];
        }

        try {
            $lineItems = [];

            foreach ($order->getItems() as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item->getArticleName(),
                            'description' => 'SKU: ' . ($item->getArticleSku() ?? 'N/A'),
                        ],
                        'unit_amount' => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity(),
                ];
            }

            // Add shipping cost as a line item if > 0
            $shippingCost = (float) $order->getShippingCost();
            if ($shippingCost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Shipping: ' . $order->getShippingMethod()->getName(),
                        ],
                        'unit_amount' => (int) round($shippingCost * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            // Add payment fee if applicable
            $paymentFee = (float) $order->getPaymentMethod()->getFee();
            if ($paymentFee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Payment Fee',
                        ],
                        'unit_amount' => (int) round($paymentFee * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->urlGenerator->generate('app_payment_success', [
                    'orderNumber' => $order->getOrderNumber(),
                ], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->urlGenerator->generate('app_payment_cancel', [
                    'orderNumber' => $order->getOrderNumber(),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'customer_email' => $order->getEmail(),
                'metadata' => [
                    'order_number' => $order->getOrderNumber(),
                    'order_id' => $order->getId(),
                ],
                'expires_at' => time() + 1800, // 30 minutes
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create Stripe checkout session: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a completed Stripe Checkout Session
     */
    public function verifySession(string $sessionId): array
    {
        if (!$this->stripeSecretKey || $this->stripeSecretKey === 'sk_test_123456789') {
            return [
                'success' => false,
                'message' => 'Stripe is not configured.',
            ];
        }

        try {
            $session = StripeSession::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return [
                    'success' => true,
                    'status' => 'paid',
                    'transaction_id' => $session->payment_intent,
                    'session_id' => $session->id,
                    'amount_total' => $session->amount_total / 100,
                    'currency' => $session->currency,
                    'customer_email' => $session->customer_email,
                    'metadata' => $session->metadata->toArray(),
                ];
            }

            return [
                'success' => false,
                'status' => $session->payment_status,
                'message' => 'Payment not completed.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to verify Stripe session: ' . $e->getMessage(),
            ];
        }
    }

    public function processPayment(Order $order, array $paymentData = []): array
    {
        // For Stripe, we use Checkout Sessions, so this creates the session
        return $this->createCheckoutSession($order);
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        if (!$this->stripeSecretKey || $this->stripeSecretKey === 'sk_test_123456789') {
            return [
                'success' => false,
                'message' => 'Stripe is not configured.',
            ];
        }

        try {
            $refund = Refund::create([
                'payment_intent' => $transactionId,
                'amount' => (int) round($amount * 100),
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100,
                'message' => 'Refund processed successfully.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Stripe refund failed: ' . $e->getMessage(),
            ];
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        if (!$this->stripeSecretKey || $this->stripeSecretKey === 'sk_test_123456789') {
            return [
                'status' => 'unknown',
                'message' => 'Stripe is not configured.',
            ];
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($transactionId);

            return [
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'message' => 'Payment status retrieved.',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to get payment status: ' . $e->getMessage(),
            ];
        }
    }

    public function supports(string $paymentType): bool
    {
        return $paymentType === PaymentMethod::TYPE_STRIPE;
    }

    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(string $payload, string $signature, string $webhookSecret): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    return [
                        'success' => true,
                        'event_type' => 'checkout.session.completed',
                        'order_number' => $session->metadata->order_number ?? null,
                        'payment_intent' => $session->payment_intent,
                        'payment_status' => $session->payment_status,
                    ];

                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    return [
                        'success' => true,
                        'event_type' => 'payment_intent.succeeded',
                        'payment_intent' => $paymentIntent->id,
                        'amount' => $paymentIntent->amount / 100,
                    ];

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    return [
                        'success' => false,
                        'event_type' => 'payment_intent.payment_failed',
                        'payment_intent' => $paymentIntent->id,
                        'error' => $paymentIntent->last_payment_error?->message ?? 'Payment failed',
                    ];

                default:
                    return [
                        'success' => true,
                        'event_type' => $event->type,
                        'message' => 'Unhandled event type',
                    ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Webhook error: ' . $e->getMessage(),
            ];
        }
    }
}
