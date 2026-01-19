<?php

namespace App\EventListener;

use App\Repository\SiteSettingsRepository;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminMenuListener
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SiteSettingsRepository $siteSettingsRepository
    ) {}

    public function addMenuItems(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();

        // Check if ecommerce is enabled
        try {
            $settings = $this->siteSettingsRepository->getSettings();
            $ecommerceEnabled = $settings->isEcommerceEnabled();
        } catch (\Exception $e) {
            // Default to enabled if settings can't be loaded
            $ecommerceEnabled = true;
        }

        // Hide ECommerce group if ecommerce is disabled
        if (!$ecommerceEnabled) {
            $ecommerceGroup = $menu->getChild('ECommerce');
            if ($ecommerceGroup) {
                $menu->removeChild('ECommerce');
            }
        }

        // Find or create the CMS group
        $cmsGroup = $menu->getChild('CMS');
        if (!$cmsGroup) {
            $cmsGroup = $menu->addChild('CMS', [
                'label' => 'CMS',
                'attributes' => [
                    'class' => 'header',
                    'icon' => '<i class="fas fa-edit"></i>'
                ]
            ]);
        }

        // Add Email Settings menu item to CMS group
        $cmsGroup->addChild('email_settings', [
            'label' => 'Email Settings',
            'route' => 'admin_email_settings',
            'attributes' => [
                'icon' => '<i class="fas fa-envelope-open-text"></i>'
            ],
            'extras' => [
                'icon' => '<i class="fas fa-envelope-open-text"></i>'
            ]
        ]);

        // Add AI Agent Configuration menu item to CMS group
        $cmsGroup->addChild('agent_configuration', [
            'label' => 'AI Agents',
            'route' => 'admin_agent_configuration',
            'attributes' => [
                'icon' => '<i class="fas fa-robot"></i>'
            ],
            'extras' => [
                'icon' => '<i class="fas fa-robot"></i>'
            ]
        ]);

        // Find or create the System group
        $systemGroup = $menu->getChild('System');
        if (!$systemGroup) {
            $systemGroup = $menu->addChild('System', [
                'label' => 'System',
                'attributes' => [
                    'class' => 'header',
                    'icon' => '<i class="fas fa-cogs"></i>'
                ]
            ]);
        }

        // Add Cache Management menu item
        $systemGroup->addChild('cache_management', [
            'label' => 'Cache Management',
            'route' => 'admin_cache_management',
            'attributes' => [
                'icon' => '<i class="fas fa-database"></i>'
            ],
            'extras' => [
                'icon' => '<i class="fas fa-database"></i>'
            ]
        ]);
    }
}
