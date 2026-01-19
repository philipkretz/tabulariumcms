<?php
namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Security\CartSessionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cart')]
class CartApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartSessionValidator $validator
    ) {}

    #[Route('', name: 'api_cart_get', methods: ['GET'])]
    public function getCart(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        return $this->json([
            'items' => $this->formatCartItems($cart),
            'totals' => [
                'subtotal' => $cart->getTotal(),
                'itemCount' => $cart->getItemCount(),
            ]
        ]);
    }

    #[Route('/add', name: 'api_cart_add', methods: ['POST'])]
    public function addItem(Request $request): JsonResponse
    {
        // Validate origin to prevent CSRF
        if (!$this->validator->validateOrigin($request)) {
            return $this->json(['error' => 'Invalid request origin'], 403);
        }

        // Rate limiting: max 30 add-to-cart requests per minute per IP
        $clientIp = $request->getClientIp();
        if (!$this->validator->checkRateLimit('cart_add_' . $clientIp, 30, 60)) {
            return $this->json(['error' => 'Too many requests. Please try again later.'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $articleId = $data['article_id'] ?? null;
        $quantity = max(1, min(100, (int)($data['quantity'] ?? 1))); // Max 100 items at once

        if (!$articleId || !is_numeric($articleId)) {
            return $this->json(['error' => 'Invalid article ID'], 400);
        }

        $article = $this->em->getRepository(Article::class)->find($articleId);
        if (!$article || !$article->isActive()) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        // Check stock
        if (!$article->getIgnoreStock() && $article->getStock() < $quantity) {
            return $this->json(['error' => 'Insufficient stock'], 400);
        }

        $cart = $this->getOrCreateCart($request);

        // Check if item already in cart
        $cartItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getArticle()->getId() === $article->getId()) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setArticle($article);
            $cartItem->setQuantity($quantity);
            $cartItem->setPrice($article->getGrossPrice());
            $cart->addItem($cartItem);
            $this->em->persist($cartItem);
        }

        $this->em->flush();

        // Store cart ID in session for debugging
        $session = $request->getSession();
        $session->set('cart_id', $cart->getId());
        $session->save();

        return $this->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart' => [
                'items' => $this->formatCartItems($cart),
                'totals' => [
                    'subtotal' => $cart->getTotal(),
                    'itemCount' => $cart->getItemCount(),
                ]
            ]
        ]);
    }

    #[Route('/update/{itemId}', name: 'api_cart_update', methods: ['PUT'])]
    public function updateItem(int $itemId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = max(1, (int)($data['quantity'] ?? 1));

        $cartItem = $this->em->getRepository(CartItem::class)->find($itemId);
        if (!$cartItem) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        // Verify ownership
        $cart = $this->getOrCreateCart($request);
        if ($cartItem->getCart()->getId() !== $cart->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Check stock
        $article = $cartItem->getArticle();
        if (!$article->getIgnoreStock() && $article->getStock() < $quantity) {
            return $this->json(['error' => 'Insufficient stock'], 400);
        }

        $cartItem->setQuantity($quantity);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Quantity updated',
            'cart' => [
                'items' => $this->formatCartItems($cart),
                'totals' => [
                    'subtotal' => $cart->getTotal(),
                    'itemCount' => $cart->getItemCount(),
                ]
            ]
        ]);
    }

    #[Route('/remove/{itemId}', name: 'api_cart_remove', methods: ['DELETE'])]
    public function removeItem(int $itemId, Request $request): JsonResponse
    {
        $cartItem = $this->em->getRepository(CartItem::class)->find($itemId);
        if (!$cartItem) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        // Verify ownership
        $cart = $this->getOrCreateCart($request);
        if ($cartItem->getCart()->getId() !== $cart->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $cart->removeItem($cartItem);
        $this->em->remove($cartItem);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => [
                'items' => $this->formatCartItems($cart),
                'totals' => [
                    'subtotal' => $cart->getTotal(),
                    'itemCount' => $cart->getItemCount(),
                ]
            ]
        ]);
    }

    private function getOrCreateCart(Request $request): Cart
    {
        $session = $request->getSession();
        $session->start(); // Ensure session is started
        $sessionId = $session->getId();

        error_log('API - Session ID: ' . $sessionId);

        // Try to find cart by cart_id in session first (for consistency with CartController)
        $cartId = $session->get('cart_id');
        if ($cartId) {
            $cart = $this->em->getRepository(Cart::class)->find($cartId);
            if ($cart) {
                error_log('API - Found cart by cart_id from session: ' . $cart->getId());
                // Ensure session is saved
                $session->save();
                return $cart;
            }
        }

        // Try to find cart by user
        $user = $this->getUser();
        if ($user) {
            $cart = $this->em->getRepository(Cart::class)
                ->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
            if ($cart) {
                error_log('API - Found cart by user: ' . $cart->getId());
                // Store cart ID in session for future requests
                $session->set('cart_id', $cart->getId());
                $session->save();
                return $cart;
            }
        }

        // Try to find cart by session ID
        $cart = $this->em->getRepository(Cart::class)
            ->findOneBy(['sessionId' => $sessionId]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setSessionId($sessionId);
            if ($user) {
                $cart->setUser($user);
            }
            $this->em->persist($cart);
            $this->em->flush();
            error_log('API - Created new cart: ' . $cart->getId() . ' with session: ' . $sessionId);
            // Store cart ID in session
            $session->set('cart_id', $cart->getId());
            $session->save();
        } else {
            error_log('API - Found cart by session ID: ' . $cart->getId());
            // Store cart ID in session for future requests
            $session->set('cart_id', $cart->getId());
            $session->save();
        }

        return $cart;
    }

    private function formatCartItems(Cart $cart): array
    {
        $items = [];
        foreach ($cart->getItems() as $item) {
            $article = $item->getArticle();
            $items[] = [
                'id' => $item->getId(),
                'article' => [
                    'id' => $article->getId(),
                    'name' => $article->getName(),
                    'slug' => $article->getSlug(),
                    'sku' => $article->getSku(),
                    'image' => $article->getMainImage() ? '/uploads/media/' . $article->getMainImage()->getFilename() : null,
                ],
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'subtotal' => $item->getSubtotal(),
            ];
        }
        return $items;
    }
}
