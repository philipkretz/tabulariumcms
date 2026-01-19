<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\EmailService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
class UserRegistrationListener
{
    public function __construct(
        private EmailService $emailService
    ) {
    }

    public function postPersist(User $user, LifecycleEventArgs $event): void
    {
        // Send welcome email to new users
        $this->emailService->sendTemplatedEmail(
            'user-registration',
            $user->getEmail(),
            [
                'user' => [
                    'name' => $user->getUsername(),
                    'email' => $user->getEmail(),
                ],
                'siteUrl' => $_ENV['SITE_URL'] ?? 'http://localhost'
            ],
            $user->getUsername()
        );
    }
}
