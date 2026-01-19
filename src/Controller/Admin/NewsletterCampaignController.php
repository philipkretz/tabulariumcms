<?php

namespace App\Controller\Admin;

use App\Entity\NewsletterCampaign;
use App\Repository\NewsletterCampaignRepository;
use App\Repository\SiteSettingsRepository;
use App\Service\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/newsletter-campaign')]
class NewsletterCampaignController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
        private NewsletterCampaignRepository $campaignRepository,
        private SiteSettingsRepository $settingsRepository
    ) {
    }

    #[Route('/{id}/preview', name: 'admin_newsletter_campaign_preview')]
    public function preview(int $id): Response
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            throw $this->createNotFoundException('Newsletter campaign not found');
        }

        return $this->render('admin/newsletter_campaign/preview.html.twig', [
            'campaign' => $campaign
        ]);
    }

    #[Route('/{id}/test', name: 'admin_newsletter_campaign_test')]
    public function test(Request $request, int $id): Response
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            throw $this->createNotFoundException('Newsletter campaign not found');
        }

        if (!$campaign->isDraft()) {
            $this->addFlash('error', 'Can only send test emails for draft campaigns');
            return $this->redirectToRoute('admin_app_newslettercampaign_list');
        }

        // Get admin email from settings
        $settings = $this->settingsRepository->getSettings();
        $testEmail = $settings->getAdminNotificationEmail();

        if (!$testEmail) {
            $this->addFlash('error', 'No admin email configured in site settings');
            return $this->redirectToRoute('admin_app_newslettercampaign_list');
        }

        try {
            $this->newsletterService->sendTestEmail($campaign, $testEmail);
            $this->addFlash('success', sprintf('Test email sent to %s', $testEmail));
        } catch (\Exception $e) {
            $this->addFlash('error', sprintf('Failed to send test email: %s', $e->getMessage()));
        }

        return $this->redirectToRoute('admin_app_newslettercampaign_list');
    }

    #[Route('/{id}/send', name: 'admin_newsletter_campaign_send', methods: ['GET'])]
    public function send(int $id): Response
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            throw $this->createNotFoundException('Newsletter campaign not found');
        }

        if (!$campaign->isDraft()) {
            $this->addFlash('error', 'Campaign has already been sent or is currently sending');
            return $this->redirectToRoute('admin_app_newslettercampaign_list');
        }

        try {
            $result = $this->newsletterService->sendCampaign($campaign);

            $this->addFlash('success', sprintf(
                'Campaign sent successfully! Sent: %d, Failed: %d out of %d total subscribers',
                $result['sent'],
                $result['failed'],
                $result['total']
            ));
        } catch (\Exception $e) {
            $campaign->setStatus(NewsletterCampaign::STATUS_FAILED);
            $this->campaignRepository->save($campaign, true);

            $this->addFlash('error', sprintf('Failed to send campaign: %s', $e->getMessage()));
        }

        return $this->redirectToRoute('admin_app_newslettercampaign_list');
    }
}
