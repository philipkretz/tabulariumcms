<?php

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = 'Site Settings';

    #[ORM\Column(type: 'boolean')]
    private bool $userProfilesEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $friendSystemEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $messagingEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $userMediaEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $sellerSystemEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $ecommerceEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $userBlockingEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $publicProfilesEnabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $requireProfileApproval = false;

    #[ORM\Column(type: 'integer')]
    private int $maxMediaPerUser = 50;

    #[ORM\Column(type: 'integer')]
    private int $maxMediaSizeKb = 5120; // 5MB

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorEnabledForUsers = true;

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorEnabledForSellers = true;

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorEnabledForAdmins = true;

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorRequired = false;

    #[ORM\Column(type: 'string', length: 3)]
    private string $defaultCurrency = 'EUR';

    // Email Notification Settings
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpHost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $smtpPort = 587;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $smtpUsername = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $smtpPassword = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $smtpEncryption = 'tls';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adminNotificationEmail = null;

    #[ORM\Column(type: 'boolean')]
    private bool $useCustomSmtpSettings = false;

    // Notification toggles
    #[ORM\Column(type: 'boolean')]
    private bool $notifyAdminOnStockLow = true;

    #[ORM\Column(type: 'boolean')]
    private bool $notifyAdminOnNewUser = true;

    #[ORM\Column(type: 'boolean')]
    private bool $notifyAdminOnContactForm = true;

    #[ORM\Column(type: 'boolean')]
    private bool $notifyAdminOnCommentModeration = true;

    #[ORM\Column(type: 'boolean')]
    private bool $notifyAdminOnSellerRegistration = true;

    // Theme Settings
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logoPath = 'tabulariumcms.png';

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $siteName = 'TabulariumCMS';

    #[ORM\Column(type: 'string', length: 7)]
    private string $primaryColor = '#d97706'; // amber-600

    #[ORM\Column(type: 'string', length: 7)]
    private string $secondaryColor = '#b45309'; // amber-700

    #[ORM\Column(type: 'string', length: 7)]
    private string $accentColor = '#92400e'; // amber-800

    #[ORM\Column(type: 'string', length: 7)]
    private string $navigationBgColor = '#fef3c7'; // amber-100

    #[ORM\Column(type: 'string', length: 7)]
    private string $navigationTextColor = '#92400e'; // amber-800

    #[ORM\Column(type: 'string', length: 7)]
    private string $buttonColor = '#d97706'; // amber-600

    #[ORM\Column(type: 'string', length: 7)]
    private string $buttonHoverColor = '#b45309'; // amber-700

    // Responsive Breakpoints (in pixels)
    #[ORM\Column(type: 'integer')]
    private int $breakpointMobile = 768;

    #[ORM\Column(type: 'integer')]
    private int $breakpointTablet = 1024;

    #[ORM\Column(type: 'integer')]
    private int $breakpointDesktop = 1280;

    #[ORM\Column(type: 'integer')]
    private int $breakpointXl = 1536;

    // Container Settings
    #[ORM\Column(type: 'integer')]
    private int $containerMaxWidth = 1280; // in pixels

    // Logo Size (multiplier: 1 = normal, 2 = 2x, 3 = 3x)
    #[ORM\Column(type: 'integer')]
    private int $logoSizeMultiplier = 3;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function isUserProfilesEnabled(): bool { return $this->userProfilesEnabled; }
    public function setUserProfilesEnabled(bool $enabled): static { $this->userProfilesEnabled = $enabled; return $this; }
    public function isFriendSystemEnabled(): bool { return $this->friendSystemEnabled; }
    public function setFriendSystemEnabled(bool $enabled): static { $this->friendSystemEnabled = $enabled; return $this; }
    public function isMessagingEnabled(): bool { return $this->messagingEnabled; }
    public function setMessagingEnabled(bool $enabled): static { $this->messagingEnabled = $enabled; return $this; }
    public function isUserMediaEnabled(): bool { return $this->userMediaEnabled; }
    public function setUserMediaEnabled(bool $enabled): static { $this->userMediaEnabled = $enabled; return $this; }
    public function isSellerSystemEnabled(): bool { return $this->sellerSystemEnabled; }
    public function setSellerSystemEnabled(bool $enabled): static { $this->sellerSystemEnabled = $enabled; return $this; }
    public function isEcommerceEnabled(): bool { return $this->ecommerceEnabled; }
    public function setEcommerceEnabled(bool $enabled): static { $this->ecommerceEnabled = $enabled; return $this; }
    public function isUserBlockingEnabled(): bool { return $this->userBlockingEnabled; }
    public function setUserBlockingEnabled(bool $enabled): static { $this->userBlockingEnabled = $enabled; return $this; }
    public function isPublicProfilesEnabled(): bool { return $this->publicProfilesEnabled; }
    public function setPublicProfilesEnabled(bool $enabled): static { $this->publicProfilesEnabled = $enabled; return $this; }
    public function isRequireProfileApproval(): bool { return $this->requireProfileApproval; }
    public function setRequireProfileApproval(bool $require): static { $this->requireProfileApproval = $require; return $this; }
    public function getMaxMediaPerUser(): int { return $this->maxMediaPerUser; }
    public function setMaxMediaPerUser(int $max): static { $this->maxMediaPerUser = $max; return $this; }
    public function getMaxMediaSizeKb(): int { return $this->maxMediaSizeKb; }
    public function setMaxMediaSizeKb(int $size): static { $this->maxMediaSizeKb = $size; return $this; }
    public function isTwoFactorEnabledForUsers(): bool { return $this->twoFactorEnabledForUsers; }
    public function setTwoFactorEnabledForUsers(bool $enabled): static { $this->twoFactorEnabledForUsers = $enabled; return $this; }
    public function isTwoFactorEnabledForSellers(): bool { return $this->twoFactorEnabledForSellers; }
    public function setTwoFactorEnabledForSellers(bool $enabled): static { $this->twoFactorEnabledForSellers = $enabled; return $this; }
    public function isTwoFactorEnabledForAdmins(): bool { return $this->twoFactorEnabledForAdmins; }
    public function setTwoFactorEnabledForAdmins(bool $enabled): static { $this->twoFactorEnabledForAdmins = $enabled; return $this; }
    public function isTwoFactorRequired(): bool { return $this->twoFactorRequired; }
    public function setTwoFactorRequired(bool $required): static { $this->twoFactorRequired = $required; return $this; }
    public function getDefaultCurrency(): string { return $this->defaultCurrency; }
    public function setDefaultCurrency(string $currency): static { $this->defaultCurrency = $currency; return $this; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // SMTP Configuration Getters/Setters
    public function getSmtpHost(): ?string { return $this->smtpHost; }
    public function setSmtpHost(?string $smtpHost): static { $this->smtpHost = $smtpHost; return $this; }
    public function getSmtpPort(): ?int { return $this->smtpPort; }
    public function setSmtpPort(?int $smtpPort): static { $this->smtpPort = $smtpPort; return $this; }
    public function getSmtpUsername(): ?string { return $this->smtpUsername; }
    public function setSmtpUsername(?string $smtpUsername): static { $this->smtpUsername = $smtpUsername; return $this; }
    public function getSmtpPassword(): ?string { return $this->smtpPassword; }
    public function setSmtpPassword(?string $smtpPassword): static { $this->smtpPassword = $smtpPassword; return $this; }
    public function getSmtpEncryption(): ?string { return $this->smtpEncryption; }
    public function setSmtpEncryption(?string $smtpEncryption): static { $this->smtpEncryption = $smtpEncryption; return $this; }
    public function getAdminNotificationEmail(): ?string { return $this->adminNotificationEmail; }
    public function setAdminNotificationEmail(?string $adminNotificationEmail): static { $this->adminNotificationEmail = $adminNotificationEmail; return $this; }
    public function isUseCustomSmtpSettings(): bool { return $this->useCustomSmtpSettings; }
    public function setUseCustomSmtpSettings(bool $useCustomSmtpSettings): static { $this->useCustomSmtpSettings = $useCustomSmtpSettings; return $this; }

    // Notification Toggle Getters/Setters
    public function isNotifyAdminOnStockLow(): bool { return $this->notifyAdminOnStockLow; }
    public function setNotifyAdminOnStockLow(bool $notifyAdminOnStockLow): static { $this->notifyAdminOnStockLow = $notifyAdminOnStockLow; return $this; }
    public function isNotifyAdminOnNewUser(): bool { return $this->notifyAdminOnNewUser; }
    public function setNotifyAdminOnNewUser(bool $notifyAdminOnNewUser): static { $this->notifyAdminOnNewUser = $notifyAdminOnNewUser; return $this; }
    public function isNotifyAdminOnContactForm(): bool { return $this->notifyAdminOnContactForm; }
    public function setNotifyAdminOnContactForm(bool $notifyAdminOnContactForm): static { $this->notifyAdminOnContactForm = $notifyAdminOnContactForm; return $this; }
    public function isNotifyAdminOnCommentModeration(): bool { return $this->notifyAdminOnCommentModeration; }
    public function setNotifyAdminOnCommentModeration(bool $notifyAdminOnCommentModeration): static { $this->notifyAdminOnCommentModeration = $notifyAdminOnCommentModeration; return $this; }
    public function isNotifyAdminOnSellerRegistration(): bool { return $this->notifyAdminOnSellerRegistration; }
    public function setNotifyAdminOnSellerRegistration(bool $notifyAdminOnSellerRegistration): static { $this->notifyAdminOnSellerRegistration = $notifyAdminOnSellerRegistration; return $this; }

    // Theme Settings Getters/Setters
    public function getLogoPath(): ?string { return $this->logoPath; }
    public function setLogoPath(?string $logoPath): static { $this->logoPath = $logoPath; return $this; }
    public function getSiteName(): ?string { return $this->siteName; }
    public function setSiteName(?string $siteName): static { $this->siteName = $siteName; return $this; }
    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function setPrimaryColor(string $primaryColor): static { $this->primaryColor = $primaryColor; return $this; }
    public function getSecondaryColor(): string { return $this->secondaryColor; }
    public function setSecondaryColor(string $secondaryColor): static { $this->secondaryColor = $secondaryColor; return $this; }
    public function getAccentColor(): string { return $this->accentColor; }
    public function setAccentColor(string $accentColor): static { $this->accentColor = $accentColor; return $this; }
    public function getNavigationBgColor(): string { return $this->navigationBgColor; }
    public function setNavigationBgColor(string $navigationBgColor): static { $this->navigationBgColor = $navigationBgColor; return $this; }
    public function getNavigationTextColor(): string { return $this->navigationTextColor; }
    public function setNavigationTextColor(string $navigationTextColor): static { $this->navigationTextColor = $navigationTextColor; return $this; }
    public function getButtonColor(): string { return $this->buttonColor; }
    public function setButtonColor(string $buttonColor): static { $this->buttonColor = $buttonColor; return $this; }
    public function getButtonHoverColor(): string { return $this->buttonHoverColor; }
    public function setButtonHoverColor(string $buttonHoverColor): static { $this->buttonHoverColor = $buttonHoverColor; return $this; }
    public function getBreakpointMobile(): int { return $this->breakpointMobile; }
    public function setBreakpointMobile(int $breakpointMobile): static { $this->breakpointMobile = $breakpointMobile; return $this; }
    public function getBreakpointTablet(): int { return $this->breakpointTablet; }
    public function setBreakpointTablet(int $breakpointTablet): static { $this->breakpointTablet = $breakpointTablet; return $this; }
    public function getBreakpointDesktop(): int { return $this->breakpointDesktop; }
    public function setBreakpointDesktop(int $breakpointDesktop): static { $this->breakpointDesktop = $breakpointDesktop; return $this; }
    public function getBreakpointXl(): int { return $this->breakpointXl; }
    public function setBreakpointXl(int $breakpointXl): static { $this->breakpointXl = $breakpointXl; return $this; }
    public function getContainerMaxWidth(): int { return $this->containerMaxWidth; }
    public function setContainerMaxWidth(int $containerMaxWidth): static { $this->containerMaxWidth = $containerMaxWidth; return $this; }
    public function getLogoSizeMultiplier(): int { return $this->logoSizeMultiplier; }
    public function setLogoSizeMultiplier(int $logoSizeMultiplier): static { $this->logoSizeMultiplier = $logoSizeMultiplier; return $this; }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
