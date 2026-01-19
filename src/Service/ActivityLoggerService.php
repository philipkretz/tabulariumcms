<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActivityLoggerService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage
    ) {}

    /**
     * Log an activity
     *
     * @param string $actionType Use ActivityLog::TYPE_* constants
     * @param string $description Human-readable description of the action
     * @param User|null $user User who performed the action (null = current user)
     * @param string|null $entityType Related entity class name
     * @param int|null $entityId Related entity ID
     * @param array|null $metadata Additional data
     */
    public function log(
        string $actionType,
        string $description,
        ?User $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): void {
        try {
            $log = new ActivityLog();
            $log->setActionType($actionType);
            $log->setDescription($description);

            // Get current user if not provided
            if ($user === null) {
                $token = $this->tokenStorage->getToken();
                if ($token && $token->getUser() instanceof User) {
                    $user = $token->getUser();
                }
            }
            $log->setUser($user);

            // Set entity information
            if ($entityType) {
                $log->setEntityType($entityType);
            }
            if ($entityId) {
                $log->setEntityId($entityId);
            }
            if ($metadata) {
                $log->setMetadata($metadata);
            }

            // Get request information
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $log->setIpAddress($request->getClientIp());
                $log->setUserAgent($request->headers->get('User-Agent'));
            }

            $this->em->persist($log);
            $this->em->flush();
        } catch (\Exception $e) {
            // Silently fail - logging should not break the application
            // In production, you might want to log this error elsewhere
        }
    }

    /**
     * Log user registration
     */
    public function logUserRegister(User $user): void
    {
        $this->log(
            ActivityLog::TYPE_USER_REGISTER,
            sprintf('User "%s" registered', $user->getEmail()),
            $user,
            User::class,
            $user->getId()
        );
    }

    /**
     * Log user login
     */
    public function logUserLogin(User $user): void
    {
        $this->log(
            ActivityLog::TYPE_USER_LOGIN,
            sprintf('User "%s" logged in', $user->getEmail()),
            $user
        );
    }

    /**
     * Log seller registration
     */
    public function logSellerRegister(User $user, string $companyName): void
    {
        $this->log(
            ActivityLog::TYPE_SELLER_REGISTER,
            sprintf('Seller "%s" registered by user %s', $companyName, $user->getEmail()),
            $user,
            'Seller',
            null,
            ['company_name' => $companyName]
        );
    }

    /**
     * Log seller approval
     */
    public function logSellerApproved(int $sellerId, string $companyName): void
    {
        $this->log(
            ActivityLog::TYPE_SELLER_APPROVED,
            sprintf('Seller "%s" was approved', $companyName),
            null,
            'Seller',
            $sellerId
        );
    }

    /**
     * Log product creation
     */
    public function logProductCreated(string $productName, int $productId, ?User $seller = null): void
    {
        $this->log(
            ActivityLog::TYPE_PRODUCT_CREATED,
            sprintf('Product "%s" was created', $productName),
            $seller,
            'Article',
            $productId
        );
    }

    /**
     * Log order creation
     */
    public function logOrderCreated(string $orderNumber, int $orderId, float $total, ?User $customer = null): void
    {
        $this->log(
            ActivityLog::TYPE_ORDER_CREATED,
            sprintf('Order %s created (Total: %.2f EUR)', $orderNumber, $total),
            $customer,
            'Order',
            $orderId,
            ['order_number' => $orderNumber, 'total' => $total]
        );
    }

    /**
     * Log order payment
     */
    public function logOrderPaid(string $orderNumber, int $orderId, float $amount): void
    {
        $this->log(
            ActivityLog::TYPE_ORDER_PAID,
            sprintf('Order %s paid (Amount: %.2f EUR)', $orderNumber, $amount),
            null,
            'Order',
            $orderId,
            ['order_number' => $orderNumber, 'amount' => $amount]
        );
    }

    /**
     * Log seller sale
     */
    public function logSellerSale(int $sellerId, string $companyName, float $saleAmount, float $commission): void
    {
        $this->log(
            ActivityLog::TYPE_SELLER_SALE,
            sprintf('Seller "%s" made a sale: %.2f EUR (Commission: %.2f EUR)', $companyName, $saleAmount, $commission),
            null,
            'Seller',
            $sellerId,
            [
                'sale_amount' => $saleAmount,
                'commission' => $commission,
                'revenue' => $saleAmount - $commission
            ]
        );
    }

    /**
     * Log payment success
     */
    public function logPaymentSuccess(string $orderNumber, float $amount, string $paymentMethod): void
    {
        $this->log(
            ActivityLog::TYPE_PAYMENT_SUCCESS,
            sprintf('Payment successful for order %s: %.2f EUR via %s', $orderNumber, $amount, $paymentMethod),
            null,
            'Order',
            null,
            [
                'order_number' => $orderNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]
        );
    }

    /**
     * Log payment failure
     */
    public function logPaymentFailed(string $orderNumber, string $reason): void
    {
        $this->log(
            ActivityLog::TYPE_PAYMENT_FAILED,
            sprintf('Payment failed for order %s: %s', $orderNumber, $reason),
            null,
            'Order',
            null,
            ['order_number' => $orderNumber, 'reason' => $reason]
        );
    }

    /**
     * Log admin action
     */
    public function logAdminAction(string $action, ?string $details = null): void
    {
        $this->log(
            ActivityLog::TYPE_ADMIN_ACTION,
            $details ?? $action,
            null,
            null,
            null,
            ['action' => $action]
        );
    }
}
