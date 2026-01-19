<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        RateLimiterFactory $authGlobalLimiter,
        RateLimiterFactory $loginIpLimiter
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Apply global authentication rate limit
        $globalLimiter = $authGlobalLimiter->create($request->getClientIp());
        if (false === $globalLimiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(60, 'Too many requests. Please try again later.');
        }

        // Apply login-specific rate limit
        $ipLimiter = $loginIpLimiter->create($request->getClientIp());
        if (false === $ipLimiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Too many login attempts. Please try again in 15 minutes.');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/{_locale}/login', name: 'app_login_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function loginLocale(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        RateLimiterFactory $authGlobalLimiter,
        RateLimiterFactory $loginIpLimiter
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Apply global authentication rate limit
        $globalLimiter = $authGlobalLimiter->create($request->getClientIp());
        if (false === $globalLimiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(60, 'Too many requests. Please try again later.');
        }

        // Apply login-specific rate limit
        $ipLimiter = $loginIpLimiter->create($request->getClientIp());
        if (false === $ipLimiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Too many login attempts. Please try again in 15 minutes.');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/admin/login', name: 'admin_login')]
    public function adminLogin(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/admin_login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        RateLimiterFactory $authGlobalLimiter,
        RateLimiterFactory $registerIpLimiter
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Apply global authentication rate limit
        $globalLimiter = $authGlobalLimiter->create($request->getClientIp());
        if (false === $globalLimiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(60, 'Too many requests. Please try again later.');
        }

        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            // Apply registration rate limit
            $registerLimiter = $registerIpLimiter->create($request->getClientIp());
            if (false === $registerLimiter->consume(1)->isAccepted()) {
                $error = 'Too many registration attempts. Please try again later.';
                return $this->render('security/register.html.twig', [
                    'error' => $error,
                    'success' => $success,
                ]);
            }
            $email = $request->request->get('email');
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');

            // Validation
            if (empty($email) || empty($username) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Check if user already exists
                $existingUser = $entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $error = 'An account with this email already exists.';
                } else {
                    $existingUsername = $entityManager->getRepository(User::class)
                        ->findOneBy(['username' => $username]);

                    if ($existingUsername) {
                        $error = 'This username is already taken.';
                    } else {
                        // Create new user
                        $user = new User();
                        $user->setEmail($email);
                        $user->setUsername($username);
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setRoles(['ROLE_USER']);
                        $user->setIsVerified(false);
                        $user->setIsActive(true);
                        $user->setLocale('en');
                        $user->setCurrency('EUR');

                        $hashedPassword = $passwordHasher->hashPassword($user, $password);
                        $user->setPassword($hashedPassword);

                        $entityManager->persist($user);
                        $entityManager->flush();

                        $success = true;
                    }
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }

    #[Route('/{_locale}/register', name: 'app_register_locale', methods: ['GET', 'POST'], requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function registerLocale(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        RateLimiterFactory $authGlobalLimiter,
        RateLimiterFactory $registerIpLimiter
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Apply global authentication rate limit
        $globalLimiter = $authGlobalLimiter->create($request->getClientIp());
        if (false === $globalLimiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(60, 'Too many requests. Please try again later.');
        }

        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            // Apply registration rate limit
            $registerLimiter = $registerIpLimiter->create($request->getClientIp());
            if (false === $registerLimiter->consume(1)->isAccepted()) {
                $error = 'Too many registration attempts. Please try again later.';
                return $this->render('security/register.html.twig', [
                    'error' => $error,
                    'success' => $success,
                ]);
            }
            $email = $request->request->get('email');
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');

            // Validation
            if (empty($email) || empty($username) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Check if user already exists
                $existingUser = $entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $error = 'An account with this email already exists.';
                } else {
                    $existingUsername = $entityManager->getRepository(User::class)
                        ->findOneBy(['username' => $username]);

                    if ($existingUsername) {
                        $error = 'This username is already taken.';
                    } else {
                        // Create new user - use locale from URL
                        $user = new User();
                        $user->setEmail($email);
                        $user->setUsername($username);
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setRoles(['ROLE_USER']);
                        $user->setIsVerified(false);
                        $user->setIsActive(true);
                        $user->setLocale($request->getLocale());
                        $user->setCurrency('EUR');

                        $hashedPassword = $passwordHasher->hashPassword($user, $password);
                        $user->setPassword($hashedPassword);

                        $entityManager->persist($user);
                        $entityManager->flush();

                        $success = true;
                    }
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
