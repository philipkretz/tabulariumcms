<?php

namespace App\Service\Payment;

use App\Entity\Order;
use App\Entity\PaymentMethod;

class PrepaymentService implements PaymentServiceInterface
{
    public function processPayment(Order $order, array $paymentData = []): array
    {
        return [
            'success' => true,
            'status' => 'pending',
            'message' => 'Awaiting bank transfer. Please transfer the amount to our bank account.',
            'transaction_id' => 'PREPAY-' . uniqid(),
            'instructions' => $this->getBankTransferInstructions($order),
        ];
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        return [
            'success' => true,
            'message' => 'Refund will be processed manually via bank transfer.',
        ];
    }

    public function getPaymentStatus(string $transactionId): array
    {
        return [
            'status' => 'pending',
            'message' => 'Payment pending - awaiting manual confirmation.',
        ];
    }

    public function supports(string $paymentType): bool
    {
        return $paymentType === PaymentMethod::TYPE_PREPAYMENT;
    }

    private function getBankTransferInstructions(Order $order): string
    {
        return sprintf(
            "Bank Transfer Instructions:\n" .
            "Account Holder: TabulariumCMS\n" .
            "IBAN: DE89 3704 0044 0532 0130 00\n" .
            "BIC: COBADEFFXXX\n" .
            "Amount: EUR %s\n" .
            "Reference: %s\n" .
            "\nPlease include the order number in the transfer reference.",
            number_format((float) $order->getTotal(), 2),
            $order->getOrderNumber()
        );
    }
}
