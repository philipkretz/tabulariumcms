<?php

namespace App\EventListener;

use App\Entity\Page;
use App\Service\ActivityLogService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Page::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Page::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Page::class)]
class PageListener
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    public function postPersist(Page $page, LifecycleEventArgs $event): void
    {
        $this->activityLogService->logPageCreated(
            $page->getId(),
            $page->getTitle() ?? 'Untitled'
        );
    }

    public function postUpdate(Page $page, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($page);

        if (!empty($changeSet)) {
            $changes = [];
            foreach ($changeSet as $field => $values) {
                $changes[$field] = [
                    'old' => $this->formatValue($values[0]),
                    'new' => $this->formatValue($values[1])
                ];
            }
            $this->activityLogService->logPageUpdated(
                $page->getId(),
                $page->getTitle() ?? 'Untitled',
                $changes
            );
        }
    }

    public function preRemove(Page $page, LifecycleEventArgs $event): void
    {
        $this->activityLogService->logPageDeleted(
            $page->getId(),
            $page->getTitle() ?? 'Untitled'
        );
    }

    private function formatValue($value): string|int|bool|null
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 100) . '...';
        }
        return $value;
    }
}
