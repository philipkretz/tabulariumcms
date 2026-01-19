<?php

namespace App\EventListener;

use App\Entity\Post;
use App\Service\ActivityLogService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Post::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Post::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Post::class)]
class PostListener
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    public function postPersist(Post $post, LifecycleEventArgs $event): void
    {
        $this->activityLogService->logPostCreated(
            $post->getId(),
            $post->getTitle() ?? 'Untitled'
        );
    }

    public function postUpdate(Post $post, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($post);

        if (!empty($changeSet)) {
            $changes = [];
            foreach ($changeSet as $field => $values) {
                $changes[$field] = [
                    'old' => $this->formatValue($values[0]),
                    'new' => $this->formatValue($values[1])
                ];
            }
            $this->activityLogService->logPostUpdated(
                $post->getId(),
                $post->getTitle() ?? 'Untitled',
                $changes
            );
        }
    }

    public function preRemove(Post $post, LifecycleEventArgs $event): void
    {
        $this->activityLogService->logPostDeleted(
            $post->getId(),
            $post->getTitle() ?? 'Untitled'
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
