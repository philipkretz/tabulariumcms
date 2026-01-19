<?php

namespace App\Twig;

use App\Service\MenuService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MenuExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private MenuService $menuService
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'headerMenu' => $this->menuService->getMenuByIdentifier('main-menu'),
            'footerMenu' => $this->menuService->getMenuByIdentifier('footer-menu'),
            'mobileMenu' => $this->menuService->getMenuByIdentifier('mobile-menu'),
        ];
    }
}
