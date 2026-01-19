<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmailService $emailService
    ) {
    }

    #[Route('/newsletter/subscribe', name: 'newsletter_subscribe', methods: ['POST'])]
    public function subscribe(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $name = $request->request->get('name');
        $locale = $request->getLocale();

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        // Check if already subscribed
        $existing = $this->em->getRepository(Newsletter::class)->findOneBy(['email' => $email]);
        
        if ($existing && $existing->isActive() && $existing->isConfirmed()) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'This email is already subscribed'
            ], 400);
        }

        if ($existing && !$existing->isConfirmed()) {
            // Resend confirmation
            $this->sendConfirmationEmail($existing);
            return new JsonResponse([
                'success' => true,
                'message' => 'Confirmation email has been resent'
            ]);
        }

        // Create new subscription
        $newsletter = new Newsletter();
        $newsletter->setEmail($email);
        $newsletter->setName($name);
        $newsletter->setLocale($locale);
        $newsletter->setIpAddress($request->getClientIp());
        $newsletter->setIsActive(true);
        $newsletter->setIsConfirmed(false);

        $this->em->persist($newsletter);
        $this->em->flush();

        // Send confirmation email
        $this->sendConfirmationEmail($newsletter);

        return new JsonResponse([
            'success' => true,
            'message' => 'Please check your email to confirm your subscription'
        ]);
    }

    #[Route('/newsletter/confirm/{token}', name: 'newsletter_confirm')]
    public function confirm(string $token): Response
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->findByToken($token);

        if (!$newsletter) {
            $this->addFlash('error', 'Invalid confirmation link');
            return $this->redirectToRoute('app_homepage');
        }

        if ($newsletter->isConfirmed()) {
            $this->addFlash('info', 'Your subscription is already confirmed');
            return $this->redirectToRoute('app_homepage');
        }

        $newsletter->setIsConfirmed(true);
        $newsletter->setConfirmedAt(new \DateTimeImmutable());
        $this->em->flush();

        // Send welcome email
        $this->emailService->sendTemplatedEmail(
            'newsletter-welcome',
            $newsletter->getEmail(),
            [
                'name' => $newsletter->getName() ?? 'Subscriber',
                'email' => $newsletter->getEmail()
            ],
            $newsletter->getName()
        );

        $this->addFlash('success', 'Thank you! Your newsletter subscription has been confirmed');
        return $this->redirectToRoute('app_homepage');
    }

    #[Route('/newsletter/unsubscribe/{token}', name: 'newsletter_unsubscribe')]
    public function unsubscribe(string $token): Response
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->findByToken($token);

        if (!$newsletter) {
            $this->addFlash('error', 'Invalid unsubscribe link');
            return $this->redirectToRoute('app_homepage');
        }

        if (!$newsletter->isActive()) {
            $this->addFlash('info', 'You are already unsubscribed');
            return $this->redirectToRoute('app_homepage');
        }

        $newsletter->setIsActive(false);
        $newsletter->setUnsubscribedAt(new \DateTimeImmutable());
        $this->em->flush();

        // Send goodbye email
        $this->emailService->sendTemplatedEmail(
            'newsletter-unsubscribe',
            $newsletter->getEmail(),
            [
                'name' => $newsletter->getName() ?? 'Subscriber',
                'email' => $newsletter->getEmail()
            ],
            $newsletter->getName()
        );

        $this->addFlash('success', 'You have been unsubscribed from our newsletter');
        return $this->redirectToRoute('app_homepage');
    }

    private function sendConfirmationEmail(Newsletter $newsletter): void
    {
        $confirmUrl = $this->generateUrl('newsletter_confirm', 
            ['token' => $newsletter->getToken()],
            \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->emailService->sendTemplatedEmail(
            'newsletter-confirm',
            $newsletter->getEmail(),
            [
                'name' => $newsletter->getName() ?? 'Subscriber',
                'email' => $newsletter->getEmail(),
                'confirmUrl' => $confirmUrl
            ],
            $newsletter->getName()
        );
    }
}
