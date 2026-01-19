<?php

namespace App\Command;

use App\Entity\EmailTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-email-templates',
    description: 'Load default email templates into the database'
)]
class LoadEmailTemplatesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $templates = [
            [
                'name' => 'User Registration',
                'slug' => 'user-registration',
                'subject' => 'Welcome to TabulariumCMS, {{user.name}}!',
                'bodyHtml' => $this->getUserRegistrationTemplate(),
                'description' => 'Sent when a new user registers an account'
            ],
            [
                'name' => 'Newsletter Confirmation',
                'slug' => 'newsletter-confirm',
                'subject' => 'Please confirm your newsletter subscription',
                'bodyHtml' => $this->getNewsletterConfirmTemplate(),
                'description' => 'Sent to confirm newsletter subscription'
            ],
            [
                'name' => 'Newsletter Welcome',
                'slug' => 'newsletter-welcome',
                'subject' => 'Welcome to our newsletter!',
                'bodyHtml' => $this->getNewsletterWelcomeTemplate(),
                'description' => 'Sent after newsletter subscription is confirmed'
            ],
            [
                'name' => 'Newsletter Unsubscribe',
                'slug' => 'newsletter-unsubscribe',
                'subject' => 'You have been unsubscribed',
                'bodyHtml' => $this->getNewsletterUnsubscribeTemplate(),
                'description' => 'Sent when user unsubscribes from newsletter'
            ],
            [
                'name' => 'Order Confirmation',
                'slug' => 'order-confirmation',
                'subject' => 'Order Confirmation #{{order.number}}',
                'bodyHtml' => $this->getOrderConfirmationTemplate(),
                'description' => 'Sent when a new order is placed'
            ],
            [
                'name' => 'Order Status Change',
                'slug' => 'order-status-change',
                'subject' => 'Order #{{order.number}} Status Update',
                'bodyHtml' => $this->getOrderStatusChangeTemplate(),
                'description' => 'Sent when an order status changes'
            ],
            [
                'name' => 'Test Email',
                'slug' => 'test-email',
                'subject' => 'SMTP Test Email - TabulariumCMS',
                'bodyHtml' => $this->getTestEmailTemplate(),
                'description' => 'Test email to verify SMTP configuration'
            ],
            [
                'name' => 'Admin - Low Stock Alert',
                'slug' => 'admin-stock-low',
                'subject' => 'Low Stock Alert: {{product.name}}',
                'bodyHtml' => $this->getStockLowTemplate(),
                'description' => 'Sent to admin when product stock is low or out of stock'
            ],
            [
                'name' => 'Admin - New User Registration',
                'slug' => 'admin-new-user',
                'subject' => 'New User Registered: {{user.email}}',
                'bodyHtml' => $this->getAdminNewUserTemplate(),
                'description' => 'Sent to admin when a new user registers'
            ],
            [
                'name' => 'Admin - Contact Form Submission',
                'slug' => 'admin-contact-form',
                'subject' => 'New Contact Form Submission',
                'bodyHtml' => $this->getAdminContactFormTemplate(),
                'description' => 'Sent to admin when contact form is submitted'
            ],
            [
                'name' => 'Admin - Comment Moderation Needed',
                'slug' => 'admin-comment-moderation',
                'subject' => 'Comment Awaiting Moderation',
                'bodyHtml' => $this->getAdminCommentModerationTemplate(),
                'description' => 'Sent to admin when a comment needs moderation'
            ],
            [
                'name' => 'Seller Registration',
                'slug' => 'seller-registration',
                'subject' => 'Welcome to TabulariumCMS Seller Program',
                'bodyHtml' => $this->getSellerRegistrationTemplate(),
                'description' => 'Sent when a seller registers'
            ],
            [
                'name' => 'Seller Approved',
                'slug' => 'seller-approved',
                'subject' => 'Your Seller Account Has Been Approved!',
                'bodyHtml' => $this->getSellerApprovedTemplate(),
                'description' => 'Sent when a seller is approved'
            ],
            [
                'name' => 'Seller Rejected',
                'slug' => 'seller-rejected',
                'subject' => 'Seller Application Update',
                'bodyHtml' => $this->getSellerRejectedTemplate(),
                'description' => 'Sent when a seller is rejected'
            ],
            [
                'name' => 'Admin - New Seller Registration',
                'slug' => 'admin-seller-registration',
                'subject' => 'New Seller Registration: {{seller.companyName}}',
                'bodyHtml' => $this->getAdminSellerRegistrationTemplate(),
                'description' => 'Sent to admin when a new seller registers'
            ],
            [
                'name' => 'Newsletter Campaign',
                'slug' => 'newsletter-campaign',
                'subject' => '{{campaign.subject}}',
                'bodyHtml' => $this->getNewsletterCampaignTemplate(),
                'description' => 'Template wrapper for newsletter campaigns'
            ],
        ];

        $count = 0;
        foreach ($templates as $templateData) {
            $existing = $this->em->getRepository(EmailTemplate::class)
                ->findOneBy(['slug' => $templateData['slug']]);

            if ($existing) {
                $io->note("Template '{$templateData['slug']}' already exists, skipping");
                continue;
            }

            $template = new EmailTemplate();
            $template->setName($templateData['name']);
            $template->setSlug($templateData['slug']);
            $template->setSubject($templateData['subject']);
            $template->setBodyHtml($templateData['bodyHtml']);
            $template->setDescription($templateData['description']);
            $template->setIsActive(true);

            $this->em->persist($template);
            $count++;
        }

        $this->em->flush();

        $io->success("Successfully loaded {$count} email templates");

        return Command::SUCCESS;
    }

    private function getUserRegistrationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Welcome to TabulariumCMS!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{user.name}}</strong>,</p>
        <p>Thank you for registering an account with us! We're excited to have you on board.</p>
        <p>You can now log in and start exploring all the features we have to offer.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{siteUrl}}" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Visit Website</a>
        </p>
        <p>If you have any questions, feel free to contact our support team.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getNewsletterConfirmTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Confirm Your Subscription</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello {{name}},</p>
        <p>Thank you for subscribing to our newsletter!</p>
        <p>Please click the button below to confirm your subscription:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{confirmUrl}}" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Confirm Subscription</a>
        </p>
        <p style="font-size: 12px; color: #666;">If you didn't subscribe to this newsletter, you can safely ignore this email.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getNewsletterWelcomeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Welcome to Our Newsletter!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello {{name}},</p>
        <p>Your subscription has been confirmed! Thank you for joining our newsletter.</p>
        <p>You'll now receive our latest updates, exclusive offers, and news directly in your inbox.</p>
        <p>We're committed to providing you with valuable content and respecting your privacy.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getNewsletterUnsubscribeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #666; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Unsubscribed</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello {{name}},</p>
        <p>You have been successfully unsubscribed from our newsletter.</p>
        <p>We're sorry to see you go. If you change your mind, you can always subscribe again.</p>
        <p>Thank you for being part of our community.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderConfirmationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Order Confirmed!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{customer.name}}</strong>,</p>
        <p>Thank you for your order! We've received your order and it's being processed.</p>

        <!-- Order Summary -->
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Order Number:</strong> #{{order.number}}</p>
            <p style="margin: 5px 0;"><strong>Order Date:</strong> {{order.createdAt}}</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> {{order.status}}</p>
        </div>

        <!-- Order Items -->
        <h2 style="color: #16a34a; border-bottom: 2px solid #16a34a; padding-bottom: 10px;">Order Items</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e5e7eb;">Product</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #e5e7eb;">Qty</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #e5e7eb;">Price</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #e5e7eb;">Total</th>
                </tr>
            </thead>
            <tbody>
                {% for item in order.items %}
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <strong>{{item.name}}</strong>
                        {% if item.sku %}<br><small style="color: #6b7280;">SKU: {{item.sku}}</small>{% endif %}
                    </td>
                    <td style="padding: 10px; text-align: center; border-bottom: 1px solid #e5e7eb;">{{item.quantity}}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;">€{{item.unitPrice}}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #e5e7eb;">€{{item.subtotal}}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>

        <!-- Order Totals -->
        <div style="background: #f9fafb; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px; text-align: right;"><strong>Subtotal:</strong></td>
                    <td style="padding: 5px; text-align: right; width: 120px;">€{{order.subtotal}}</td>
                </tr>
                <tr>
                    <td style="padding: 5px; text-align: right;"><strong>Shipping:</strong></td>
                    <td style="padding: 5px; text-align: right;">€{{order.shippingCost}}</td>
                </tr>
                {% if order.discount > 0 %}
                <tr>
                    <td style="padding: 5px; text-align: right; color: #16a34a;"><strong>Discount:</strong></td>
                    <td style="padding: 5px; text-align: right; color: #16a34a;">-€{{order.discount}}</td>
                </tr>
                {% endif %}
                <tr>
                    <td style="padding: 5px; text-align: right;"><strong>Tax:</strong></td>
                    <td style="padding: 5px; text-align: right;">€{{order.taxAmount}}</td>
                </tr>
                <tr style="border-top: 2px solid #16a34a;">
                    <td style="padding: 10px 5px 5px 5px; text-align: right; font-size: 18px;"><strong>Total:</strong></td>
                    <td style="padding: 10px 5px 5px 5px; text-align: right; font-size: 18px; color: #16a34a;"><strong>€{{order.total}}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Payment & Shipping Methods -->
        <div style="display: flex; gap: 20px; margin: 20px 0;">
            <div style="flex: 1; background: #f9fafb; padding: 15px; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #374151; font-size: 14px;">Payment Method</h3>
                <p style="margin: 0;">{{paymentMethod}}</p>
            </div>
            <div style="flex: 1; background: #f9fafb; padding: 15px; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #374151; font-size: 14px;">Shipping Method</h3>
                <p style="margin: 0;">{{shippingMethod}}</p>
            </div>
        </div>

        <!-- Shipping Address -->
        <h2 style="color: #16a34a; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-top: 30px;">Shipping Address</h2>
        <div style="background: #f9fafb; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 0;">{{shippingAddress.street}}</p>
            {% if shippingAddress.line2 %}<p style="margin: 0;">{{shippingAddress.line2}}</p>{% endif %}
            <p style="margin: 0;">{{shippingAddress.postcode}} {{shippingAddress.city}}</p>
            <p style="margin: 0;">{{shippingAddress.country}}</p>
        </div>

        <!-- Billing Address -->
        <h2 style="color: #16a34a; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-top: 30px;">Billing Address</h2>
        <div style="background: #f9fafb; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 0;">{{billingAddress.street}}</p>
            {% if billingAddress.line2 %}<p style="margin: 0;">{{billingAddress.line2}}</p>{% endif %}
            <p style="margin: 0;">{{billingAddress.postcode}} {{billingAddress.city}}</p>
            <p style="margin: 0;">{{billingAddress.country}}</p>
        </div>

        <p style="margin-top: 30px;">You'll receive another email when your order has been shipped.</p>
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderStatusChangeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Order Status Update</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{customer.name}}</strong>,</p>
        <p>Your order status has been updated!</p>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Order Number:</strong> #{{order.number}}</p>
            <p style="margin: 5px 0;"><strong>Order Date:</strong> {{order.createdAt}}</p>
            <p style="margin: 5px 0;"><strong>New Status:</strong> <span style="background: #3b82f6; color: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block;">{{order.status}}</span></p>
        </div>

        {% if order.status == 'shipped' and order.trackingNumber %}
        <!-- Tracking Information -->
        <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #1e40af; font-size: 18px;">📦 Tracking Information</h2>
            <p style="margin: 5px 0;"><strong>Tracking Number:</strong> {{order.trackingNumber}}</p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #4b5563;">
                Your order has been shipped and is on its way! You can track your package using the tracking number above.
            </p>
        </div>
        {% endif %}

        {% if order.status == 'delivered' %}
        <!-- Delivery Confirmation -->
        <div style="background: #d1fae5; border-left: 4px solid #16a34a; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #15803d; font-size: 18px;">✓ Delivered Successfully</h2>
            <p style="margin: 0; color: #4b5563;">
                Your order has been delivered! We hope you enjoy your purchase. If you have any issues, please contact our support team.
            </p>
        </div>
        {% endif %}

        {% if order.status == 'cancelled' %}
        <!-- Cancellation Notice -->
        <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #991b1b; font-size: 18px;">Order Cancelled</h2>
            <p style="margin: 0; color: #4b5563;">
                Your order has been cancelled. If you did not request this cancellation or have questions, please contact our support team.
            </p>
        </div>
        {% endif %}

        <!-- Order Items Summary -->
        <h3 style="color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">Order Summary</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
            {% for item in order.items %}
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <strong>{{item.name}}</strong><br>
                    <small style="color: #6b7280;">Qty: {{item.quantity}}</small>
                </td>
                <td style="padding: 10px 0; text-align: right; border-bottom: 1px solid #e5e7eb;">€{{item.subtotal}}</td>
            </tr>
            {% endfor %}
            <tr style="background: #f9fafb;">
                <td style="padding: 10px; text-align: right;"><strong>Total:</strong></td>
                <td style="padding: 10px; text-align: right;"><strong>€{{order.total}}</strong></td>
            </tr>
        </table>

        <p style="margin-top: 30px;">If you have any questions about your order, please don't hesitate to contact us.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getTestEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">SMTP Test Successful!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Congratulations! Your SMTP settings are working correctly.</p>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>SMTP Host:</strong> {{smtpHost}}</p>
            <p style="margin: 5px 0;"><strong>Port:</strong> {{smtpPort}}</p>
            <p style="margin: 5px 0;"><strong>Test Time:</strong> {{timestamp}}</p>
        </div>
        <p>Your email notification system is ready to use.</p>
        <p>Best regards,<br>TabulariumCMS</p>
    </div>
