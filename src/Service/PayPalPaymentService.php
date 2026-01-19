<?php

namespace App\Service;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalPaymentService
{
    private PayPalHttpClient $client;
    private string $clientId;
    private bool $isSandbox;

    public function __construct(string $clientId, string $clientSecret, bool $isSandbox = true)
    {
        $this->clientId = $clientId;
        $this->isSandbox = $isSandbox;
        
        $environment = $isSandbox 
            ? new SandboxEnvironment($clientId, $clientSecret)
            : new ProductionEnvironment($clientId, $clientSecret);
            
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder(float $amount, string $currency = 'EUR'): array
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => (string) $amount,
                ],
            ]],
            'application_context' => [
                'cancel_url' => 'https://example.com/cancel',
                'return_url' => 'https://example.com/return',
            ],
        ];

        try {
            $response = $this->client->execute($request);
            
            return [
                'success' => true,
                'order_id' => $response->result->id,
                'approval_url' => $this->getApprovalUrl($response->result->links),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function capturePayment(string $orderId): array
    {
        $request = new OrdersCaptureRequest($orderId);

        try {
            $response = $this->client->execute($request);
            
            return [
                'success' => $response->result->status === 'COMPLETED',
                'status' => $response->result->status,
                'order' => $response->result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getApprovalUrl(array $links): ?string
    {
        foreach ($links as $link) {
            if ($link->rel === 'approve') {
                return $link->href;
            }
        }
        return null;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }
}