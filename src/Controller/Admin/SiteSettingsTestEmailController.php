<?php

namespace App\Controller\Admin;

use App\Repository\SiteSettingsRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/admin/site-settings')]
class SiteSettingsTestEmailController extends AbstractController
{
    public function __construct(
        private SiteSettingsRepository $settingsRepo,
        private EmailService $emailService,
        private LoggerInterface $logger
    ) {}

    #[Route('/test-email', name: 'admin_site_settings_test_email', methods: ['POST'])]
    public function testEmail(Request $request): JsonResponse
    {
        try {
            $settings = $this->settingsRepo->getSettings();
            $testEmail = $settings->getAdminNotificationEmail();

            if (!$testEmail) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Please set an admin notification email first'
                ], 400);
            }

            // Send test email
            $success = $this->emailService->sendTemplatedEmail(
                'test-email',
                $testEmail,
                [
                    'smtpHost' => $settings->getSmtpHost() ?? 'default',
                    'smtpPort' => $settings->getSmtpPort() ?? 'default',
                    'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ]
            );

            if ($success) {
                return new JsonResponse([
                    'success' => true,
                    'message' => "Test email sent successfully to {$testEmail}"
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Failed to send test email. Check logs for details.'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error('Test email failed', [
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
