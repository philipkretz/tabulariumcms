<?php

namespace App\DataFixtures;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteSettingsFixtures extends Fixture
{
    public function __construct(
        private SiteSettingsRepository $siteSettingsRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $settings = [
            [
                'key' => 'language_switcher_enabled',
                'name' => 'Enable Language Switcher',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'frontend',
                'description' => 'Show/hide language switcher on frontend',
                'isPublic' => true
            ],
            [
                'key' => 'admin_language_switcher_enabled',
                'name' => 'Enable Admin Language Switcher',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'admin',
                'description' => 'Show/hide language switcher in admin panel',
                'isPublic' => false
            ],
        ];

        foreach ($settings as $settingData) {
            // Check if setting already exists
            $existing = $this->siteSettingsRepository->findOneBy(['settingKey' => $settingData['key']]);
            if ($existing) {
                continue;
            }

            $setting = new SiteSettings();
            $setting->setSettingKey($settingData['key']);
            $setting->setSettingName($settingData['name']);
            $setting->setSettingValue($settingData['value']);
            $setting->setSettingType($settingData['type']);
            $setting->setCategory($settingData['category']);
            $setting->setDescription($settingData['description']);
            $setting->setIsPublic($settingData['isPublic']);

            $manager->persist($setting);
        }

        $manager->flush();
    }
}
