<?php

namespace App\Controller;

use App\Repository\SiteSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class SiteSettingsApiController extends AbstractController
{
    public function __construct(
        private SiteSettingsRepository $siteSettingsRepository
    ) {}

    #[Route('/site-settings/public', name: 'api_site_settings_public', methods: ['GET'])]
    public function getPublicSettings(): JsonResponse
    {
        $settings = $this->siteSettingsRepository->findBy(['isPublic' => true]);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $settingsData = array_map(function ($setting) {
            return [
                'key' => $setting->getSettingKey(),
                'value' => $setting->getSettingValue(),
                'type' => $setting->getSettingType(),
                'name' => $setting->getSettingName()
            ];
        }, $settings);

        return new JsonResponse($settingsData);
    }
}
