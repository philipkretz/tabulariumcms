<?php

namespace App\Service\Payment;

use App\Entity\Order;
use App\Entity\PaymentMethod;

class PaymentManager
{
    private array $paymentServices = [];

    public function __construct(
        PrepaymentService $prepaymentService,
        StripePaymentService $stripeService,
        PayPalPaymentService $paypalService,
        GooglePayService $googlePayService
    ) {
        $this->paymentServices = [
            $prepaymentService,
            $stripeService,
            $paypalService,
            $googlePayService,
        ];
    }

    public function processPayment(Order $order, PaymentMethod $paymentMethod, array $paymentData = []): array
    {
        foreach ($this->paymentServices as $service) {
            if ($service->supports($paymentMethod->getType())) {
                return $service->processPayment($order, $paymentData);
            }
        }

        // Fallback for unsupported payment methods
        return [
            'success' => true,
            'status' => 'pending',
            'message' => sprintf('Payment method %s is pending manual processing.', $paymentMethod->getName()),
            'transaction_id' => strtoupper($paymentMethod->getType()) . '-' . uniqid(),
        ];
    }

    public function refundPayment(string $transactionId, PaymentMethod $paymentMethod, float $amount): array
    {
        foreach ($this->paymentServices as $service) {
            if ($service->supports($paymentMethod->getType())) {
                return $service->refundPayment($transactionId, $amount);
            }
        }

        return [
            'success' => false,
            'message' => 'Payment method not supported for refunds.',
        ];
    }

    public function getPaymentStatus(string $transactionId, PaymentMethod $paymentMethod): array
    {
        foreach ($this->paymentServices as $service) {
            if ($service->supports($paymentMethod->getType())) {
                return $service->getPaymentStatus($transactionId);
            }
        }

        return [
            'status' => 'unknown',
            'message' => 'Payment method not supported.',
        ];
    }
}
