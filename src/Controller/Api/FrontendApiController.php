<?php

namespace App\Controller\Api;

use App\Repository\MenuRepository;
use App\Repository\LanguageRepository;
use App\Repository\PageRepository;
use App\Repository\CustomerReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/public-api')]
class FrontendApiController extends AbstractController
{
    #[Route('/menus/{position}', name: 'api_menu_by_position', methods: ['GET'])]
    public function getMenuByPosition(
        string $position,
        MenuRepository $menuRepository
    ): JsonResponse {
        $menu = $menuRepository->findOneBy([
            'position' => $position,
            'isActive' => true
        ]);

        if (!$menu) {
            return $this->json(['items' => []]);
        }

        $items = [];
        foreach ($menu->getMenuItems() as $menuItem) {
            if ($menuItem->isActive() && !$menuItem->getParent()) {
                $items[] = $this->serializeMenuItem($menuItem);
            }
        }

        return $this->json([
            'name' => $menu->getName(),
            'identifier' => $menu->getIdentifier(),
            'position' => $menu->getPosition(),
            'items' => $items
        ]);
    }

    #[Route('/menus', name: 'api_menus_all', methods: ['GET'])]
    public function getAllMenus(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findBy(['isActive' => true]);
        $result = [];

        foreach ($menus as $menu) {
            $items = [];
            foreach ($menu->getMenuItems() as $menuItem) {
                if ($menuItem->isActive() && !$menuItem->getParent()) {
                    $items[] = $this->serializeMenuItem($menuItem);
                }
            }

            $result[$menu->getPosition()] = [
                'name' => $menu->getName(),
                'identifier' => $menu->getIdentifier(),
                'position' => $menu->getPosition(),
                'items' => $items
            ];
        }

        return $this->json($result);
    }

    #[Route('/languages', name: 'api_languages', methods: ['GET'])]
    public function getLanguages(LanguageRepository $languageRepository): JsonResponse
    {
        $languages = $languageRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC']);
        $result = [];

        foreach ($languages as $language) {
            $result[] = [
                'code' => $language->getCode(),
                'name' => $language->getName(),
                'nativeName' => $language->getNativeName(),
                'flagEmoji' => $language->getFlagEmoji(),
                'isDefault' => $language->isDefault(),
                'urlPath' => $language->getUrlPath(),
            ];
        }

        return $this->json($result);
    }

    #[Route('/pages/home', name: 'api_page_home', methods: ['GET'])]
    public function getHomePage(PageRepository $pageRepository): JsonResponse
    {
        $homePage = $pageRepository->findOneBy(['isHomePage' => true, 'isPublished' => true]);

        if (!$homePage) {
            return $this->json(['error' => 'No homepage found'], 404);
        }

        return $this->json([
            'id' => $homePage->getId(),
            'title' => $homePage->getTitle(),
            'slug' => $homePage->getSlug(),
            'content' => $homePage->getContent(),
            'template' => $homePage->getTemplate(),
            'metaTitle' => $homePage->getMetaTitle(),
            'metaDescription' => $homePage->getMetaDescription(),
        ]);
    }

