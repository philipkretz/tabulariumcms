<?php

namespace App\Service;

use App\Entity\NewsletterCampaign;
use App\Repository\NewsletterRepository;
use App\Repository\NewsletterCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class NewsletterService
{
    public function __construct(
        private EmailService $emailService,
        private NewsletterRepository $newsletterRepository,
        private NewsletterCampaignRepository $campaignRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {
    }

    public function sendCampaign(NewsletterCampaign $campaign): array
    {
        // Get all active, confirmed subscribers
        $subscribers = $this->newsletterRepository->findActiveSubscribers();

        $campaign->setTotalRecipients(count($subscribers));
        $campaign->setStatus(NewsletterCampaign::STATUS_SENDING);
        $campaign->setSentCount(0);
        $campaign->setFailedCount(0);
        $this->em->flush();

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $this->sendToSubscriber($campaign, $subscriber);
                $campaign->incrementSentCount();
                $sent++;

                // Flush every 10 emails to update progress
                if ($sent % 10 === 0) {
                    $this->em->flush();
                }
            } catch (\Exception $e) {
                $campaign->incrementFailedCount();
                $failed++;

                $this->logger->error('Failed to send newsletter campaign', [
                    'campaignId' => $campaign->getId(),
                    'subscriberId' => $subscriber->getId(),
                    'subscriberEmail' => $subscriber->getEmail(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Mark as completed
        $campaign->setStatus(NewsletterCampaign::STATUS_COMPLETED);
        $campaign->setSentAt(new \DateTimeImmutable());
        $this->em->flush();

        return [
            'total' => count($subscribers),
            'sent' => $sent,
            'failed' => $failed
        ];
    }

    public function sendTestEmail(NewsletterCampaign $campaign, string $testEmail): void
    {
        $this->emailService->sendTemplatedEmail(
            'newsletter-campaign',
            $testEmail,
            [
                'campaign' => [
                    'subject' => $campaign->getSubject(),
                    'content' => $campaign->getContent(),
                ],
                'subscriber' => [
                    'email' => $testEmail,
                    'name' => 'Test User'
                ],
                'unsubscribeUrl' => '#test-unsubscribe-url'
            ],
            'Test User',
            $campaign->getSubject()
        );
    }

    private function sendToSubscriber(NewsletterCampaign $campaign, $subscriber): void
    {
        // Generate unsubscribe URL
        $unsubscribeUrl = $_ENV['SITE_URL'] . '/newsletter/unsubscribe/' . $subscriber->getToken();

        $this->emailService->sendTemplatedEmail(
            'newsletter-campaign',
            $subscriber->getEmail(),
            [
                'campaign' => [
                    'subject' => $campaign->getSubject(),
                    'content' => $campaign->getContent(),
                ],
                'subscriber' => [
                    'email' => $subscriber->getEmail(),
                    'name' => $subscriber->getName() ?? 'Subscriber'
                ],
                'unsubscribeUrl' => $unsubscribeUrl
            ],
            $subscriber->getName() ?? 'Subscriber',
            $campaign->getSubject()
        );
    }

    public function getSubscriberCount(): int
    {
        return count($this->newsletterRepository->findActiveSubscribers());
    }
}
