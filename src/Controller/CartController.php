<?php

namespace App\Controller;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    #[Route('/{_locale}/cart', name: 'app_cart_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function index(Request $request, ?string $_locale = null): Response
    {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $cart = $this->getCart($request);

        // Debug logging
        $session = $request->getSession();
        error_log('Cart page - Session ID: ' . $session->getId());
        error_log('Cart page - Cart ID from session: ' . ($session->get('cart_id') ?? 'none'));
        error_log('Cart page - Cart found: ' . ($cart ? 'yes (ID: ' . $cart->getId() . ')' : 'no'));
        if ($cart) {
            error_log('Cart page - Item count: ' . $cart->getItems()->count());
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * API endpoint to get cart item count for badge display
     */
    #[Route('/api/cart/count', name: 'api_cart_count', methods: ['GET'])]
    public function getCartCount(Request $request): JsonResponse
    {
        try {
            $cart = $this->getCart($request);
            $count = 0;

            if ($cart) {
                $count = $cart->getItems()->count();
            }

            return $this->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            // Log error but return valid JSON to prevent breaking the UI
            error_log('Error getting cart count: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'count' => 0,
                'error' => 'Failed to retrieve cart count'
            ]);
        }
    }

    private function getCart(Request $request): ?Cart
    {
        $session = $request->getSession();
        $session->start(); // Ensure session is started
        $sessionId = $session->getId();

        // Try to find cart by cart_id in session first
        $cartId = $session->get('cart_id');
        if ($cartId) {
            $cart = $this->em->getRepository(Cart::class)->find($cartId);
            if ($cart) {
                return $cart;
            }
        }

        // Try to find cart by user
        $user = $this->getUser();
        if ($user) {
            $cart = $this->em->getRepository(Cart::class)
                ->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
            if ($cart) {
                return $cart;
            }
        }

        // Try to find cart by session
        return $this->em->getRepository(Cart::class)
            ->findOneBy(['sessionId' => $sessionId]);
    }
}
