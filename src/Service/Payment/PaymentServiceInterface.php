<?php

namespace App\Service\Payment;

use App\Entity\Order;

interface PaymentServiceInterface
{
    public function processPayment(Order $order, array $paymentData = []): array;
    public function refundPayment(string $transactionId, float $amount): array;
    public function getPaymentStatus(string $transactionId): array;
    public function supports(string $paymentType): bool;
}
