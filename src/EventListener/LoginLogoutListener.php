<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\ActivityLogService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: InteractiveLoginEvent::class)]
#[AsEventListener(event: LogoutEvent::class)]
class LoginLogoutListener
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {
    }

    public function __invoke(InteractiveLoginEvent|LogoutEvent $event): void
    {
        if ($event instanceof InteractiveLoginEvent) {
            $this->onLogin($event);
        } elseif ($event instanceof LogoutEvent) {
            $this->onLogout($event);
        }
    }

    private function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Check if user has admin role
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->activityLogService->logAdminLogin($user);
        }
    }

    private function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token === null) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Check if user has admin role
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->activityLogService->logAdminLogout($user);
        }
    }
}