    #[Route('/customer-reviews', name: 'api_customer_reviews', methods: ['GET'])]
    public function getCustomerReviews(
        Request $request,
        CustomerReviewRepository $reviewRepository
    ): JsonResponse {
        $limit = $request->query->getInt('limit', 6);
        $limit = min($limit, 50); // Max 50 reviews

        $reviews = $reviewRepository->findLatestFiveStarReviews($limit);
        $result = [];

        foreach ($reviews as $review) {
            $result[] = [
                'id' => $review->getId(),
                'customerName' => $review->getCustomerName(),
                'customerTitle' => $review->getCustomerTitle(),
                'customerLocation' => $review->getCustomerLocation(),
                'customerImage' => $review->getCustomerImage(),
                'reviewText' => $review->getReviewText(),
                'rating' => $review->getRating(),
                'isVerified' => $review->isVerified(),
                'isFeatured' => $review->isFeatured(),
                'product' => $review->getProduct() ? [
                    'id' => $review->getProduct()->getId(),
                    'name' => $review->getProduct()->getName(),
                    'slug' => $review->getProduct()->getSlug(),
                ] : null,
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($result);
    }

    #[Route('/translations', name: 'api_translations', methods: ['GET'])]
    public function getTranslations(
        Request $request,
        TranslatorInterface $translator
    ): JsonResponse {
        $locale = $request->query->get('locale', $request->getLocale());

        // Define all translation keys needed for the frontend
        $translationKeys = [
            // StartPage
            'startpage.hero.welcome',
            'startpage.hero.title',
            'startpage.hero.subtitle',
            'startpage.hero.admin_dashboard',
            'startpage.hero.documentation',
            'startpage.features.title',
            'startpage.features.subtitle',
            'startpage.features.multilanguage.title',
            'startpage.features.multilanguage.description',
            'startpage.features.modern_tech.title',
            'startpage.features.modern_tech.description',
            'startpage.features.content_management.title',
            'startpage.features.content_management.description',
            'startpage.features.ecommerce.title',
            'startpage.features.ecommerce.description',
            'startpage.features.user_management.title',
            'startpage.features.user_management.description',
            'startpage.features.seo.title',
            'startpage.features.seo.description',
            'startpage.stats.languages',
            'startpage.stats.payment_gateways',
            'startpage.stats.themes',
            'startpage.stats.api_endpoints',
            'startpage.reviews.title',
            'startpage.reviews.subtitle',
            'startpage.reviews.rating',
            'startpage.reviews.verified',
            'startpage.reviews.featured',
            'startpage.reviews.no_reviews',
            'startpage.reviews.loading',
            'startpage.recent_content.title',
            'startpage.recent_content.subtitle',
            'startpage.recent_content.recent_posts',
            'startpage.recent_content.recent_pages',

            // Reviews
            'reviews.verified_customer',
            'reviews.featured_review',

            // Menu
            'menu.home',
            'menu.products',
            'menu.cart',
            'menu.checkout',
            'menu.admin',
            'menu.language',

            // Cart
            'cart.title',
            'cart.empty',
            'cart.empty_message',
            'cart.continue_shopping',
            'cart.item',
            'cart.price',
            'cart.quantity',
            'cart.total',
            'cart.subtotal',
            'cart.remove',
            'cart.update',
            'cart.proceed_to_checkout',

            // Checkout
            'checkout.title',
            'checkout.billing_address',
            'checkout.shipping_address',
            'checkout.different_shipping',
            'checkout.payment_method',
            'checkout.shipping_method',
            'checkout.order_summary',
            'checkout.items',
            'checkout.shipping',
            'checkout.payment_fee',
            'checkout.total',
            'checkout.place_order',
            'checkout.processing',
            'checkout.fields.title',
            'checkout.fields.first_name',
            'checkout.fields.last_name',
            'checkout.fields.email',
            'checkout.fields.phone',
            'checkout.fields.address',
            'checkout.fields.address_line2',
            'checkout.fields.city',
            'checkout.fields.postal_code',
            'checkout.fields.country',
            'checkout.fields.notes',
            'checkout.fields.notes_placeholder',
            'checkout.create_account',
            'checkout.account_password',

            // Products
            'products.title',
            'products.subtitle',
            'products.featured',
            'products.in_stock',
            'products.out_of_stock',
            'products.view_details',
            'products.back_to_all',
        ];

        $translations = [];
        foreach ($translationKeys as $key) {
            $translations[$key] = $translator->trans($key, [], 'messages', $locale);
        }

        return $this->json($translations);
    }

    private function serializeMenuItem($menuItem): array
    {
        $data = [
            'id' => $menuItem->getId(),
            'title' => $menuItem->getTitle(),
            'url' => $menuItem->getEffectiveUrl(),
            'icon' => $menuItem->getIcon(),
            'cssClass' => $menuItem->getCssClass(),
            'openInNewTab' => $menuItem->isOpenInNewTab(),
            'children' => []
        ];

        foreach ($menuItem->getChildren() as $child) {
            if ($child->isActive()) {
                $data['children'][] = $this->serializeMenuItem($child);
            }
        }

        return $data;
    }
}
