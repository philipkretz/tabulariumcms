<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private Security $security
    ) {
    }

    public function log(
        string $actionType,
        string $description,
        ?User $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): void {
        $activityLog = new ActivityLog();
        $activityLog->setActionType($actionType);
        $activityLog->setDescription($description);

        // Use provided user or get current user
        if ($user === null) {
            $user = $this->security->getUser();
        }
        
        if ($user instanceof User) {
            $activityLog->setUser($user);
        }

        if ($entityType !== null) {
            $activityLog->setEntityType($entityType);
        }

        if ($entityId !== null) {
            $activityLog->setEntityId($entityId);
        }

        if ($metadata !== null) {
            $activityLog->setMetadata($metadata);
        }

        // Capture IP and User Agent
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $activityLog->setIpAddress($request->getClientIp());
            $activityLog->setUserAgent($request->headers->get('User-Agent'));
        }

        $this->entityManager->persist($activityLog);
        $this->entityManager->flush();
    }

    public function logAdminLogin(User $user): void
    {
        $this->log(
            ActivityLog::TYPE_ADMIN_LOGIN,
            sprintf('Admin "%s" logged in', $user->getUsername()),
            $user
        );
    }

    public function logAdminLogout(User $user): void
    {
        $this->log(
            ActivityLog::TYPE_ADMIN_LOGOUT,
            sprintf('Admin "%s" logged out', $user->getUsername()),
            $user
        );
    }

    public function logOrderCreated(int $orderId, string $orderNumber): void
    {
        $this->log(
            ActivityLog::TYPE_ORDER_CREATED,
            sprintf('Order #%s created', $orderNumber),
            null,
            'Order',
            $orderId
        );
    }

    public function logOrderUpdated(int $orderId, string $orderNumber, ?array $changes = null): void
    {
        $this->log(
            ActivityLog::TYPE_ORDER_UPDATED,
            sprintf('Order #%s updated', $orderNumber),
            null,
            'Order',
            $orderId,
            $changes
        );
    }

    public function logSiteSettingsUpdated(?array $changes = null): void
    {
        $this->log(
            ActivityLog::TYPE_SETTINGS_UPDATED,
            'Site settings updated',
            null,
            'SiteSettings',
            null,
            $changes
        );
    }

    public function logPageCreated(int $pageId, string $title): void
    {
        $this->log(
            ActivityLog::TYPE_PAGE_CREATED,
            sprintf('Page "%s" created', $title),
            null,
            'Page',
            $pageId
        );
    }

    public function logPageUpdated(int $pageId, string $title, ?array $changes = null): void
    {
        $this->log(
            ActivityLog::TYPE_PAGE_UPDATED,
            sprintf('Page "%s" updated', $title),
            null,
            'Page',
            $pageId,
            $changes
        );
    }

    public function logPageDeleted(int $pageId, string $title): void
    {
        $this->log(
            ActivityLog::TYPE_PAGE_DELETED,
            sprintf('Page "%s" deleted', $title),
            null,
            'Page',
            $pageId
        );
    }

    public function logPostCreated(int $postId, string $title): void
    {
        $this->log(
            ActivityLog::TYPE_POST_CREATED,
            sprintf('Post "%s" created', $title),
            null,
            'Post',
            $postId
        );
    }

    public function logPostUpdated(int $postId, string $title, ?array $changes = null): void
    {
        $this->log(
            ActivityLog::TYPE_POST_UPDATED,
            sprintf('Post "%s" updated', $title),
            null,
            'Post',
            $postId,
            $changes
        );
    }

    public function logPostDeleted(int $postId, string $title): void
    {
        $this->log(
            ActivityLog::TYPE_POST_DELETED,
            sprintf('Post "%s" deleted', $title),
            null,
            'Post',
            $postId
        );
    }
}
