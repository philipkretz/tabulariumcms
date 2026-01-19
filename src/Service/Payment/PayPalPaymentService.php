<?php

namespace App\Service\Payment;

use App\Entity\Order;
use App\Entity\PaymentMethod;
use PaypalServerSdk\PaypalServerSdkClientBuilder;
use PaypalServerSdk\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdk\Models\Builders\OrderRequestBuilder;
use PaypalServerSdk\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdk\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdk\Models\Builders\ItemBuilder;
use PaypalServerSdk\Models\Builders\AmountBreakdownBuilder;
use PaypalServerSdk\Models\Builders\MoneyBuilder;
use PaypalServerSdk\Models\CheckoutPaymentIntent;
use PaypalServerSdk\Environment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayPalPaymentService implements PaymentServiceInterface
{
    private $client;
    private bool $isConfigured = false;

    public function __construct(
        private string $paypalClientId,
        private string $paypalClientSecret,
        private bool $paypalSandbox,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        if (!$this->paypalClientId ||
            !$this->paypalClientSecret ||
            $this->paypalClientId === 'your-paypal-sandbox-client-id') {
            $this->isConfigured = false;
            return;
        }

        try {
            $this->client = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    ClientCredentialsAuthCredentialsBuilder::init(
                        $this->paypalClientId,
                        $this->paypalClientSecret
                    )
                )
                ->environment($this->paypalSandbox ? Environment::SANDBOX : Environment::PRODUCTION)
                ->build();
            $this->isConfigured = true;
        } catch (\Exception $e) {
            $this->isConfigured = false;
        }
    }

    public function getClientId(): string
    {
        return $this->paypalClientId;
    }

    public function isSandbox(): bool
    {
        return $this->paypalSandbox;
    }

    /**
     * Create a PayPal order
     */
    public function createOrder(Order $order): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured. Please set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in .env',
            ];
        }

        try {
            $items = [];
            $itemTotal = 0;

            foreach ($order->getItems() as $item) {
                $unitPrice = (float) $item->getUnitPrice();
                $quantity = $item->getQuantity();
                $itemTotal += $unitPrice * $quantity;

                $items[] = ItemBuilder::init(
                    $item->getArticleName(),
                    MoneyBuilder::init('EUR', number_format($unitPrice, 2, '.', ''))->build(),
                    (string) $quantity
                )
                    ->sku($item->getArticleSku() ?? 'N/A')
                    ->build();
            }

            $shippingCost = (float) $order->getShippingCost();
            $paymentFee = (float) $order->getPaymentMethod()->getFee();
            $discount = (float) $order->getDiscount();
            $total = $itemTotal + $shippingCost + $paymentFee - $discount;

            $amountBreakdown = AmountBreakdownBuilder::init()
                ->itemTotal(MoneyBuilder::init('EUR', number_format($itemTotal, 2, '.', ''))->build())
                ->shipping(MoneyBuilder::init('EUR', number_format($shippingCost, 2, '.', ''))->build())
                ->handling(MoneyBuilder::init('EUR', number_format($paymentFee, 2, '.', ''))->build())
                ->discount(MoneyBuilder::init('EUR', number_format($discount, 2, '.', ''))->build())
                ->build();

            $purchaseUnit = PurchaseUnitRequestBuilder::init(
                AmountWithBreakdownBuilder::init('EUR', number_format($total, 2, '.', ''))
                    ->breakdown($amountBreakdown)
                    ->build()
            )
                ->referenceId($order->getOrderNumber())
                ->description('Order ' . $order->getOrderNumber())
                ->items($items)
                ->build();

            $orderRequest = OrderRequestBuilder::init(CheckoutPaymentIntent::CAPTURE, [$purchaseUnit])
                ->build();

            $ordersController = $this->client->getOrdersController();
            $response = $ordersController->ordersCreate(['body' => $orderRequest]);
            $paypalOrder = $response->getResult();

            // Find the approve link
            $approveUrl = null;
            foreach ($paypalOrder->getLinks() as $link) {
                if ($link->getRel() === 'approve') {
                    $approveUrl = $link->getHref();
                    break;
                }
            }

            return [
                'success' => true,
                'order_id' => $paypalOrder->getId(),
                'status' => $paypalOrder->getStatus(),
                'approve_url' => $approveUrl,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create PayPal order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Capture a PayPal order after user approval
     */
    public function captureOrder(string $paypalOrderId): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured.',
            ];
        }

        try {
            $ordersController = $this->client->getOrdersController();
            $response = $ordersController->ordersCapture([
                'id' => $paypalOrderId,
                'prefer' => 'return=representation'
            ]);
            $capturedOrder = $response->getResult();

            $transactionId = null;
            $purchaseUnits = $capturedOrder->getPurchaseUnits();
            if ($purchaseUnits && count($purchaseUnits) > 0) {
                $captures = $purchaseUnits[0]->getPayments()?->getCaptures();
                if ($captures && count($captures) > 0) {
                    $transactionId = $captures[0]->getId();
                }
            }

            return [
                'success' => true,
                'order_id' => $capturedOrder->getId(),
                'status' => $capturedOrder->getStatus(),
                'transaction_id' => $transactionId,
                'payer_email' => $capturedOrder->getPayer()?->getEmailAddress(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to capture PayPal order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal order details
     */
    public function getOrderDetails(string $paypalOrderId): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured.',
            ];
        }

        try {
            $ordersController = $this->client->getOrdersController();
            $response = $ordersController->ordersGet(['id' => $paypalOrderId]);
            $paypalOrder = $response->getResult();

            return [
                'success' => true,
                'order_id' => $paypalOrder->getId(),
                'status' => $paypalOrder->getStatus(),
                'payer_email' => $paypalOrder->getPayer()?->getEmailAddress(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get PayPal order: ' . $e->getMessage(),
            ];
        }
    }

    public function processPayment(Order $order, array $paymentData = []): array
    {
        // Create PayPal order for redirect flow
        return $this->createOrder($order);
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured.',
            ];
        }

        try {
            $paymentsController = $this->client->getPaymentsController();
            $response = $paymentsController->capturesRefund([
                'captureId' => $transactionId,
                'body' => [
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency_code' => 'EUR',
                    ],
                ],
            ]);
            $refund = $response->getResult();

            return [
                'success' => true,
                'refund_id' => $refund->getId(),
                'status' => $refund->getStatus(),
                'message' => 'Refund processed successfully.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'PayPal refund failed: ' . $e->getMessage(),
            ];
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        if (!$this->isConfigured) {
            return [
                'status' => 'unknown',
                'message' => 'PayPal is not configured.',
            ];
        }

        try {
            $paymentsController = $this->client->getPaymentsController();
            $response = $paymentsController->capturesGet(['captureId' => $transactionId]);
            $capture = $response->getResult();

            return [
                'status' => $capture->getStatus(),
                'amount' => $capture->getAmount()?->getValue(),
                'currency' => $capture->getAmount()?->getCurrencyCode(),
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
        return $paymentType === PaymentMethod::TYPE_PAYPAL;
    }

    /**
     * Handle PayPal webhook events
     */
    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['event_type'] ?? null;

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                return [
                    'success' => true,
                    'event_type' => $eventType,
                    'order_id' => $payload['resource']['id'] ?? null,
                ];

            case 'PAYMENT.CAPTURE.COMPLETED':
                return [
                    'success' => true,
                    'event_type' => $eventType,
                    'capture_id' => $payload['resource']['id'] ?? null,
                    'order_id' => $payload['resource']['supplementary_data']['related_ids']['order_id'] ?? null,
                ];

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REFUNDED':
                return [
                    'success' => false,
                    'event_type' => $eventType,
                    'capture_id' => $payload['resource']['id'] ?? null,
                ];

            default:
                return [
                    'success' => true,
                    'event_type' => $eventType,
                    'message' => 'Unhandled event type',
                ];
        }
    }
}
