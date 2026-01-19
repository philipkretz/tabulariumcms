<?php

namespace App\Service;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;

class SettingsService
{
    private SiteSettingsRepository $repository;
    private EntityManagerInterface $entityManager;
    private array $cache = [];

    public function __construct(SiteSettingsRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function get(string $key, ?string $locale = 'global', $default = null)
    {
        $cacheKey = "{$key}_{$locale}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $setting = $this->repository->findByKeyAndLocale($key, $locale);
        
        if (!$setting) {
            // Try fallback to global
            $setting = $this->repository->findByKeyAndLocale($key, 'global');
        }

        $value = $setting ? $setting->getValue() : $default;
        $this->cache[$cacheKey] = $value;
        
        return $value;
    }

    public function set(string $key, $value, string $type = 'string', ?string $locale = 'global', ?string $category = null): void
    {
        $setting = $this->repository->createOrUpdate($key, $value, $type, $locale, $category);
        $this->repository->save($setting, true);
        
        // Clear cache
        $this->clearCache();
    }

    public function getPaymentSettings(): array
    {
        return [
            'stripe_enabled' => $this->get('stripe_enabled', 'global', false),
            'stripe_public_key' => $this->get('stripe_public_key', 'global'),
            'stripe_secret_key' => $this->get('stripe_secret_key', 'global'),
            'stripe_webhook_secret' => $this->get('stripe_webhook_secret', 'global'),
            
            'paypal_enabled' => $this->get('paypal_enabled', 'global', false),
            'paypal_client_id' => $this->get('paypal_client_id', 'global'),
            'paypal_client_secret' => $this->get('paypal_client_secret', 'global'),
            'paypal_sandbox' => $this->get('paypal_sandbox', 'global', true),
            
            'bank_transfer_enabled' => $this->get('bank_transfer_enabled', 'global', false),
            'bank_account_info' => $this->get('bank_account_info', 'global', []),
            
            'default_currency' => $this->get('default_currency', 'global', 'EUR'),
            'auto_invoice' => $this->get('auto_invoice', 'global', true),
            'payment_timeout' => $this->get('payment_timeout', 'global', 30),
        ];
    }

    public function getSeoSettings(): array
    {
        return [
            'site_title' => $this->get('site_title', 'global'),
            'site_description' => $this->get('site_description', 'global'),
            'site_keywords' => $this->get('site_keywords', 'global', []),
            'default_locale' => $this->get('default_locale', 'global', 'en'),
            'canonical_domain' => $this->get('canonical_domain', 'global'),
            'robots_txt' => $this->get('robots_txt', 'global'),
            'google_analytics' => $this->get('google_analytics', 'global'),
            'google_search_console' => $this->get('google_search_console', 'global'),
            'structured_data_enabled' => $this->get('structured_data_enabled', 'global', true),
        ];
    }

    public function getBlogSettings(): array
    {
        return [
            'posts_per_page' => $this->get('posts_per_page', 'global', 10),
            'enable_comments' => $this->get('enable_comments', 'global', true),
            'comment_moderation' => $this->get('comment_moderation', 'global', true),
            'enable_rss' => $this->get('enable_rss', 'global', true),
            'rss_items' => $this->get('rss_items', 'global', 20),
            'enable_categories' => $this->get('enable_categories', 'global', true),
            'enable_tags' => $this->get('enable_tags', 'global', true),
            'auto_generate_excerpt' => $this->get('auto_generate_excerpt', 'global', true),
            'excerpt_length' => $this->get('excerpt_length', 'global', 150),
        ];
    }

    public function initializeDefaultSettings(): void
    {
        $defaults = [
            // General Settings
            'site_title' => ['TabulariumCMS', 'string', 'general'],
            'site_description' => ['Modern Content Management System', 'string', 'general'],
            'default_locale' => ['en', 'string', 'general'],
            'default_currency' => ['EUR', 'string', 'general'],
            'timezone' => ['UTC', 'string', 'general'],
            
            // Payment Settings
            'stripe_enabled' => [false, 'boolean', 'payment'],
            'paypal_enabled' => [false, 'boolean', 'payment'],
            'paypal_sandbox' => [true, 'boolean', 'payment'],
            'auto_invoice' => [true, 'boolean', 'payment'],
            'payment_timeout' => [30, 'integer', 'payment'],
            
            // SEO Settings
            'structured_data_enabled' => [true, 'boolean', 'seo'],
            'enable_rss' => [true, 'boolean', 'seo'],
            'rss_items' => [20, 'integer', 'seo'],
            
            // Blog Settings
            'posts_per_page' => [10, 'integer', 'blog'],
            'enable_comments' => [true, 'boolean', 'blog'],
            'comment_moderation' => [true, 'boolean', 'blog'],
            'enable_categories' => [true, 'boolean', 'blog'],
            'enable_tags' => [true, 'boolean', 'blog'],
            'auto_generate_excerpt' => [true, 'boolean', 'blog'],
            'excerpt_length' => [150, 'integer', 'blog'],
            
            // Media Settings
            'max_upload_size' => [50, 'integer', 'media'],
            'allowed_file_types' => [['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'], 'array', 'media'],
            'image_quality' => [85, 'integer', 'media'],
            'auto_generate_thumbnails' => [true, 'boolean', 'media'],
        ];

        foreach ($defaults as $key => $config) {
            if (!$this->repository->findByKeyAndLocale($key, 'global')) {
                $setting = new SiteSettings();
                $setting->setSettingKey($key);
                $setting->setValue($config[0]);
                $setting->setSettingType($config[1]);
                $setting->setCategory($config[2]);
                $setting->setLocale('global');
                $this->repository->save($setting);
            }
        }

        $this->entityManager->flush();
        $this->clearCache();
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }

    public function getAllByCategory(string $category): array
    {
        return $this->repository->findByCategory($category);
    }

    public function delete(string $key, ?string $locale = 'global'): void
    {
        $setting = $this->repository->findByKeyAndLocale($key, $locale);
        if ($setting) {
            $this->repository->remove($setting, true);
            $this->clearCache();
        }
    }
}