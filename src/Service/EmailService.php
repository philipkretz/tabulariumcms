<?php

namespace App\Service;

use App\Entity\EmailLog;
use App\Entity\EmailTemplate;
use App\Entity\SiteSettings;
use App\Repository\EmailTemplateRepository;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private EmailTemplateRepository $emailTemplateRepository,
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger,
        private SiteSettingsRepository $settingsRepository,
        private EncryptionService $encryptionService,
        private EntityManagerInterface $entityManager,
        private string $defaultFromEmail = 'noreply@tabulariumcms.local',
        private string $defaultFromName = 'TabulariumCMS'
    ) {
    }

    /**
     * Send an email using a template
     * 
     * @param string $templateSlug The slug of the email template
     * @param string $toEmail Recipient email address
     * @param array $variables Variables to replace in template
     * @param string|null $toName Recipient name (optional)
     * @return bool Success status
     */
    public function sendTemplatedEmail(
        string $templateSlug,
        string $toEmail,
        array $variables = [],
        ?string $toName = null,
        ?string $relatedEntity = null,
        ?int $relatedEntityId = null
    ): bool {
        $emailLog = null;

        try {
            // Find the template
            $template = $this->emailTemplateRepository->findActiveBySlug($templateSlug);

            if (!$template) {
                $this->logger->warning("Email template not found or inactive: {$templateSlug}");
                return false;
            }

            // Render subject and body with Twig
            $subject = $this->renderTemplate($template->getSubject(), $variables);
            $htmlBody = $this->renderTemplate($template->getBodyHtml(), $variables);
            $textBody = $template->getBodyText()
                ? $this->renderTemplate($template->getBodyText(), $variables)
                : null;

            $fromEmail = $template->getFromEmail() ?? $this->defaultFromEmail;
            $fromName = $template->getFromName() ?? $this->defaultFromName;

            // Create email log entry
            $emailLog = new EmailLog();
            $emailLog->setRecipient($toEmail);
            $emailLog->setRecipientName($toName);
            $emailLog->setSubject($subject);
            $emailLog->setBody($htmlBody);
            $emailLog->setPlainTextBody($textBody);
            $emailLog->setTemplateCode($templateSlug);
            $emailLog->setTemplate($template);
            $emailLog->setFromEmail($fromEmail);
            $emailLog->setFromName($fromName);
            $emailLog->setSentAt(new \DateTimeImmutable());
            $emailLog->setStatus(EmailLog::STATUS_PENDING);

            if ($relatedEntity && $relatedEntityId) {
                $emailLog->setRelatedEntity($relatedEntity);
                $emailLog->setRelatedEntityId($relatedEntityId);
            }

            // Create email
            $email = (new Email())
                ->from($fromEmail, $fromName)
                ->to($toEmail)
                ->subject($subject)
                ->html($htmlBody);

            if ($toName) {
                $email->to($toEmail, $toName);
            }

            if ($textBody) {
                $email->text($textBody);
            }

            // Add BCC recipients if configured
            if ($template->getBccEmails()) {
                // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Using built-in 'trim' function
                $bccEmails = array_map('trim', explode(',', $template->getBccEmails()));
                foreach ($bccEmails as $bccEmail) {
                    if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                        $email->addBcc($bccEmail);
                    }
                }
            }

            // Send email
            $this->getMailer()->send($email);

            // Mark as sent
            $emailLog->markAsSent();

            $this->logger->info("Email sent successfully", [
                'template' => $templateSlug,
                'to' => $toEmail
            ]);

            // Persist email log
            $this->entityManager->persist($emailLog);
            $this->entityManager->flush();

            return true;

        } catch (\Exception $e) {
            $this->logger->error("Failed to send email", [
                'template' => $templateSlug,
                'to' => $toEmail,
                'error' => $e->getMessage()
            ]);

            // Mark as failed if log was created
            if ($emailLog) {
                $emailLog->markAsFailed($e->getMessage());
                $this->entityManager->persist($emailLog);
                $this->entityManager->flush();
            }

            return false;
        }
    }

    /**
     * Get the appropriate mailer instance (custom SMTP or default)
     */
    private function getMailer(): MailerInterface
    {
        try {
            $settings = $this->settingsRepository->getSettings();

            if (!$settings->isUseCustomSmtpSettings() || !$settings->getSmtpHost()) {
                // Use default mailer from config
                return $this->mailer;
            }

            // Build custom SMTP DSN
            $dsn = $this->buildSmtpDsn($settings);
            $transport = Transport::fromDsn($dsn);

            return new Mailer($transport);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create custom mailer, falling back to default', [
                'error' => $e->getMessage()
            ]);
            return $this->mailer;
        }
    }

    /**
     * Build SMTP DSN from site settings
     */
    private function buildSmtpDsn(SiteSettings $settings): string
    {
        $host = $settings->getSmtpHost();
        $port = $settings->getSmtpPort() ?? 587;
        $username = $settings->getSmtpUsername();
        $password = $settings->getSmtpPassword();
        $encryption = $settings->getSmtpEncryption();

        // Decrypt password if encrypted
        if ($password && $this->encryptionService->isEncrypted($password)) {
            $password = $this->encryptionService->decrypt($password);
        }

        // Build DSN: smtp://username:password@host:port
        $dsn = 'smtp://';
        if ($username && $password) {
            $dsn .= urlencode($username) . ':' . urlencode($password) . '@';
        } elseif ($username) {
            $dsn .= urlencode($username) . '@';
        }
        $dsn .= $host . ':' . $port;

        // Add encryption parameter
        if ($encryption) {
            $dsn .= '?encryption=' . $encryption;
        }

        return $dsn;
    }

    /**
     * Render a template string with Twig
     */
    private function renderTemplate(string $template, array $variables): string
    {
        try {
            $twigTemplate = $this->twig->createTemplate($template);
            return $twigTemplate->render($variables);
        } catch (\Exception $e) {
            $this->logger->error("Failed to render email template", [
                'error' => $e->getMessage()
            ]);
            return $template;
        }
    }
}
