<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use App\Entity\Store;
use App\Entity\User;
use App\Security\CartSessionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class CheckoutController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private CartSessionValidator $validator
    ) {
    }

    #[Route('/checkout', name: 'app_checkout')]
    #[Route('/{_locale}/checkout', name: 'app_checkout_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function index(Request $request, ?string $_locale = null): Response
    {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $cart = $this->getCart($request);

        if (!$cart || $cart->getItems()->isEmpty()) {
            $this->addFlash('warning', 'Your cart is empty');
            $routeName = $locale !== 'en' ? 'app_products_locale' : 'app_products';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        $paymentMethods = $this->em->getRepository(PaymentMethod::class)
            ->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        $shippingMethods = $this->em->getRepository(ShippingMethod::class)
            ->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        // Get user data for pre-filling checkout form
        $user = $this->getUser();
        $billingAddress = null;
        $shippingAddress = null;

        if ($user instanceof User) {
            // Get user's default billing address, or first billing/personal address
            $billingAddress = $this->em->getRepository(Address::class)
                ->findOneBy(['user' => $user, 'type' => 'billing', 'isDefault' => true]);

            if (!$billingAddress) {
                $billingAddress = $this->em->getRepository(Address::class)
                    ->findOneBy(['user' => $user, 'type' => 'billing']);
            }

            if (!$billingAddress) {
                $billingAddress = $this->em->getRepository(Address::class)
                    ->findOneBy(['user' => $user, 'type' => 'personal', 'isDefault' => true]);
            }

            if (!$billingAddress) {
                $billingAddress = $this->em->getRepository(Address::class)
                    ->findOneBy(['user' => $user, 'type' => 'personal']);
            }

            // Get user's default shipping address
            $shippingAddress = $this->em->getRepository(Address::class)
                ->findOneBy(['user' => $user, 'type' => 'shipping', 'isDefault' => true]);

            if (!$shippingAddress) {
                $shippingAddress = $this->em->getRepository(Address::class)
                    ->findOneBy(['user' => $user, 'type' => 'shipping']);
            }
        }

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
        ]);
    }

    #[Route('/checkout/process', name: 'app_checkout_process', methods: ['POST'])]
    #[Route('/{_locale}/checkout/process', name: 'app_checkout_process_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'], methods: ['POST'])]
    public function process(Request $request, LoggerInterface $logger, ?string $_locale = null): Response
    {
        $logger->info('=== CHECKOUT PROCESS STARTED ===');
        $logger->info('Request method: ' . $request->getMethod());
        $logger->info('Request URI: ' . $request->getRequestUri());

        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $logger->info('Locale: ' . $locale);

        // Verify CSRF token
        $submittedToken = $request->request->get('_csrf_token');
        $logger->info('CSRF token check - Submitted: ' . ($submittedToken ? 'present' : 'missing'));

        if (!$this->isCsrfTokenValid('checkout', $submittedToken)) {
            $logger->error('❌ CSRF VALIDATION FAILED');
            $this->addFlash('error', 'Invalid security token. Please try again.');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            $logger->info('Redirecting to: ' . $routeName . ' with params: ' . json_encode($params));
            return $this->redirectToRoute($routeName, $params);
        }
        $logger->info('✓ CSRF validation passed');

        // Check honeypot field (spam protection)
        $honeypot = $request->request->get('website');

        if (!$this->validator->validateHoneypot($honeypot)) {
            $this->addFlash('error', 'Your submission was flagged as spam. Please try again.');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Rate limiting for checkout submissions
        $clientIp = $request->getClientIp();
        if (!$this->validator->checkRateLimit('checkout_' . $clientIp, 5, 300)) {
            $this->addFlash('error', 'Too many checkout attempts. Please wait a few minutes and try again.');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        $cart = $this->getCart($request);

        if (!$cart || $cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Your cart is empty');
            $routeName = $locale !== 'en' ? 'app_products_locale' : 'app_products';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Get customer information and sanitize
        $title = $this->validator->sanitizeInput($request->request->get('title', ''));
        $firstName = $this->validator->sanitizeInput($request->request->get('first_name', ''));
        $lastName = $this->validator->sanitizeInput($request->request->get('last_name', ''));
        $email = $this->validator->sanitizeInput($request->request->get('email', ''));
        $phone = $this->validator->sanitizeInput($request->request->get('phone', ''));

        // Get billing address and sanitize
        $billingAddress = $this->validator->sanitizeInput($request->request->get('billing_address', ''));
        $billingAddressLine2 = $this->validator->sanitizeInput($request->request->get('billing_address_line2', ''));
        $billingCity = $this->validator->sanitizeInput($request->request->get('billing_city', ''));
        $billingPostalCode = $this->validator->sanitizeInput($request->request->get('billing_postal_code', ''));
        $billingCountry = $this->validator->sanitizeInput($request->request->get('billing_country', ''));

        // Check if shipping to different address
        $differentShipping = $request->request->get('different_shipping');

        // Get shipping address (use billing if not different)
        if ($differentShipping) {
            $shippingAddress = $this->validator->sanitizeInput($request->request->get('shipping_address', ''));
            $shippingAddressLine2 = $this->validator->sanitizeInput($request->request->get('shipping_address_line2', ''));
            $shippingCity = $this->validator->sanitizeInput($request->request->get('shipping_city', ''));
            $shippingPostalCode = $this->validator->sanitizeInput($request->request->get('shipping_postal_code', ''));
            $shippingCountry = $this->validator->sanitizeInput($request->request->get('shipping_country', ''));
        } else {
            $shippingAddress = $billingAddress;
            $shippingAddressLine2 = $billingAddressLine2;
            $shippingCity = $billingCity;
            $shippingPostalCode = $billingPostalCode;
            $shippingCountry = $billingCountry;
        }

        // Get payment and shipping method IDs
        $paymentMethodId = $request->request->get('payment_method_id');
        $shippingMethodId = $request->request->get('shipping_method_id');
        $pickupStoreId = $request->request->get('pickup_store_id');
        $notes = $this->validator->sanitizeInput($request->request->get('notes', ''), true); // Allow newlines in notes

        // Get account creation fields
        $createAccount = $request->request->get('create_account');
        $password = $request->request->get('password');
        $passwordConfirm = $request->request->get('password_confirm');

        // Validate required fields (title and phone are optional)
        if (!$firstName || !$lastName || !$email ||
            !$billingAddress || !$billingCity || !$billingPostalCode || !$billingCountry) {
            $this->addFlash('error', 'Please fill in all required fields');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Validate email format
        if (!$this->validator->isValidEmail($email)) {
            $this->addFlash('error', 'Invalid email address');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Validate phone format (only if provided)
        if ($phone && !$this->validator->isValidPhone($phone)) {
            $this->addFlash('error', 'Invalid phone number. Please use format: +49 123 456789 or 0123456789');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Validate postal code
        if (!$this->validator->isValidPostalCode($billingPostalCode, $billingCountry)) {
            $this->addFlash('error', 'Invalid postal code for selected country');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Check for SQL injection patterns in all inputs
        $fieldsToCheck = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'billing_address' => $billingAddress,
            'billing_city' => $billingCity,
            'notes' => $notes,
        ];

        foreach ($fieldsToCheck as $fieldName => $value) {
            if ($this->validator->hasSqlInjectionPatterns($value)) {
                $this->addFlash('error', 'Invalid characters detected in your input. Please check your information.');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }
        }

        // Check for spam in notes field
        if ($notes && $this->validator->isSpam($notes)) {
            $this->addFlash('error', 'Your order notes appear to contain spam. Please remove any suspicious content.');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Verify reCAPTCHA if enabled
        $recaptchaEnabled = $_SERVER['RECAPTCHA_ENABLED'] ?? 'false';
        if ($recaptchaEnabled === 'true') {
            $recaptchaToken = $request->request->get('recaptcha_token');
            $recaptchaSecret = $_SERVER['RECAPTCHA_SECRET_KEY'] ?? '';

            if (!$this->validator->verifyRecaptcha($recaptchaToken, $recaptchaSecret)) {
                $this->addFlash('error', 'reCAPTCHA verification failed. Please try again.');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }
        }

        // Validate shipping address if different
        if ($differentShipping && (!$shippingAddress || !$shippingCity || !$shippingPostalCode || !$shippingCountry)) {
            $this->addFlash('error', 'Please fill in all required shipping address fields');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Get payment and shipping methods
        $paymentMethod = $this->em->getRepository(PaymentMethod::class)->find($paymentMethodId);
        $shippingMethod = $this->em->getRepository(ShippingMethod::class)->find($shippingMethodId);

        if (!$paymentMethod || !$shippingMethod) {
            $this->addFlash('error', 'Invalid payment or shipping method');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Validate store selection for pickup shipping methods
        if ($shippingMethod->isRequiresStoreSelection() && !$pickupStoreId) {
            $this->addFlash('error', 'Please select a pickup store for this shipping method');
            $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
        }

        // Get pickup store if required
        $pickupStore = null;
        if ($pickupStoreId) {
            $pickupStore = $this->em->getRepository(Store::class)->find($pickupStoreId);
            if (!$pickupStore || !$pickupStore->isActive()) {
                $this->addFlash('error', 'Invalid pickup store selected');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }
        }

        // Handle account creation
        $newUser = null;
        if ($createAccount) {
            // Validate password fields
            if (!$password || !$passwordConfirm) {
                $this->addFlash('error', 'Password is required when creating an account');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Passwords do not match');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Password must be at least 8 characters long');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }

            // Check if email already exists
            $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'An account with this email already exists. Please log in or use a different email.');
                $routeName = $locale !== 'en' ? 'app_checkout_locale' : 'app_checkout';
            $params = $locale !== 'en' ? ['_locale' => $locale] : [];
            return $this->redirectToRoute($routeName, $params);
            }

            // Create new user
            $newUser = new User();

            // Generate username from email (use part before @)
            $username = explode('@', $email)[0];

            // Ensure username is unique by appending a number if needed
            $baseUsername = $username;
            $counter = 1;
            while ($this->em->getRepository(User::class)->findOneBy(['username' => $username])) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $newUser->setUsername($username);
            $newUser->setEmail($email);
            $newUser->setFirstName($firstName);
            $newUser->setLastName($lastName);
            $newUser->setPhone($phone);
            $newUser->setRoles(['ROLE_USER']);
            $newUser->setIsActive(true);

            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($newUser, $password);
            $newUser->setPassword($hashedPassword);

            $this->em->persist($newUser);

            // Create billing address for the new user
            $billingAddressEntity = new Address();
            $billingAddressEntity->setUser($newUser);
            $billingAddressEntity->setType('billing');
            $billingAddressEntity->setTitle($title);
            $billingAddressEntity->setFirstName($firstName);
            $billingAddressEntity->setLastName($lastName);
            $billingAddressEntity->setAddressLine1($billingAddress);
            $billingAddressEntity->setAddressLine2($billingAddressLine2);
            $billingAddressEntity->setCity($billingCity);
            $billingAddressEntity->setPostalCode($billingPostalCode);
            $billingAddressEntity->setCountry($billingCountry);
            $billingAddressEntity->setPhone($phone);
            $billingAddressEntity->setDefault(true);
            $this->em->persist($billingAddressEntity);

            // Check if shipping address is different from billing
            $shippingIsDifferent = (
                $shippingAddress !== $billingAddress ||
                $shippingCity !== $billingCity ||
                $shippingPostalCode !== $billingPostalCode ||
                $shippingCountry !== $billingCountry
            );

            if ($shippingIsDifferent) {
                // Create separate shipping address
                $shippingAddressEntity = new Address();
                $shippingAddressEntity->setUser($newUser);
                $shippingAddressEntity->setType('shipping');
                $shippingAddressEntity->setTitle($title);
                $shippingAddressEntity->setFirstName($firstName);
                $shippingAddressEntity->setLastName($lastName);
                $shippingAddressEntity->setAddressLine1($shippingAddress);
                $shippingAddressEntity->setAddressLine2($shippingAddressLine2);
                $shippingAddressEntity->setCity($shippingCity);
                $shippingAddressEntity->setPostalCode($shippingPostalCode);
                $shippingAddressEntity->setCountry($shippingCountry);
                $shippingAddressEntity->setPhone($phone);
                $shippingAddressEntity->setDefault(true);
                $this->em->persist($shippingAddressEntity);
            }
        }

        // Create order
        $order = new Order();
        $order->setOrderNumber($this->generateOrderNumber());

        // Customer information
        $order->setTitle($title);
        $order->setFirstName($firstName);
        $order->setLastName($lastName);
        // Build customer name with optional title
        $customerName = trim(($title ? $title . ' ' : '') . $firstName . ' ' . $lastName);
        $order->setCustomerName($customerName);
        $order->setEmail($email);
        $order->setPhone($phone);

        // Billing address
        $order->setBillingAddress($billingAddress);
        $order->setBillingAddressLine2($billingAddressLine2);
        $order->setBillingCity($billingCity);
        $order->setBillingPostcode($billingPostalCode);
        $order->setBillingCountry($billingCountry);

        // Shipping address
        $order->setShippingAddress($shippingAddress);
        $order->setShippingAddressLine2($shippingAddressLine2);
        $order->setShippingCity($shippingCity);
        $order->setShippingPostcode($shippingPostalCode);
        $order->setShippingCountry($shippingCountry);

        $order->setPaymentMethod($paymentMethod);
        $order->setShippingMethod($shippingMethod);

        // Set pickup store if applicable
        if ($pickupStore) {
            $order->setPickupStore($pickupStore);
        }

        $order->setCustomerNotes($notes);
        $order->setStatus('pending');

        // Link user to order (either newly created or existing logged-in user)
        if ($newUser) {
            $order->setCustomer($newUser);
        } elseif ($this->getUser()) {
            $order->setCustomer($this->getUser());
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setArticle($cartItem->getArticle());
            $orderItem->setArticleName($cartItem->getArticle()->getName());
            $orderItem->setArticleSku($cartItem->getArticle()->getSku());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getPrice());
            $orderItem->setSubtotal($cartItem->getSubtotal());
            $order->addItem($orderItem);
            $this->em->persist($orderItem);

            $subtotal += (float)$cartItem->getSubtotal();
        }

        $shippingCost = (float)$shippingMethod->getPrice();
        $paymentFee = (float)$paymentMethod->getFee();
        $totalAmount = $subtotal + $shippingCost + $paymentFee;

        $order->setSubtotal((string)$subtotal);
        $order->setShippingCost((string)$shippingCost);
        $order->setTotal((string)$totalAmount);

        $this->em->persist($order);

        // Clear cart
        foreach ($cart->getItems() as $item) {
            $this->em->remove($item);
        }
        $this->em->remove($cart);

        $this->em->flush();

        // Add success message if account was created
        if ($newUser) {
            $this->addFlash('success', 'Your order has been placed and your account has been created! Check your email for login details.');
        }

        // Check if payment method requires redirect to payment provider
        $paymentType = $paymentMethod->getType();
        $requiresPaymentRedirect = in_array($paymentType, [
            PaymentMethod::TYPE_STRIPE,
            PaymentMethod::TYPE_PAYPAL,
        ]);

        if ($requiresPaymentRedirect) {
            // Redirect to payment initiation
            $routeName = $locale !== 'en' ? 'app_payment_initiate_locale' : 'app_payment_initiate';
            $params = ['orderNumber' => $order->getOrderNumber()];
            if ($locale !== 'en') {
                $params['_locale'] = $locale;
            }
            return $this->redirectToRoute($routeName, $params);
        }

        // For non-redirect payment methods (prepayment, cash on delivery, at store)
        // Mark payment as pending and go directly to confirmation
        $order->setPaymentStatus('pending');
        $this->em->flush();

        $routeName = $locale !== 'en' ? 'app_checkout_confirmation_locale' : 'app_checkout_confirmation';
        $params = ['orderNumber' => $order->getOrderNumber()];
        if ($locale !== 'en') {
            $params['_locale'] = $locale;
        }
        return $this->redirectToRoute($routeName, $params);
    }

    #[Route('/checkout/confirmation/{orderNumber}', name: 'app_checkout_confirmation')]
    #[Route('/{_locale}/checkout/confirmation/{orderNumber}', name: 'app_checkout_confirmation_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function confirmation(string $orderNumber, Request $request, ?string $_locale = null): Response
    {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $order = $this->em->getRepository(Order::class)
            ->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        // Verify user owns this order (if logged in)
        if ($this->getUser() && $order->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
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

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -8));
    }
}
