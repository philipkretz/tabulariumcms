<?php

namespace App\EventListener;

use App\Entity\Seller;
use App\Service\EmailService;
use App\Service\ActivityLogService;
use App\Repository\SiteSettingsRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Seller::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Seller::class)]
class SellerEventListener
{
    public function __construct(
        private EmailService $emailService,
        private LoggerInterface $logger,
        private ActivityLogService $activityLogService,
        private SiteSettingsRepository $settingsRepository
    ) {
    }

    public function postPersist(Seller $seller, LifecycleEventArgs $event): void
    {
        // Log seller registration
        $this->activityLogService->logSellerRegistration(
            $seller->getId(),
            $seller->getCompanyName()
        );

        // Send registration email to seller
        $this->sendSellerEmail($seller, 'seller-registration');

        // Send admin notification if enabled
        $settings = $this->settingsRepository->getSettings();
        if ($settings->isNotifyAdminOnSellerRegistration()) {
            $this->sendAdminNotification($seller);
        }
    }

    public function postUpdate(Seller $seller, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($seller);

        // Check if status changed
        if (isset($changeSet['status'])) {
            $oldStatus = $changeSet['status'][0];
            $newStatus = $changeSet['status'][1];

            $this->logger->info('Seller status changed', [
                'sellerId' => $seller->getId(),
                'companyName' => $seller->getCompanyName(),
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus
            ]);

            // Send approval email
            if ($newStatus === Seller::STATUS_APPROVED) {
                $this->activityLogService->logSellerApproved(
                    $seller->getId(),
                    $seller->getCompanyName()
                );
                $this->sendSellerEmail($seller, 'seller-approved');
            }

            // Send rejection email
            if ($newStatus === Seller::STATUS_REJECTED) {
                $this->activityLogService->logSellerRejected(
                    $seller->getId(),
                    $seller->getCompanyName()
                );
                $this->sendSellerEmail($seller, 'seller-rejected');
            }
        }
    }

    private function sendSellerEmail(Seller $seller, string $templateSlug): void
    {
        try {
            $email = $seller->getUser()?->getEmail();
            $name = $seller->getUser()?->getUsername();

            if (!$email) {
                $this->logger->warning('Cannot send seller email - no email address', [
                    'sellerId' => $seller->getId(),
                    'templateSlug' => $templateSlug
                ]);
                return;
            }

            $this->emailService->sendTemplatedEmail(
                $templateSlug,
                $email,
                [
                    'seller' => [
                        'id' => $seller->getId(),
                        'companyName' => $seller->getCompanyName(),
                        'businessName' => $seller->getBusinessName(),
                        'status' => $seller->getStatus(),
                        'commissionRate' => $seller->getCommissionRate(),
                        'registeredAt' => $seller->getRegisteredAt()?->format('Y-m-d H:i:s'),
                        'rejectionReason' => $seller->getRejectionReason(),
                    ],
                    'user' => [
                        'name' => $name ?? 'Seller',
                        'email' => $email,
                    ],
                    'dashboardUrl' => $_ENV['SITE_URL'] ?? 'http://localhost'
                ],
                $name
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send seller email', [
                'sellerId' => $seller->getId(),
                'templateSlug' => $templateSlug,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendAdminNotification(Seller $seller): void
    {
        try {
            $settings = $this->settingsRepository->getSettings();
            $adminEmail = $settings->getAdminNotificationEmail();

            if (!$adminEmail) {
                $this->logger->warning('Cannot send admin notification - no admin email configured');
                return;
            }

            $this->emailService->sendTemplatedEmail(
                'admin-seller-registration',
                $adminEmail,
                [
                    'seller' => [
                        'id' => $seller->getId(),
                        'companyName' => $seller->getCompanyName(),
                        'businessName' => $seller->getBusinessName(),
                        'status' => $seller->getStatus(),
                        'email' => $seller->getUser()?->getEmail(),
                        'phone' => $seller->getPhone(),
                        'website' => $seller->getWebsite(),
                        'registeredAt' => $seller->getRegisteredAt()?->format('Y-m-d H:i:s'),
                    ],
                    'adminUrl' => $_ENV['SITE_URL'] . '/admin/app/seller/' . $seller->getId() . '/edit'
                ],
                'Admin'
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send admin seller notification', [
                'sellerId' => $seller->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }
}
