<?php

namespace App\Service;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LanguageService
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private SiteSettingsRepository $siteSettingsRepository,
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache
    ) {
    }

    public function isLanguageSwitchEnabled(): bool
    {
        $cacheKey = 'language_switcher_enabled';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour

            $setting = $this->siteSettingsRepository->findOneBy([
                'settingKey' => 'language_switcher_enabled'
            ]);

            return $setting && $setting->getSettingValue() === 'true';
        });
    }

    public function isAdminLanguageSwitchEnabled(): bool
    {
        $cacheKey = 'admin_language_switcher_enabled';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour

            $setting = $this->siteSettingsRepository->findOneBy([
                'settingKey' => 'admin_language_switcher_enabled'
            ]);

            return $setting && $setting->getSettingValue() === 'true';
        });
    }

    public function getDefaultLanguage(): string
    {
        $cacheKey = 'default_language';
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour
            
            $setting = $this->siteSettingsRepository->findOneBy([
                'settingKey' => 'default_language'
            ]);
            
            return $setting ? $setting->getSettingValue() : 'en';
        });
    }

    public function getActiveLanguages(): array
    {
        $cacheKey = 'active_languages';
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour
            
            $languages = $this->languageRepository->findBy(
                ['isActive' => true],
                ['name' => 'ASC']
            );
            
            $activeLanguages = [];
            foreach ($languages as $language) {
                $activeLanguages[] = [
                    'code' => $language->getCode(),
                    'name' => $language->getName(),
                    'nativeName' => $language->getNativeName(),
                    'flag' => $language->getFlagEmoji() ?? '🌍'
                ];
            }

            return $activeLanguages;
        });
    }

    public function getActiveLanguageCodes(): array
    {
        $languages = $this->getActiveLanguages();
        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        return array_map(fn($lang) => $lang['code'], $languages);
    }

    public function matchLanguageToTranslation(string $languageCode): ?string
    {
        $languageMap = [
            'en' => 'en',
            'es' => 'es',
            'fr' => 'fr',
            'de' => 'de',
            'it' => 'it',
            'pt' => 'pt',
            'ru' => 'ru',
            'ja' => 'ja',
            'zh' => 'zh',
            'ko' => 'ko',
            'ar' => 'ar',
            'hi' => 'hi',
            'nl' => 'nl',
            'sv' => 'sv',
            'no' => 'no',
            'da' => 'da',
            'fi' => 'fi',
            'pl' => 'pl',
            'tr' => 'tr',
            'el' => 'el',
            'he' => 'he',
            'th' => 'th',
            'vi' => 'vi',
            'cs' => 'cs',
            'sk' => 'sk',
            'hu' => 'hu',
            'ro' => 'ro',
            'bg' => 'bg',
            'hr' => 'hr',
            'sr' => 'sr',
            'sl' => 'sl',
            'et' => 'et',
            'lv' => 'lv',
            'lt' => 'lt',
            'uk' => 'uk',
            'be' => 'be',
            'ka' => 'ka',
            'am' => 'am',
            'sw' => 'sw',
            'zu' => 'zu',
            'af' => 'af',
            'is' => 'is',
            'mt' => 'mt',
            'cy' => 'cy',
            'ga' => 'ga',
            'gd' => 'gd',
            'eu' => 'eu',
            'ca' => 'ca',
            'gl' => 'gl',
            'ast' => 'ast',
            'lb' => 'lb',
            'fo' => 'fo'
        ];

        return $languageMap[$languageCode] ?? 'en';
    }

    public function updateLanguageSetting(string $key, string $value): void
    {
        $setting = $this->siteSettingsRepository->findOneBy(['settingKey' => $key]);
        
        if (!$setting) {
            $setting = new \App\Entity\SiteSettings();
            $setting->setSettingKey($key);
            $setting->setSettingName($key === 'language_switch_enabled' ? 'Enable Language Switch' : 'Default Language');
            $setting->setSettingType($key === 'language_switch_enabled' ? 'boolean' : 'string');
            $setting->setCategory('localization');
            $this->entityManager->persist($setting);
        }
        
        $setting->setSettingValue($value);
        $this->entityManager->flush();
        
        // Clear cache
        $this->cache->delete($key === 'language_switch_enabled' ? 'language_switch_enabled' : 'default_language');
    }

    public function createMissingLanguageSettings(): void
    {
        $requiredSettings = [
            'language_switch_enabled' => 'true',
            'default_language' => 'en'
        ];

        foreach ($requiredSettings as $key => $value) {
            $setting = $this->siteSettingsRepository->findOneBy(['settingKey' => $key]);
            
            if (!$setting) {
                $setting = new \App\Entity\SiteSettings();
                $setting->setSettingKey($key);
                $setting->setSettingName($key === 'language_switch_enabled' ? 'Enable Language Switch' : 'Default Language');
                $setting->setSettingValue($value);
                $setting->setSettingType($key === 'language_switch_enabled' ? 'boolean' : 'string');
                $setting->setCategory('localization');
                $setting->setDescription($key === 'language_switch_enabled' ? 
                    'Show language switch in header and admin panel' : 
                    'Default language for the site');
                $this->entityManager->persist($setting);
            }
        }
        
        $this->entityManager->flush();
    }
}