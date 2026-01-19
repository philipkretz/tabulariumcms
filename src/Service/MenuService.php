<?php

namespace App\Service;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\ORM\EntityManagerInterface;

class MenuService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function getMenuByIdentifier(string $identifier): ?Menu
    {
        return $this->em->getRepository(Menu::class)->findOneBy([
            'identifier' => $identifier,
            'isActive' => true
        ]);
    }

    public function getMenuItemsTree(Menu $menu): array
    {
        $items = $menu->getMenuItems()->toArray();

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $rootItems = array_filter($items, function(MenuItem $item) {
            return $item->isActive() && $item->getParent() === null;
        });

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for sorting
        usort($rootItems, function(MenuItem $a, MenuItem $b) {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        $tree = [];
        foreach ($rootItems as $item) {
            $tree[] = $this->buildMenuItemArray($item);
        }

        return $tree;
    }

    private function buildMenuItemArray(MenuItem $item): array
    {
        $children = [];
        foreach ($item->getChildren() as $child) {
            if ($child->isActive()) {
                $children[] = $this->buildMenuItemArray($child);
            }
        }

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for sorting
        usort($children, function($a, $b) {
            return $a['sortOrder'] <=> $b['sortOrder'];
        });

        return [
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'url' => $item->getEffectiveUrl() ?? '#',
            'target' => $item->isOpenInNewTab() ? '_blank' : '',
            'icon' => $item->getIcon(),
            'cssClass' => $item->getCssClass(),
            'sortOrder' => $item->getSortOrder(),
            'children' => $children
        ];
    }

    public function renderMenu(Menu $menu, string $cssClass = ''): string
    {
        $items = $this->getMenuItemsTree($menu);

        if (empty($items)) {
            return '';
        }

        $html = '<nav class="' . htmlspecialchars($cssClass) . '">';
        $html .= '<ul>';

        foreach ($items as $item) {
            $html .= $this->renderMenuItem($item);
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    private function renderMenuItem(array $item, int $depth = 0): string
    {
        $html = '<li class="menu-item-' . $item['id'];
        if (!empty($item['children'])) {
            $html .= ' has-children';
        }
        if (!empty($item['cssClass'])) {
            $html .= ' ' . htmlspecialchars($item['cssClass']);
        }
        $html .= '">';

        $html .= '<a href="' . htmlspecialchars($item['url']) . '"';
        if ($item['target']) {
            $html .= ' target="' . htmlspecialchars($item['target']) . '"';
        }
        $html .= '>';

        if (!empty($item['icon'])) {
            $html .= '<i class="' . htmlspecialchars($item['icon']) . '"></i> ';
        }

        $html .= htmlspecialchars($item['title']);
        $html .= '</a>';

        if (!empty($item['children'])) {
            $html .= '<ul class="submenu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderMenuItem($child, $depth + 1);
            }
            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }
}
