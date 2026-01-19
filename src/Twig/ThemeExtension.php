<?php

namespace App\Twig;

use App\Repository\SiteSettingsRepository;
use App\Repository\ThemeRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ThemeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteSettingsRepository $siteSettingsRepo,
        private readonly ThemeRepository $themeRepo
    ) {
    }

    public function getGlobals(): array
    {
        $settings = $this->siteSettingsRepo->findOneBy([]);
        $activeTheme = $this->themeRepo->findActive();

        // Default values
        $defaults = [
            'logo_path' => 'tabulariumcms.png',
            'site_name' => 'TabulariumCMS',
            'logo_size_multiplier' => 3,
            'primary_color' => '#d97706',
            'secondary_color' => '#b45309',
            'accent_color' => '#92400e',
            'background_color' => '#ffffff',
            'text_color' => '#1f2937',
            'navigation_bg_color' => '#fef3c7',
            'navigation_text_color' => '#92400e',
            'button_color' => '#d97706',
            'button_hover_color' => '#b45309',
            'heading_font' => 'Inter, system-ui, sans-serif',
            'body_font' => 'Inter, system-ui, sans-serif',
            'font_size' => '16px',
            'container_width' => '1280px',
            'header_style' => 'default',
            'sidebar_position' => 'none',
            'custom_css' => '',
            'breakpoint_mobile' => 768,
            'breakpoint_tablet' => 1024,
            'breakpoint_desktop' => 1280,
            'breakpoint_xl' => 1536,
            'container_max_width' => 1280,
            'active_theme_name' => null,
        ];

        // Apply SiteSettings values if available
        if ($settings) {
            $defaults['logo_path'] = $settings->getLogoPath() ?? $defaults['logo_path'];
            $defaults['site_name'] = $settings->getSiteName() ?? $defaults['site_name'];
            $defaults['logo_size_multiplier'] = $settings->getLogoSizeMultiplier() ?? $defaults['logo_size_multiplier'];
            $defaults['primary_color'] = $settings->getPrimaryColor() ?? $defaults['primary_color'];
            $defaults['secondary_color'] = $settings->getSecondaryColor() ?? $defaults['secondary_color'];
            $defaults['accent_color'] = $settings->getAccentColor() ?? $defaults['accent_color'];
            $defaults['navigation_bg_color'] = $settings->getNavigationBgColor() ?? $defaults['navigation_bg_color'];
            $defaults['navigation_text_color'] = $settings->getNavigationTextColor() ?? $defaults['navigation_text_color'];
            $defaults['button_color'] = $settings->getButtonColor() ?? $defaults['button_color'];
            $defaults['button_hover_color'] = $settings->getButtonHoverColor() ?? $defaults['button_hover_color'];
            $defaults['breakpoint_mobile'] = $settings->getBreakpointMobile() ?? $defaults['breakpoint_mobile'];
            $defaults['breakpoint_tablet'] = $settings->getBreakpointTablet() ?? $defaults['breakpoint_tablet'];
            $defaults['breakpoint_desktop'] = $settings->getBreakpointDesktop() ?? $defaults['breakpoint_desktop'];
            $defaults['breakpoint_xl'] = $settings->getBreakpointXl() ?? $defaults['breakpoint_xl'];
            $defaults['container_max_width'] = $settings->getContainerMaxWidth() ?? $defaults['container_max_width'];
        }

        // Override with active Theme customization values if set
        if ($activeTheme) {
            $defaults['active_theme_name'] = $activeTheme->getName();

            // Only override if theme has specific values set (not null)
            if ($activeTheme->getPrimaryColor()) {
                $defaults['primary_color'] = $activeTheme->getPrimaryColor();
            }
            if ($activeTheme->getSecondaryColor()) {
                $defaults['secondary_color'] = $activeTheme->getSecondaryColor();
            }
            if ($activeTheme->getAccentColor()) {
                $defaults['accent_color'] = $activeTheme->getAccentColor();
            }
            if ($activeTheme->getBackgroundColor()) {
                $defaults['background_color'] = $activeTheme->getBackgroundColor();
            }
            if ($activeTheme->getTextColor()) {
                $defaults['text_color'] = $activeTheme->getTextColor();
            }
            if ($activeTheme->getHeadingFont()) {
                $defaults['heading_font'] = $activeTheme->getHeadingFont();
            }
            if ($activeTheme->getBodyFont()) {
                $defaults['body_font'] = $activeTheme->getBodyFont();
            }
            if ($activeTheme->getFontSize()) {
                $defaults['font_size'] = $activeTheme->getFontSize();
            }
            if ($activeTheme->getContainerWidth()) {
                $defaults['container_width'] = $activeTheme->getContainerWidth();
            }
            if ($activeTheme->getHeaderStyle()) {
                $defaults['header_style'] = $activeTheme->getHeaderStyle();
            }
            if ($activeTheme->getSidebarPosition()) {
                $defaults['sidebar_position'] = $activeTheme->getSidebarPosition();
            }
            if ($activeTheme->getCustomCss()) {
                $defaults['custom_css'] = $activeTheme->getCustomCss();
            }
        }

        return ['theme' => $defaults];
    }
}
