<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleRedirectSubscriber implements EventSubscriberInterface
{
    private array $supportedLocales = [
        'en', 'de', 'es', 'fr', 'ca', 'it', 'pt', 'nl', 'pl', 'ru',
        'ja', 'zh', 'ar', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu',
        'ro', 'el', 'tr', 'uk', 'ko', 'hi', 'th', 'vi'
    ];

    private array $excludedRoutes = [
        'admin', '_profiler', '_wdt', 'api', 'public-api', 'change-language',
        'setup', 'webhook', 'payment'
    ];

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // CRITICAL: Never redirect POST/PUT/PATCH/DELETE requests
        // Redirects convert POST to GET, breaking form submissions
        if (!$request->isMethodSafe()) {
            return;
        }

        $path = $request->getPathInfo();

        // Skip if it's an excluded route (admin, API, etc.)
        foreach ($this->excludedRoutes as $excluded) {
            if (str_starts_with($path, '/' . $excluded)) {
                return;
            }
        }

        // Get current path without locale
        $pathParts = explode('/', trim($path, '/'));
        $firstSegment = $pathParts[0] ?? '';

        $hasLocaleInUrl = in_array($firstSegment, $this->supportedLocales);
        $currentUrlLocale = $hasLocaleInUrl ? $firstSegment : 'en';

        // Get user's preferred locale from session
        $session = $request->getSession();
        $preferredLocale = $session->get('_locale', 'en');

        // If user has a non-English preference and the URL doesn't match, redirect
        if ($preferredLocale !== 'en' && $preferredLocale !== $currentUrlLocale) {
            // Build new URL with correct locale
            $pathWithoutLocale = $hasLocaleInUrl
                ? '/' . implode('/', array_slice($pathParts, 1))
                : $path;

            // Normalize path
            if ($pathWithoutLocale === '/' || $pathWithoutLocale === '') {
                $pathWithoutLocale = '';
            }

            $newPath = '/' . $preferredLocale . $pathWithoutLocale;

            // Add query string if present
            if ($request->getQueryString()) {
                $newPath .= '?' . $request->getQueryString();
            }

            $event->setResponse(new RedirectResponse($newPath, 302));
            return;
        }

        // If user prefers English and URL has a locale prefix, redirect to remove it
        if ($preferredLocale === 'en' && $hasLocaleInUrl) {
            $pathWithoutLocale = '/' . implode('/', array_slice($pathParts, 1));

            if ($pathWithoutLocale === '/' || $pathWithoutLocale === '') {
                $pathWithoutLocale = '/';
            }

            // Add query string if present
            if ($request->getQueryString()) {
                $pathWithoutLocale .= '?' . $request->getQueryString();
            }

            $event->setResponse(new RedirectResponse($pathWithoutLocale, 302));
            return;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Run after LocaleSubscriber (priority 20) but before routing
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