</body>
</html>
HTML;
    }

    private function getStockLowTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">⚠️ Low Stock Alert</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p><strong>Product stock is running low!</strong></p>
        <div style="background: #fef3c7; padding: 20px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Product:</strong> {{product.name}}</p>
            <p style="margin: 5px 0;"><strong>Store:</strong> {{store.name}}</p>
            <p style="margin: 5px 0;"><strong>Current Stock:</strong> {{stock.quantity}}</p>
            <p style="margin: 5px 0;"><strong>Minimum Stock:</strong> {{stock.minQuantity}}</p>
        </div>
        <p>Please restock this product to avoid stockouts.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{adminUrl}}" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Manage Stock</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getAdminNewUserTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">New User Registration</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>A new user has registered on your site.</p>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Username:</strong> {{user.name}}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {{user.email}}</p>
            <p style="margin: 5px 0;"><strong>Registered:</strong> {{user.createdAt}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{adminUrl}}" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View User</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getAdminContactFormTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">New Contact Form Submission</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>You have received a new contact form submission.</p>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Form:</strong> {{form.name}}</p>
            <p style="margin: 5px 0;"><strong>Submitted:</strong> {{submission.submittedAt}}</p>
            <hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">
            {% for field, value in submission.data %}
            <p style="margin: 5px 0;"><strong>{{field}}:</strong> {{value}}</p>
            {% endfor %}
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{adminUrl}}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Submission</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getAdminCommentModerationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Comment Awaiting Moderation</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>A new comment has been posted and is awaiting moderation.</p>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Author:</strong> {{comment.author.username}}</p>
            <p style="margin: 5px 0;"><strong>Post:</strong> {{comment.post.title}}</p>
            <p style="margin: 5px 0;"><strong>Posted:</strong> {{comment.createdAt}}</p>
            <hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">
            <p style="margin: 10px 0;">{{comment.content}}</p>
        </div>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{adminUrl}}" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Moderate Comment</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getSellerRegistrationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Welcome to TabulariumCMS Seller Program!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{user.name}}</strong>,</p>
        <p>Thank you for applying to become a seller on TabulariumCMS! We're excited to have you join our marketplace.</p>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Company Name:</strong> {{seller.companyName}}</p>
            <p style="margin: 5px 0;"><strong>Registration Date:</strong> {{seller.registeredAt}}</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="background: #f59e0b; color: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block;">{{seller.status}}</span></p>
        </div>

        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #d97706; font-size: 18px;">⏳ Application Under Review</h2>
            <p style="margin: 0; color: #4b5563;">
                Your seller application is currently being reviewed by our team. We will notify you via email once your application has been processed.
            </p>
        </div>

        <h3 style="color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">What's Next?</h3>
        <ul style="color: #6b7280;">
            <li>Our team will review your application within 2-3 business days</li>
            <li>You'll receive an email notification once approved</li>
            <li>After approval, you can start listing your products</li>
        </ul>

        <p style="margin-top: 30px;">If you have any questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getSellerApprovedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Congratulations! Your Seller Account is Approved!</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{user.name}}</strong>,</p>
        <p>Great news! Your seller application has been approved and your account is now active.</p>

        <div style="background: #d1fae5; border-left: 4px solid #16a34a; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #15803d; font-size: 18px;">✓ Account Activated</h2>
            <p style="margin: 0; color: #4b5563;">
                You can now start listing your products and selling on our platform!
            </p>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Company Name:</strong> {{seller.companyName}}</p>
            <p style="margin: 5px 0;"><strong>Commission Rate:</strong> {{seller.commissionRate}}%</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="background: #16a34a; color: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block;">Approved</span></p>
        </div>

        <h3 style="color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">Getting Started</h3>
        <ul style="color: #6b7280;">
            <li>Log in to your seller dashboard</li>
            <li>Complete your seller profile</li>
            <li>Start adding your products</li>
            <li>Set up your payment and shipping options</li>
        </ul>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{dashboardUrl}}" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Seller Dashboard</a>
        </p>

        <p style="margin-top: 30px;">We're excited to have you as part of our marketplace! If you have any questions, our support team is here to help.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getSellerRejectedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Seller Application Update</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>Hello <strong>{{user.name}}</strong>,</p>
        <p>Thank you for your interest in becoming a seller on TabulariumCMS.</p>

        <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 20px; margin: 20px 0;">
            <h2 style="margin: 0 0 10px 0; color: #991b1b; font-size: 18px;">Application Status</h2>
            <p style="margin: 0; color: #4b5563;">
                After careful review, we regret to inform you that we are unable to approve your seller application at this time.
            </p>
        </div>

        {% if seller.rejectionReason %}
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0;"><strong>Reason:</strong></p>
            <p style="margin: 0; color: #6b7280;">{{seller.rejectionReason}}</p>
        </div>
        {% endif %}

        <h3 style="color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">What You Can Do</h3>
        <ul style="color: #6b7280;">
            <li>Review the reason for rejection above</li>
            <li>Address any issues mentioned</li>
            <li>You're welcome to reapply in the future</li>
            <li>Contact our support team if you have questions</li>
        </ul>

        <p style="margin-top: 30px;">We appreciate your interest in our marketplace. If you have any questions or would like to discuss your application, please don't hesitate to contact our support team.</p>
        <p>Best regards,<br>The TabulariumCMS Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getAdminSellerRegistrationTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">New Seller Registration</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p>A new seller has registered on your platform and is awaiting approval.</p>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #374151;">Seller Information</h3>
            <p style="margin: 5px 0;"><strong>Company Name:</strong> {{seller.companyName}}</p>
            {% if seller.businessName %}
            <p style="margin: 5px 0;"><strong>Business Name:</strong> {{seller.businessName}}</p>
            {% endif %}
            <p style="margin: 5px 0;"><strong>Email:</strong> {{seller.email}}</p>
            {% if seller.phone %}
            <p style="margin: 5px 0;"><strong>Phone:</strong> {{seller.phone}}</p>
            {% endif %}
            {% if seller.website %}
            <p style="margin: 5px 0;"><strong>Website:</strong> {{seller.website}}</p>
            {% endif %}
            <p style="margin: 5px 0;"><strong>Registered:</strong> {{seller.registeredAt}}</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="background: #f59e0b; color: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block;">{{seller.status}}</span></p>
        </div>

        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0;">
            <p style="margin: 0; color: #4b5563;">
                <strong>Action Required:</strong> Please review this seller application and approve or reject it from the admin panel.
            </p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{adminUrl}}" style="background: linear-gradient(135deg, #d4af37 0%, #f4e5a2 100%); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Review Application</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getNewsletterCampaignTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <!-- Campaign Content -->
    <div style="background: #fff;">
        {{campaign.content|raw}}
    </div>

    <!-- Unsubscribe Footer -->
    <div style="text-align: center; padding: 20px; margin-top: 30px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px;">
        <p style="margin: 5px 0;">
            You are receiving this email because you subscribed to our newsletter.
        </p>
        <p style="margin: 5px 0;">
            <a href="{{unsubscribeUrl}}" style="color: #6b7280; text-decoration: underline;">Unsubscribe from this list</a>
        </p>
    </div>
</body>
</html>
HTML;
    }
}
