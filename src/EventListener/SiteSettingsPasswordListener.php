<?php

namespace App\EventListener;

use App\Entity\SiteSettings;
use App\Service\EncryptionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: SiteSettings::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: SiteSettings::class)]
class SiteSettingsPasswordListener
{
    public function __construct(
        private EncryptionService $encryptionService
    ) {}

    public function prePersist(SiteSettings $settings, LifecycleEventArgs $event): void
    {
        $this->encryptPassword($settings);
    }

    public function preUpdate(SiteSettings $settings, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('smtpPassword')) {
            $this->encryptPassword($settings);
        }
    }

    private function encryptPassword(SiteSettings $settings): void
    {
        $password = $settings->getSmtpPassword();

        // Only encrypt if password is not empty and not already encrypted
        if ($password && !$this->encryptionService->isEncrypted($password)) {
            $encrypted = $this->encryptionService->encrypt($password);
            $settings->setSmtpPassword($encrypted);
        }
    }
}
