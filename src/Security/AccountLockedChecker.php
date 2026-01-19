<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountLockedChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Check if account is locked
        if ($user->isAccountLocked()) {
            $lockedUntil = $user->getLockedUntil();

            if ($lockedUntil !== null) {
                $now = new \DateTimeImmutable();
                $minutesRemaining = max(0, ceil(($lockedUntil->getTimestamp() - $now->getTimestamp()) / 60));

                throw new CustomUserMessageAccountStatusException(
                    sprintf(
                        'Account is temporarily locked due to too many failed login attempts. Please try again in %d minutes.',
                        $minutesRemaining
                    )
                );
            } else {
                // Permanently locked
                throw new CustomUserMessageAccountStatusException(
                    'Account is locked. Please contact support for assistance.'
                );
            }
        }

        // Check if account is deactivated
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been deactivated. Please contact support.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No post-auth checks needed
    }
}
