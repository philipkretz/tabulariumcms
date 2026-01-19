<?php

namespace App\Admin;

use App\Entity\PaymentMethod;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class PaymentMethodAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $paymentMethod = $this->getSubject();

        $form
            ->with('Basic Information', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'help' => 'Name of the payment method (e.g., "Credit Card", "PayPal", "Bank Transfer")'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Prepayment / Bank Transfer' => PaymentMethod::TYPE_PREPAYMENT,
                        'Cash on Delivery / At Location' => PaymentMethod::TYPE_CASH_ON_DELIVERY,
                        'PayPal' => PaymentMethod::TYPE_PAYPAL,
                        'Stripe (Card Payment)' => PaymentMethod::TYPE_STRIPE,
                        'Google Pay' => PaymentMethod::TYPE_GOOGLE_PAY,
                    ],
                    'help' => 'Select the payment processing type'
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3],
                    'help' => 'Description shown to customers during checkout'
                ])
                ->add('fee', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Processing fee charged for this payment method (0.00 for no fee)'
                ])
                ->add('logo', ModelType::class, [
                    'class' => 'App\Entity\Media',
                    'property' => 'filename',
                    'required' => false,
                    'help' => 'Optional logo image for this payment method'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'help' => 'Enable/disable this payment method'
                ])
                ->add('sortOrder', IntegerType::class, [
                    'help' => 'Display order (lower numbers appear first)'
                ])
            ->end();

        // Show config field only for PayPal and Stripe
        if ($paymentMethod && in_array($paymentMethod->getType(), [PaymentMethod::TYPE_PAYPAL, PaymentMethod::TYPE_STRIPE, PaymentMethod::TYPE_GOOGLE_PAY])) {
            if ($paymentMethod->getType() === PaymentMethod::TYPE_PAYPAL) {
                $configHelp = 'Enter PayPal configuration in JSON format: {"client_id": "...", "client_secret": "...", "mode": "sandbox"}';
            } elseif ($paymentMethod->getType() === PaymentMethod::TYPE_STRIPE) {
                $configHelp = 'Enter Stripe configuration in JSON format: {"publishable_key": "pk_test_...", "secret_key": "sk_test_...", "mode": "test"}';
            } else {
                $configHelp = 'Enter Google Pay configuration in JSON format: {"merchant_id": "...", "merchant_name": "...", "environment": "TEST"}';
            };

            $form
                ->with('API Configuration', ['class' => 'col-md-12'])
                    ->add('config', TextareaType::class, [
                        'required' => false,
                        'attr' => ['rows' => 6],
                        'help' => $configHelp,
                        'label' => 'API Keys & Configuration (JSON)'
                    ])
                ->end();
        }

        $form
            ->with('Price Range Restrictions', ['class' => 'col-md-12'])
                ->add('minPrice', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Minimum cart total required for this payment method (leave empty for no minimum)'
                ])
                ->add('maxPrice', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Maximum cart total allowed for this payment method (leave empty for no maximum)'
                ])
            ->end()
            ->with('Country & Category Filters', ['class' => 'col-md-6'])
                ->add('allowedCountries', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 5],
                    'help' => 'Enter country codes separated by commas (e.g., "DE,AT,CH"). Leave empty to allow all countries.',
                    'label' => 'Allowed Countries'
                ])
            ->end()
            ->with('Category Filters', ['class' => 'col-md-6'])
                ->add('allowedCategories', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 5],
                    'help' => 'Enter category IDs separated by commas (e.g., "1,5,12"). Leave empty to allow all categories.',
                    'label' => 'Allowed Categories'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('type', 'choice', [
                'choices' => [
                    PaymentMethod::TYPE_PREPAYMENT => 'Prepayment',
                    PaymentMethod::TYPE_CASH_ON_DELIVERY => 'Cash on Delivery',
                    PaymentMethod::TYPE_PAYPAL => 'PayPal',
                    PaymentMethod::TYPE_STRIPE => 'Stripe',
                    PaymentMethod::TYPE_GOOGLE_PAY => 'Google Pay',
                ]
            ])
            ->add('fee', 'decimal', [
                'label' => 'Processing Fee'
            ])
            ->add('minPrice', 'decimal', [
                'label' => 'Min Cart Total'
            ])
            ->add('maxPrice', 'decimal', [
                'label' => 'Max Cart Total'
            ])
            ->add('isActive', null, [
                'editable' => true,
                'label' => 'Active'
            ])
            ->add('sortOrder')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'duplicate' => [
                        'template' => '@App/admin/payment_method/list__action_duplicate.html.twig'
                    ],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('type', null, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Prepayment' => PaymentMethod::TYPE_PREPAYMENT,
                        'Cash on Delivery' => PaymentMethod::TYPE_CASH_ON_DELIVERY,
                        'PayPal' => PaymentMethod::TYPE_PAYPAL,
                        'Stripe' => PaymentMethod::TYPE_STRIPE,
                    ]
                ]
            ])
            ->add('isActive')
            ->add('fee')
            ->add('minPrice')
            ->add('maxPrice');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('type')
            ->add('description')
            ->add('fee')
            ->add('config')
            ->add('minPrice')
            ->add('maxPrice')
            ->add('allowedCountries')
            ->add('allowedCategories')
            ->add('logo')
            ->add('isActive')
            ->add('sortOrder')
            ->add('createdAt')
            ->add('updatedAt');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('duplicate', $this->getRouterIdParameter().'/duplicate');
    }

    public function duplicateAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find Payment Method with id: %s', $id));
        }

        // Create a new payment method with copied data
        $duplicate = new PaymentMethod();
        $duplicate->setName($object->getName() . ' (Copy)');
        $duplicate->setType($object->getType());
        $duplicate->setDescription($object->getDescription());
        $duplicate->setFee($object->getFee());
        $duplicate->setConfig($object->getConfig());
        $duplicate->setMinPrice($object->getMinPrice());
        $duplicate->setMaxPrice($object->getMaxPrice());
        $duplicate->setAllowedCountries($object->getAllowedCountries());
        $duplicate->setAllowedCategories($object->getAllowedCategories());
        $duplicate->setLogo($object->getLogo());
        $duplicate->setIsActive(false); // Inactive by default
        $duplicate->setSortOrder($object->getSortOrder());

        $this->getModelManager()->create($duplicate);

        $this->addFlash('sonata_flash_success', 'Payment method duplicated successfully!');

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function toString(object $object): string
    {
        return $object instanceof PaymentMethod
            ? $object->getName() ?? 'Payment Method'
            : 'Payment Method';
    }

    protected function prePersist(object $object): void
    {
        $this->processJsonFields($object);
    }

    protected function preUpdate(object $object): void
    {
        $this->processJsonFields($object);
    }

    /**
     * Convert comma-separated strings to arrays for JSON fields
     */
    private function processJsonFields(PaymentMethod $object): void
    {
        // Process allowed countries
        if (is_string($object->getAllowedCountries())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $countries = array_filter(array_map('trim', explode(',', $object->getAllowedCountries())));
            $object->setAllowedCountries($countries ?: null);
        }

        // Process allowed categories
        if (is_string($object->getAllowedCategories())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $categories = array_filter(array_map('trim', explode(',', $object->getAllowedCategories())));
            $object->setAllowedCategories($categories ?: null);
        }

        // Process config field - validate JSON if present
        if (is_string($object->getConfig())) {
            $config = json_decode($object->getConfig(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $object->setConfig($config);
            } else {
                $object->setConfig(null);
            }
        }
    }

    protected function postLoad(object $object): void
    {
        // Convert arrays back to comma-separated strings for form display
        if (is_array($object->getAllowedCountries())) {
            $object->setAllowedCountries(implode(', ', $object->getAllowedCountries()));
        }

        if (is_array($object->getAllowedCategories())) {
            $object->setAllowedCategories(implode(', ', $object->getAllowedCategories()));
        }

        // Convert config array to JSON string for form display
        if (is_array($object->getConfig())) {
            $object->setConfig(json_encode($object->getConfig(), JSON_PRETTY_PRINT));
        }
    }
}
