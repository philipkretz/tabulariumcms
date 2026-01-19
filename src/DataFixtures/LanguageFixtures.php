<?php

namespace App\DataFixtures;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private LanguageRepository $languageRepository
    ) {
    }

    public static function getGroups(): array
    {
        return ['language'];
    }

    public function load(ObjectManager $manager): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'nativeName' => 'English',
                'urlPath' => null,
                'flagEmoji' => '🇺🇸',
                'isDefault' => true,
                'isActive' => true,
                'sortOrder' => 1,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'nativeName' => 'Deutsch',
                'urlPath' => 'de',
                'flagEmoji' => '🇩🇪',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 2,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'nativeName' => 'Français',
                'urlPath' => 'fr',
                'flagEmoji' => '🇫🇷',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 3,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'nativeName' => 'Español',
                'urlPath' => 'es',
                'flagEmoji' => '🇪🇸',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 4,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'nativeName' => 'Italiano',
                'urlPath' => 'it',
                'flagEmoji' => '🇮🇹',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 5,
            ],
            [
                'code' => 'pt',
                'name' => 'Portuguese',
                'nativeName' => 'Português',
                'urlPath' => 'pt',
                'flagEmoji' => '🇵🇹',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 6,
            ],
            [
                'code' => 'nl',
                'name' => 'Dutch',
                'nativeName' => 'Nederlands',
                'urlPath' => 'nl',
                'flagEmoji' => '🇳🇱',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 7,
            ],
            [
                'code' => 'pl',
                'name' => 'Polish',
                'nativeName' => 'Polski',
                'urlPath' => 'pl',
                'flagEmoji' => '🇵🇱',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 8,
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'nativeName' => 'Русский',
                'urlPath' => 'ru',
                'flagEmoji' => '🇷🇺',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 9,
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
                'nativeName' => '日本語',
                'urlPath' => 'ja',
                'flagEmoji' => '🇯🇵',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 10,
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese',
                'nativeName' => '中文',
                'urlPath' => 'zh',
                'flagEmoji' => '🇨🇳',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 11,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'nativeName' => 'العربية',
                'urlPath' => 'ar',
                'flagEmoji' => '🇸🇦',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 12,
            ],
            [
                'code' => 'ca',
                'name' => 'Catalan',
                'nativeName' => 'Català',
                'urlPath' => 'ca',
                'flagEmoji' => '🇪🇸',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 13,
            ],
            [
                'code' => 'sv',
                'name' => 'Swedish',
                'nativeName' => 'Svenska',
                'urlPath' => 'sv',
                'flagEmoji' => '🇸🇪',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 14,
            ],
            [
                'code' => 'no',
                'name' => 'Norwegian',
                'nativeName' => 'Norsk',
                'urlPath' => 'no',
                'flagEmoji' => '🇳🇴',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 15,
            ],
            [
                'code' => 'da',
                'name' => 'Danish',
                'nativeName' => 'Dansk',
                'urlPath' => 'da',
                'flagEmoji' => '🇩🇰',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 16,
            ],
            [
                'code' => 'fi',
                'name' => 'Finnish',
                'nativeName' => 'Suomi',
                'urlPath' => 'fi',
                'flagEmoji' => '🇫🇮',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 17,
            ],
            [
                'code' => 'cs',
                'name' => 'Czech',
                'nativeName' => 'Čeština',
                'urlPath' => 'cs',
                'flagEmoji' => '🇨🇿',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 18,
            ],
            [
                'code' => 'sk',
                'name' => 'Slovak',
                'nativeName' => 'Slovenčina',
                'urlPath' => 'sk',
                'flagEmoji' => '🇸🇰',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 19,
            ],
            [
                'code' => 'hu',
                'name' => 'Hungarian',
                'nativeName' => 'Magyar',
                'urlPath' => 'hu',
                'flagEmoji' => '🇭🇺',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 20,
            ],
            [
                'code' => 'ro',
                'name' => 'Romanian',
                'nativeName' => 'Română',
                'urlPath' => 'ro',
                'flagEmoji' => '🇷🇴',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 21,
            ],
            [
                'code' => 'el',
                'name' => 'Greek',
                'nativeName' => 'Ελληνικά',
                'urlPath' => 'el',
                'flagEmoji' => '🇬🇷',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 22,
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'nativeName' => 'Türkçe',
                'urlPath' => 'tr',
                'flagEmoji' => '🇹🇷',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 23,
            ],
            [
                'code' => 'uk',
                'name' => 'Ukrainian',
                'nativeName' => 'Українська',
                'urlPath' => 'uk',
                'flagEmoji' => '🇺🇦',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 24,
            ],
            [
                'code' => 'ko',
                'name' => 'Korean',
                'nativeName' => '한국어',
                'urlPath' => 'ko',
                'flagEmoji' => '🇰🇷',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 25,
            ],
            [
                'code' => 'hi',
                'name' => 'Hindi',
                'nativeName' => 'हिन्दी',
                'urlPath' => 'hi',
                'flagEmoji' => '🇮🇳',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 26,
            ],
            [
                'code' => 'th',
                'name' => 'Thai',
                'nativeName' => 'ไทย',
                'urlPath' => 'th',
                'flagEmoji' => '🇹🇭',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 27,
            ],
            [
                'code' => 'vi',
                'name' => 'Vietnamese',
                'nativeName' => 'Tiếng Việt',
                'urlPath' => 'vi',
                'flagEmoji' => '🇻🇳',
                'isDefault' => false,
                'isActive' => false,
                'sortOrder' => 28,
            ],
        ];

        foreach ($languages as $langData) {
            $existingLanguage = $this->languageRepository->findOneBy(['code' => $langData['code']]);

            if ($existingLanguage) {
                continue;
            }

            $language = new Language();
            $language->setCode($langData['code']);
            $language->setName($langData['name']);
            $language->setNativeName($langData['nativeName']);
            $language->setUrlPath($langData['urlPath']);
            $language->setFlagEmoji($langData['flagEmoji']);
            $language->setIsDefault($langData['isDefault']);
            $language->setIsActive($langData['isActive']);
            $language->setSortOrder($langData['sortOrder']);

            $manager->persist($language);
        }

        $manager->flush();
    }
}
