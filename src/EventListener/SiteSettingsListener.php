<?php

namespace App\EventListener;

use App\Entity\SiteSettings;
use App\Service\ActivityLogService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: SiteSettings::class)]
class SiteSettingsListener
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    public function postUpdate(SiteSettings $settings, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($settings);

        if (!empty($changeSet)) {
            $changes = [];
            foreach ($changeSet as $field => $values) {
                $changes[$field] = [
                    'old' => $this->formatValue($values[0]),
                    'new' => $this->formatValue($values[1])
                ];
            }
            $this->activityLogService->logSiteSettingsUpdated($changes);
        }
    }

    private function formatValue($value): string|int|bool|null
    {
        if (is_bool($value)) {
            return $value ? 'enabled' : 'disabled';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }
}
