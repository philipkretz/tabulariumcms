<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

final class SiteSettingsAdmin extends AbstractAdmin
{
    protected function configureActionButtons(
        array $buttonList,
        string $action,
        ?object $object = null
    ): array {
        if ($action === 'edit' && $object) {
            $buttonList['test_email'] = [
                'template' => 'admin/site_settings/test_email_button.html.twig',
            ];
        }
        return $buttonList;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Route for test email is handled by separate controller
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General Settings', ['class' => 'col-md-12'])
                ->add('defaultCurrency', ChoiceType::class, [
                    'label' => 'Default Currency',
                    'choices' => [
                        'Euro (EUR)' => 'EUR',
                        'US Dollar (USD)' => 'USD',
                        'British Pound (GBP)' => 'GBP',
                        'Swiss Franc (CHF)' => 'CHF',
                        'Japanese Yen (JPY)' => 'JPY',
                        'Canadian Dollar (CAD)' => 'CAD',
                        'Australian Dollar (AUD)' => 'AUD',
                    ],
                    'help' => 'Default currency for products and orders'
                ])
            ->end()
            ->with('ECommerce Features', ['class' => 'col-md-12'])
                ->add('ecommerceEnabled', CheckboxType::class, [
                    'label' => 'Enable ECommerce Features',
                    'required' => false,
                    'help' => 'Master toggle for all ecommerce functionality (shop, products, orders, payments, shipping). Disabling this will hide all ecommerce menu items in admin panel.'
                ])
            ->end()
            ->with('User Features', ['class' => 'col-md-6'])
                ->add('userProfilesEnabled', CheckboxType::class, [
                    'label' => 'Enable User Profiles',
                    'required' => false
                ])
                ->add('publicProfilesEnabled', CheckboxType::class, [
                    'label' => 'Allow Public Profiles',
                    'required' => false
                ])
                ->add('requireProfileApproval', CheckboxType::class, [
                    'label' => 'Require Profile Approval',
                    'required' => false
                ])
            ->end()
            ->with('Social Features', ['class' => 'col-md-6'])
                ->add('friendSystemEnabled', CheckboxType::class, [
                    'label' => 'Enable Friend System',
                    'required' => false
                ])
                ->add('messagingEnabled', CheckboxType::class, [
                    'label' => 'Enable Messaging',
                    'required' => false
                ])
                ->add('userBlockingEnabled', CheckboxType::class, [
                    'label' => 'Enable User Blocking',
                    'required' => false
                ])
            ->end()
            ->with('Media & Seller Settings', ['class' => 'col-md-6'])
                ->add('userMediaEnabled', CheckboxType::class, [
                    'label' => 'Enable User Media Uploads',
                    'required' => false
                ])
                ->add('maxMediaPerUser', IntegerType::class, [
                    'label' => 'Max Media Files Per User'
                ])
                ->add('maxMediaSizeKb', IntegerType::class, [
                    'label' => 'Max Media Size (KB)'
                ])
                ->add('sellerSystemEnabled', CheckboxType::class, [
                    'label' => 'Enable Seller System',
                    'required' => false
                ])
            ->end()
            ->with('Two-Factor Authentication', ['class' => 'col-md-6'])
                ->add('twoFactorEnabledForUsers', CheckboxType::class, [
                    'label' => 'Enable 2FA for Users',
                    'required' => false,
                    'help' => 'Allow regular users to set up two-factor authentication'
                ])
                ->add('twoFactorEnabledForSellers', CheckboxType::class, [
                    'label' => 'Enable 2FA for Sellers',
                    'required' => false,
                    'help' => 'Allow sellers to set up two-factor authentication'
                ])
                ->add('twoFactorEnabledForAdmins', CheckboxType::class, [
                    'label' => 'Enable 2FA for Admins',
                    'required' => false,
                    'help' => 'Allow administrators to set up two-factor authentication'
                ])
                ->add('twoFactorRequired', CheckboxType::class, [
                    'label' => 'Make 2FA Mandatory',
                    'required' => false,
                    'help' => 'Force all users to set up 2FA (only applies where enabled above)'
                ])
            ->end()
            ->with('Email Configuration', ['class' => 'col-md-12'])
                ->add('useCustomSmtpSettings', CheckboxType::class, [
                    'label' => 'Use Custom SMTP Settings',
                    'required' => false,
                    'help' => 'Override default MAILER_DSN with custom SMTP configuration'
                ])
                ->add('adminNotificationEmail', TextType::class, [
                    'label' => 'Admin Notification Email',
                    'required' => false,
                    'help' => 'Email address to receive admin notifications (stock alerts, new users, etc.)'
                ])
            ->end()
            ->with('SMTP Settings', ['class' => 'col-md-6'])
                ->add('smtpHost', TextType::class, [
                    'label' => 'SMTP Host',
                    'required' => false,
                    'help' => 'e.g., smtp.gmail.com, smtp.office365.com'
                ])
                ->add('smtpPort', IntegerType::class, [
                    'label' => 'SMTP Port',
                    'required' => false,
                    'help' => 'Usually 587 for TLS, 465 for SSL, 25 for unencrypted'
                ])
                ->add('smtpUsername', TextType::class, [
                    'label' => 'SMTP Username',
                    'required' => false,
                    'help' => 'Your email address or username'
                ])
                ->add('smtpPassword', PasswordType::class, [
                    'label' => 'SMTP Password',
                    'required' => false,
                    'help' => 'Password will be encrypted',
                    'always_empty' => false
                ])
                ->add('smtpEncryption', ChoiceType::class, [
                    'label' => 'Encryption',
                    'choices' => [
                        'TLS' => 'tls',
                        'SSL' => 'ssl',
                        'None' => null
                    ],
                    'required' => false,
                    'help' => 'TLS is recommended for most modern servers'
                ])
            ->end()
            ->with('Email Notifications', ['class' => 'col-md-6'])
                ->add('notifyAdminOnStockLow', CheckboxType::class, [
                    'label' => 'Notify on Low Stock',
                    'required' => false,
                    'help' => 'Send email when product stock is low or out of stock'
                ])
                ->add('notifyAdminOnNewUser', CheckboxType::class, [
                    'label' => 'Notify on New User Registration',
                    'required' => false,
                    'help' => 'Send email when a new user registers'
                ])
                ->add('notifyAdminOnContactForm', CheckboxType::class, [
                    'label' => 'Notify on Contact Form Submission',
                    'required' => false,
                    'help' => 'Send email when contact form is submitted'
                ])
                ->add('notifyAdminOnCommentModeration', CheckboxType::class, [
                    'label' => 'Notify on Comment Needs Moderation',
                    'required' => false,
                    'help' => 'Send email when a comment needs moderation'
                ])
                ->add('notifyAdminOnSellerRegistration', CheckboxType::class, [
                    'label' => 'Notify on Seller Registration',
                    'required' => false,
                    'help' => 'Send email when a new seller registers'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name')
            ->add('defaultCurrency', null, ['label' => 'Currency'])
            ->add('userProfilesEnabled', null, ['label' => 'Profiles'])
            ->add('friendSystemEnabled', null, ['label' => 'Friends'])
            ->add('messagingEnabled', null, ['label' => 'Messaging'])
            ->add('sellerSystemEnabled', null, ['label' => 'Sellers'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                ]
            ]);
    }
}
