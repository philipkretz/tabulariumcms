<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    private array $supportedLocales = [
        'en', 'de', 'es', 'fr', 'ca', 'it', 'pt', 'nl', 'pl', 'ru',
        'ja', 'zh', 'ar', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu',
        'ro', 'el', 'tr', 'uk', 'ko', 'hi', 'th', 'vi'
    ];

    public function __construct(string $defaultLocale = "en")
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Don't process sub-requests
        if (!$event->isMainRequest()) {
            return;
        }

        $session = $request->getSession();

        // Priority 1: Check if locale is in the URL (from route parameter _locale)
        $urlLocale = $request->attributes->get('_locale');

        if ($urlLocale && in_array($urlLocale, $this->supportedLocales)) {
            // Save URL locale to session for future requests
            $session->set('_locale', $urlLocale);
            $request->setLocale($urlLocale);
            return;
        }

        // Priority 2: Check if locale is in the session (user previously selected)
        $sessionLocale = $session->get('_locale');

        // CRITICAL: Ensure locale is always a string, never an array
        if ($sessionLocale) {
            if (is_array($sessionLocale)) {
                // If it's an array, take the first element or use default
                $sessionLocale = !empty($sessionLocale) ? (string) reset($sessionLocale) : null;
            } elseif (!is_string($sessionLocale)) {
                // If it's not a string or array, ignore it
                $sessionLocale = null;
            }

            if ($sessionLocale && in_array($sessionLocale, $this->supportedLocales)) {
                $request->setLocale($sessionLocale);
                return;
            }
        }

        // Priority 3: Check cookie (from LanguageSwitcher)
        $cookieLocale = $request->cookies->get('locale');
        if ($cookieLocale && in_array($cookieLocale, $this->supportedLocales)) {
            $session->set('_locale', $cookieLocale);
            $request->setLocale($cookieLocale);
            return;
        }

        // Priority 4: Detect browser language
        $browserLocale = $this->detectBrowserLanguage($request);
        if ($browserLocale) {
            $session->set('_locale', $browserLocale);
            $request->setLocale($browserLocale);
            return;
        }

        // Priority 5: Fall back to default locale
        $session->set('_locale', $this->defaultLocale);
        $request->setLocale($this->defaultLocale);
    }

    private function detectBrowserLanguage($request): ?string
    {
        $acceptLanguage = $request->headers->get('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        // Format: "en-US,en;q=0.9,de;q=0.8,fr;q=0.7"
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';q=', $lang);
            $code = trim($parts[0]);
            $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;

            // Extract just the language code (e.g., "en" from "en-US")
            $langCode = strtolower(explode('-', $code)[0]);

            if (!isset($languages[$langCode]) || $languages[$langCode] < $quality) {
                $languages[$langCode] = $quality;
            }
        }

        // Sort by quality (highest first)
        arsort($languages);

        // Find first supported language
        foreach (array_keys($languages) as $langCode) {
            if (in_array($langCode, $this->supportedLocales)) {
                return $langCode;
            }
        }

        return null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [["onKernelRequest", 20]],
        ];
    }
}
