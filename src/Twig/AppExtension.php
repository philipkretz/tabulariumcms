<?php

namespace App\Twig;

use App\Service\LanguageService;
use App\Service\TemplateService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\TwigEnvironment;
use Twig\TwigSource;

class AppExtension extends AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    private LanguageService $languageService;
    private TemplateService $templateService;

    public function __construct(LanguageService $languageService, TemplateService $templateService)
    {
        $this->languageService = $languageService;
        $this->templateService = $templateService;
    }

    public function getGlobals(): array
    {
        // This will be called for every template render
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_language_switch_enabled', [$this->languageService, 'isLanguageSwitchEnabled']),
            new TwigFunction('is_admin_language_switch_enabled', [$this->languageService, 'isAdminLanguageSwitchEnabled']),
            new TwigFunction('get_active_languages',       [$this->languageService, 'getActiveLanguages']),
            new TwigFunction('render_template',            [$this->templateService,  'renderTemplate']),
            new TwigFunction('render_template_by_position',[$this->templateService,  'renderByPosition']),
            new TwigFunction('get_default_language',       [$this->languageService, 'getDefaultLanguage']),
            new TwigFunction('match_language_to_translation', [$this->languageService, 'matchLanguageToTranslation']),
            new TwigFunction('get_locale_string', [$this, 'getLocaleString'], ['needs_context' => true]),
        ];
    }

    /**
     * Safely get locale as a string, handling cases where it might be an array
     */
    public function getLocaleString(array $context): string
    {
        $locale = $context['app']->getRequest()->getLocale();

        // Ensure it's always a string
        if (is_array($locale)) {
            return !empty($locale) ? (string) reset($locale) : 'en';
        }

        return is_string($locale) ? $locale : 'en';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('process_bracket_functions', [$this->templateService, 'processBracketFunctions']),
            new TwigFilter('safe_string', [$this, 'safeString']),
        ];
    }

    /**
     * Safely convert any value to a string, handling arrays
     */
    public function safeString($value, string $default = ''): string
    {
        if (is_array($value)) {
            return !empty($value) ? (string) reset($value) : $default;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return is_scalar($value) ? (string) $value : $default;
    }

    public function getNodeVisitor(): array
    {
        return [];
    }
}
