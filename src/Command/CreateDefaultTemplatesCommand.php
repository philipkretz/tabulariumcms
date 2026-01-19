<?php

namespace App\Command;

use App\Entity\FrontendTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-default-templates',
    description: 'Create default frontend templates for e-commerce and user accounts'
)]
class CreateDefaultTemplatesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $templates = $this->getDefaultTemplates();
        $created = 0;
        $skipped = 0;

        foreach ($templates as $templateData) {
            // Check if template already exists
            $existing = $this->em->getRepository(FrontendTemplate::class)
                ->findOneBy(['templateKey' => $templateData['templateKey']]);

            if ($existing) {
                $io->warning(sprintf('Template "%s" already exists, skipping.', $templateData['templateKey']));
                $skipped++;
                continue;
            }

            $template = new FrontendTemplate();
            $template->setTemplateKey($templateData['templateKey']);
            $template->setName($templateData['name']);
            $template->setDescription($templateData['description']);
            $template->setContent($templateData['content']);
            $template->setAvailableVariables($templateData['availableVariables']);
            $template->setCategory($templateData['category']);
            $template->setIsActive(true);
            $template->setIsEditable(true);

            $this->em->persist($template);
            $created++;

            $io->success(sprintf('Created template: %s', $templateData['name']));
        }

        $this->em->flush();

        $io->success(sprintf('Created %d templates, skipped %d existing templates.', $created, $skipped));

        return Command::SUCCESS;
    }

    private function getDefaultTemplates(): array
    {
        return [
            // E-COMMERCE TEMPLATES
            [
                'templateKey' => 'cart_view',
                'name' => 'Shopping Cart View',
                'category' => 'ecommerce',
                'description' => 'Display shopping cart with items, quantities, and total',
                'availableVariables' => ['cart', 'items', 'total', 'user'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Shopping Cart{% endblock %}

{% block body %}
<div class="container py-5">
    <h1 class="mb-4">Shopping Cart</h1>

    {% if cart.items|length > 0 %}
        <div class="cart-items">
            {% for item in cart.items %}
                <div class="cart-item border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5>{{ item.displayName }}</h5>
                            {% if item.variant %}
                                <small class="text-muted">Variant: {{ item.variant }}</small>
                            {% endif %}
                        </div>
                        <div class="col-md-2">
                            <form method="post" action="{{ path('cart_update', {id: item.id}) }}" class="d-inline">
                                <input type="number" name="quantity" value="{{ item.quantity }}" min="1" class="form-control form-control-sm" style="width: 80px;">
                                <button type="submit" class="btn btn-sm btn-secondary mt-1">Update</button>
                            </form>
                        </div>
                        <div class="col-md-2 text-end">
                            <strong>{{ item.price|number_format(2, '.', ',') }} €</strong>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ path('cart_remove', {id: item.id}) }}" class="btn btn-sm btn-danger">Remove</a>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>

        <div class="cart-summary mt-4 p-4 bg-light">
            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <h4>Cart Total: {{ cart.total|number_format(2, '.', ',') }} €</h4>
                    <a href="{{ path('checkout') }}" class="btn btn-primary btn-lg w-100 mt-3">Proceed to Checkout</a>
                    <a href="{{ path('home') }}" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                </div>
            </div>
        </div>
    {% else %}
        <div class="alert alert-info">
            <h4>Your cart is empty</h4>
            <p>Start shopping to add items to your cart.</p>
            <a href="{{ path('shop') }}" class="btn btn-primary">Go to Shop</a>
        </div>
    {% endif %}
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'product_detail',
                'name' => 'Product Detail Page',
                'category' => 'ecommerce',
                'description' => 'Single product detail view with add to cart',
                'availableVariables' => ['product', 'variants', 'images', 'seller'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}{{ product.name }}{% endblock %}

{% block body %}
<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            {% if product.images|length > 0 %}
                <img src="{{ product.images[0].path }}" alt="{{ product.name }}" class="img-fluid">
            {% else %}
                <img src="/images/placeholder.png" alt="{{ product.name }}" class="img-fluid">
            {% endif %}
        </div>

        <div class="col-md-6">
            <h1>{{ product.name }}</h1>

            {% if product.seller %}
                <p class="text-muted">Sold by: <strong>{{ product.seller.companyName }}</strong></p>
            {% endif %}

            <div class="price mb-3">
                <h2 class="text-primary">{{ product.grossPrice|number_format(2, '.', ',') }} €</h2>
                {% if product.netPrice %}
                    <small class="text-muted">Net: {{ product.netPrice|number_format(2, '.', ',') }} € + {{ product.taxRate }}% VAT</small>
                {% endif %}
            </div>

            <div class="description mb-4">
                {{ product.description|raw }}
            </div>

            <form method="post" action="{{ path('cart_add', {id: product.id}) }}">
                {% if product.variants|length > 0 %}
                    <div class="mb-3">
                        <label class="form-label">Select Variant:</label>
                        <select name="variant_id" class="form-select" required>
                            {% for variant in product.variants %}
                                {% if variant.isActive %}
                                    <option value="{{ variant.id }}">
                                        {{ variant.name }}
                                        {% if variant.priceModifier %}
                                            ({{ variant.priceModifier > 0 ? '+' : '' }}{{ variant.priceModifier }} €)
                                        {% endif %}
                                    </option>
                                {% endif %}
                            {% endfor %}
                        </select>
                    </div>
                {% endif %}

                <div class="mb-3">
                    <label class="form-label">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="width: 100px;">
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'product_listing',
                'name' => 'Product Listing Page',
                'category' => 'ecommerce',
                'description' => 'Grid view of products with filters',
                'availableVariables' => ['products', 'categories', 'currentCategory'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Shop - Products{% endblock %}

{% block body %}
<div class="container py-5">
    <h1 class="mb-4">Products</h1>

    <div class="row">
        <div class="col-md-3">
            <h5>Categories</h5>
            <ul class="list-unstyled">
                <li><a href="{{ path('shop') }}">All Products</a></li>
                {% for category in categories %}
                    <li>
                        <a href="{{ path('shop_category', {id: category.id}) }}"
                           class="{{ currentCategory and currentCategory.id == category.id ? 'fw-bold' : '' }}">
                            {{ category.name }}
                        </a>
                    </li>
                {% endfor %}
            </ul>
        </div>

        <div class="col-md-9">
            <div class="row g-4">
                {% for product in products %}
                    <div class="col-md-4">
                        <div class="card h-100">
                            {% if product.images|length > 0 %}
                                <img src="{{ product.images[0].path }}" class="card-img-top" alt="{{ product.name }}">
                            {% else %}
                                <img src="/images/placeholder.png" class="card-img-top" alt="{{ product.name }}">
                            {% endif %}

                            <div class="card-body">
                                <h5 class="card-title">{{ product.name }}</h5>
                                <p class="card-text">{{ product.shortDescription }}</p>
                                <p class="h5 text-primary">{{ product.grossPrice|number_format(2, '.', ',') }} €</p>
                                <a href="{{ path('product_detail', {id: product.id}) }}" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                {% else %}
                    <div class="col-12">
                        <div class="alert alert-info">No products found.</div>
                    </div>
                {% endfor %}
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'checkout_form',
                'name' => 'Checkout Form',
                'category' => 'checkout',
                'description' => 'Checkout form with shipping, payment selection',
                'availableVariables' => ['cart', 'shippingMethods', 'paymentMethods', 'user'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Checkout{% endblock %}

{% block body %}
<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <form method="post" action="{{ path('checkout') }}">
        <div class="row">
            <div class="col-md-8">
                {% if not user %}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                        </div>
                    </div>
                {% endif %}

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <input type="text" name="shipping_address" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" name="shipping_city" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postcode *</label>
                                <input type="text" name="shipping_postcode" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Country *</label>
                            <select name="shipping_country" class="form-select" required>
                                <option value="DE">Germany</option>
                                <option value="AT">Austria</option>
                                <option value="CH">Switzerland</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Shipping Method</h5>
                    </div>
                    <div class="card-body">
                        {% for method in shippingMethods %}
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="shipping_method"
                                       value="{{ method.id }}" id="ship{{ method.id }}" required>
                                <label class="form-check-label" for="ship{{ method.id }}">
                                    <strong>{{ method.name }}</strong> - {{ method.price|number_format(2, '.', ',') }} €
                                    <br><small class="text-muted">{{ method.deliveryTime }}</small>
                                </label>
                            </div>
                        {% endfor %}
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        {% for method in paymentMethods %}
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method"
                                       value="{{ method.id }}" id="pay{{ method.id }}" required>
                                <label class="form-check-label" for="pay{{ method.id }}">
                                    <strong>{{ method.name }}</strong>
                                    {% if method.fee > 0 %}
                                        + {{ method.fee|number_format(2, '.', ',') }} € fee
                                    {% endif %}
                                    <br><small class="text-muted">{{ method.description }}</small>
                                </label>
                            </div>
                        {% endfor %}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        {% for item in cart.items %}
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ item.displayName }} x{{ item.quantity }}</span>
                                <span>{{ item.subtotal|number_format(2, '.', ',') }} €</span>
                            </div>
                        {% endfor %}
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>{{ cart.total|number_format(2, '.', ',') }} €</strong>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'order_confirmation',
                'name' => 'Order Confirmation',
                'category' => 'checkout',
                'description' => 'Order confirmation page after successful checkout',
                'availableVariables' => ['order', 'items', 'customer'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Order Confirmation{% endblock %}

{% block body %}
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="text-success">Order Confirmed!</h1>
        <p class="lead">Thank you for your order</p>
        <p>Order Number: <strong>{{ order.orderNumber }}</strong></p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Customer Information</h5>
                </div>
                <div class="card-body">
                    {% if order.customer %}
                        <p><strong>Name:</strong> {{ order.customer.firstName }} {{ order.customer.lastName }}</p>
                        <p><strong>Email:</strong> {{ order.customer.email }}</p>
                    {% else %}
                        <p><strong>Name:</strong> {{ order.guestName }}</p>
                        <p><strong>Email:</strong> {{ order.guestEmail }}</p>
                    {% endif %}
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p>{{ order.shippingAddress }}</p>
                    <p>{{ order.shippingPostcode }} {{ order.shippingCity }}</p>
                    <p>{{ order.shippingCountry }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Order Details</h5>
                </div>
                <div class="card-body">
                    {% for item in order.items %}
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ item.articleName }} x{{ item.quantity }}</span>
                            <span>{{ item.subtotal|number_format(2, '.', ',') }} €</span>
                        </div>
                    {% endfor %}
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>{{ order.subtotal|number_format(2, '.', ',') }} €</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Shipping:</span>
                        <span>{{ order.shippingCost|number_format(2, '.', ',') }} €</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tax:</span>
                        <span>{{ order.taxAmount|number_format(2, '.', ',') }} €</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-primary">{{ order.total|number_format(2, '.', ',') }} €</strong>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h6>What's Next?</h6>
                <p>You will receive an order confirmation email at {{ order.customer ? order.customer.email : order.guestEmail }}</p>
                <p>We'll notify you when your order ships.</p>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            // USER ACCOUNT TEMPLATES
            [
                'templateKey' => 'user_login',
                'name' => 'User Login Page',
                'category' => 'account',
                'description' => 'User login form',
                'availableVariables' => ['error', 'last_username'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Login{% endblock %}

{% block body %}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Login</h3>
                </div>
                <div class="card-body">
                    {% if error %}
                        <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                    {% endif %}

                    <form method="post" action="{{ path('app_login') }}">
                        <div class="mb-3">
                            <label for="username" class="form-label">Email</label>
                            <input type="email" class="form-control" id="username" name="_username"
                                   value="{{ last_username }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="_password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="_remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>

                        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </form>

                    <hr>
                    <div class="text-center">
                        <p>Don't have an account? <a href="{{ path('app_register') }}">Register here</a></p>
                        <p><a href="{{ path('app_forgot_password') }}">Forgot your password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'user_register',
                'name' => 'User Registration Page',
                'category' => 'account',
                'description' => 'User registration form',
                'availableVariables' => ['form'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Register{% endblock %}

{% block body %}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Create Account</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ path('app_register') }}">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="passwordConfirm" class="form-control" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="{{ path('terms') }}" target="_blank">Terms & Conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>

                    <hr>
                    <div class="text-center">
                        <p>Already have an account? <a href="{{ path('app_login') }}">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'user_profile',
                'name' => 'User Profile View',
                'category' => 'account',
                'description' => 'User profile page displaying account information',
                'availableVariables' => ['user', 'orders'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}My Profile{% endblock %}

{% block body %}
<div class="container py-5">
    <h1 class="mb-4">My Account</h1>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ path('user_profile') }}" class="list-group-item list-group-item-action active">Profile</a>
                <a href="{{ path('user_orders') }}" class="list-group-item list-group-item-action">My Orders</a>
                <a href="{{ path('user_addresses') }}" class="list-group-item list-group-item-action">Addresses</a>
                <a href="{{ path('user_settings') }}" class="list-group-item list-group-item-action">Settings</a>
                <a href="{{ path('app_logout') }}" class="list-group-item list-group-item-action text-danger">Logout</a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Profile Information</h5>
                    <a href="{{ path('user_profile_edit') }}" class="btn btn-sm btn-primary">Edit Profile</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Name:</strong></div>
                        <div class="col-md-8">{{ user.firstName }} {{ user.lastName }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Email:</strong></div>
                        <div class="col-md-8">{{ user.email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Phone:</strong></div>
                        <div class="col-md-8">{{ user.phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Member Since:</strong></div>
                        <div class="col-md-8">{{ user.createdAt|date('F d, Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    {% if orders|length > 0 %}
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for order in orders|slice(0, 5) %}
                                    <tr>
                                        <td>{{ order.orderNumber }}</td>
                                        <td>{{ order.createdAt|date('Y-m-d') }}</td>
                                        <td>{{ order.total|number_format(2, '.', ',') }} €</td>
                                        <td><span class="badge bg-info">{{ order.status }}</span></td>
                                        <td><a href="{{ path('order_detail', {id: order.id}) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                        <a href="{{ path('user_orders') }}" class="btn btn-link">View All Orders</a>
                    {% else %}
                        <p>You haven't placed any orders yet.</p>
                        <a href="{{ path('shop') }}" class="btn btn-primary">Start Shopping</a>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'user_profile_edit',
                'name' => 'User Profile Edit',
                'category' => 'account',
                'description' => 'Edit user profile information',
                'availableVariables' => ['user', 'form'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Edit Profile{% endblock %}

{% block body %}
<div class="container py-5">
    <h1 class="mb-4">Edit Profile</h1>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ path('user_profile_update') }}">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstName" class="form-control"
                                   value="{{ user.firstName }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastName" class="form-control"
                                   value="{{ user.lastName }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ user.email }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ user.phone }}">
                        </div>

                        <hr class="my-4">

                        <h5>Change Password</h5>
                        <p class="text-muted">Leave blank to keep current password</p>

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="currentPassword" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="newPassword" class="form-control" minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirmPassword" class="form-control">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ path('user_profile') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            // SELLER TEMPLATES
            [
                'templateKey' => 'seller_register',
                'name' => 'Seller Registration',
                'category' => 'seller',
                'description' => 'Seller/vendor registration form',
                'availableVariables' => ['form'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Become a Seller{% endblock %}

{% block body %}
<div class="container py-5">
    <div class="text-center mb-5">
        <h1>Become a Seller</h1>
        <p class="lead">Start selling your products on our platform</p>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ path('seller_register') }}">
                        <h5 class="mb-3">Company Information</h5>

                        <div class="mb-3">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="companyName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business/Trading Name</label>
                            <input type="text" name="businessName" class="form-control">
                            <small class="text-muted">If different from company name</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>

                        <h5 class="mt-4 mb-3">Contact Information</h5>

                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" placeholder="https://">
                        </div>

                        <h5 class="mt-4 mb-3">Business Address</h5>

                        <div class="mb-3">
                            <label class="form-label">Street Address *</label>
                            <input type="text" name="businessAddress" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" name="businessCity" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postcode *</label>
                                <input type="text" name="businessPostcode" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Country *</label>
                            <select name="businessCountry" class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="DE">Germany</option>
                                <option value="AT">Austria</option>
                                <option value="CH">Switzerland</option>
                            </select>
                        </div>

                        <h5 class="mt-4 mb-3">Tax & Legal</h5>

                        <div class="mb-3">
                            <label class="form-label">Tax ID / VAT Number *</label>
                            <input type="text" name="taxId" class="form-control" required>
                        </div>

                        <h5 class="mt-4 mb-3">Bank Account (for payouts)</h5>

                        <div class="mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bankName" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">IBAN</label>
                            <input type="text" name="bankIban" class="form-control">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="{{ path('seller_terms') }}" target="_blank">Seller Terms & Conditions</a>
                                and understand that a {{ commissionRate ?? 10 }}% platform commission applies to all sales.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Submit Application</button>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h6>What happens next?</h6>
                <p>Your seller application will be reviewed by our team. You'll receive an email notification once your account is approved, typically within 1-2 business days.</p>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],

            [
                'templateKey' => 'seller_dashboard',
                'name' => 'Seller Dashboard',
                'category' => 'seller',
                'description' => 'Seller account dashboard with stats and products',
                'availableVariables' => ['seller', 'products', 'orders', 'earnings'],
                'content' => <<<'TWIG'
{% extends 'base.html.twig' %}

{% block title %}Seller Dashboard{% endblock %}

{% block body %}
<div class="container-fluid py-4">
    <h1 class="mb-4">Seller Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Products</h6>
                    <h2>{{ products|length }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Pending Orders</h6>
                    <h2 class="text-warning">{{ orders|filter(o => o.status == 'pending')|length }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Sales</h6>
                    <h2 class="text-success">{{ earnings.totalSales|number_format(0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Revenue (after commission)</h6>
                    <h2 class="text-primary">{{ earnings.netRevenue|number_format(2) }} €</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>My Products</h5>
                    <a href="{{ path('seller_product_create') }}" class="btn btn-primary">Add New Product</a>
                </div>
                <div class="card-body">
                    {% if products|length > 0 %}
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for product in products %}
                                    <tr>
                                        <td>{{ product.name }}</td>
                                        <td>{{ product.grossPrice|number_format(2) }} €</td>
                                        <td>{{ product.stock }}</td>
                                        <td>
                                            <span class="badge {{ product.isActive ? 'bg-success' : 'bg-secondary' }}">
                                                {{ product.isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ path('seller_product_edit', {id: product.id}) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    {% else %}
                        <p>You haven't added any products yet.</p>
                        <a href="{{ path('seller_product_create') }}" class="btn btn-primary">Add Your First Product</a>
                    {% endif %}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Account Status</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong>
                        <span class="badge {{ seller.status == 'active' ? 'bg-success' : 'bg-warning' }}">
                            {{ seller.status|title }}
                        </span>
                    </p>
                    <p><strong>Commission Rate:</strong> {{ seller.commissionRate }}%</p>
                    <p><strong>Can Sell:</strong> {{ seller.canSellProducts ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ path('seller_products') }}" class="list-group-item list-group-item-action">Manage Products</a>
                    <a href="{{ path('seller_orders') }}" class="list-group-item list-group-item-action">View Orders</a>
                    <a href="{{ path('seller_profile') }}" class="list-group-item list-group-item-action">Edit Profile</a>
                    <a href="{{ path('seller_payouts') }}" class="list-group-item list-group-item-action">Payout History</a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG
            ],
        ];
    }
}
