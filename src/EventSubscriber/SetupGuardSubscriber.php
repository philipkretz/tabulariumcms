<?php

namespace App\EventSubscriber;

use App\Service\InstallationChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SetupGuardSubscriber implements EventSubscriberInterface
{
    private InstallationChecker $installationChecker;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        InstallationChecker $installationChecker,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->installationChecker = $installationChecker;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Only apply to /setup routes
        if (!str_starts_with($path, '/setup')) {
            return;
        }

        // If system is installed, redirect to homepage
        if ($this->installationChecker->isInstalled()) {
            $response = new RedirectResponse($this->urlGenerator->generate('app_homepage'));
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to run before other subscribers
            KernelEvents::REQUEST => [['onKernelRequest', 100]],
        ];
    }
}
