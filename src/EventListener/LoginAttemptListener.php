<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

#[AsEventListener(event: InteractiveLoginEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LoginFailureEvent::class, method: 'onLoginFailure')]
class LoginAttemptListener
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_DURATION_MINUTES = 30;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoginAttemptRepository $loginAttemptRepository,
        private RequestStack $requestStack
    ) {
    }

    public function onLoginSuccess(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // Record successful login attempt
        $this->loginAttemptRepository->recordAttempt(
            $user->getUsername(),
            $request->getClientIp(),
            true,
            $request->headers->get('User-Agent')
        );

        // Reset failed login attempts counter
        $user->resetFailedLoginAttempts();
        $user->setLastLoginAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $passport = $event->getPassport();
        $user = $passport?->getUser();
        $username = $user ? $user->getUserIdentifier() : 'unknown';

        // Record failed login attempt
        $this->loginAttemptRepository->recordAttempt(
            $username,
            $request->getClientIp(),
            false,
            $request->headers->get('User-Agent')
        );

        // Try to find the user in database
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            return;
        }

        // Check if account is already locked
        if ($user->isAccountLocked()) {
            return;
        }

        // Increment failed attempts
        $user->incrementFailedLoginAttempts();

        // Lock account if threshold reached
        if ($user->getFailedLoginAttempts() >= self::MAX_FAILED_ATTEMPTS) {
            $lockoutUntil = new \DateTimeImmutable('+' . self::LOCKOUT_DURATION_MINUTES . ' minutes');
            $user->setLockedUntil($lockoutUntil);
            $user->setIsAccountLocked(false); // Temporary lock, not permanent
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
