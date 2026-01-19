<?php

namespace App\Controller\Admin;

use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Repository\MenuRepository;
use App\Repository\MenuItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/app/menu/menu')]
#[IsGranted('ROLE_ADMIN')]
class MenuDragDropController extends AbstractController
{
    public function __construct(
        private MenuRepository $menuRepository,
        private MenuItemRepository $menuItemRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/{id}/organize', name: 'admin_menu_organize', methods: ['GET'])]
    public function organize(Menu $menu): Response
    {
        // Get all menu items for this menu, ordered by sort_order
        $menuItems = $this->menuItemRepository->findBy(
            ['menu' => $menu, 'parent' => null],
            ['sortOrder' => 'ASC']
        );

        // Get all available menu items that can be assigned to this menu
        $availableItems = $this->menuItemRepository->findAllOrdered();

        return $this->render('admin/menu/organize.html.twig', [
            'menu' => $menu,
            'menuItems' => $menuItems,
            'availableItems' => $availableItems,
        ]);
    }

    #[Route('/{id}/update-order', name: 'admin_menu_update_order', methods: ['POST'])]
    public function updateOrder(Menu $menu, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['items']) || !is_array($data['items'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid data format'], 400);
        }

        try {
            $this->entityManager->beginTransaction();

            foreach ($data['items'] as $index => $itemData) {
                if (!isset($itemData['id'])) {
                    continue;
                }

                $menuItem = $this->menuItemRepository->find($itemData['id']);
                if (!$menuItem) {
                    continue;
                }

                // Update menu assignment and sort order
                $menuItem->setMenu($menu);
                $menuItem->setSortOrder($index * 10); // Use increments of 10 for easy reordering
                $menuItem->setParent(null); // Clear parent for top-level items

                // Handle children if present
                if (isset($itemData['children']) && is_array($itemData['children'])) {
                    foreach ($itemData['children'] as $childIndex => $childData) {
                        if (!isset($childData['id'])) {
                            continue;
                        }

                        $childItem = $this->menuItemRepository->find($childData['id']);
                        if ($childItem) {
                            $childItem->setMenu($menu);
                            $childItem->setSortOrder($childIndex * 10);
                            $childItem->setParent($menuItem);
                        }
                    }
                }

                $this->entityManager->persist($menuItem);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(['success' => true, 'message' => 'Menu order updated successfully']);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return new JsonResponse(['success' => false, 'message' => 'Error updating menu order: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/item/{id}/update', name: 'admin_menu_item_update', methods: ['POST'])]
    public function updateMenuItem(MenuItem $menuItem, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            if (isset($data['menu'])) {
                $menu = $this->menuRepository->find($data['menu']);
                if ($menu) {
                    $menuItem->setMenu($menu);
                }
            }

            if (isset($data['parent'])) {
                $parent = $data['parent'] ? $this->menuItemRepository->find($data['parent']) : null;
                $menuItem->setParent($parent);
            }

            if (isset($data['sortOrder'])) {
                $menuItem->setSortOrder($data['sortOrder']);
            }

            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Menu item updated successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error updating menu item: ' . $e->getMessage()], 500);
        }
    }
}