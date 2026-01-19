<?php

namespace App\Admin;

use App\Entity\VoucherCode;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class VoucherCodeAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Basic Information', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'help' => 'Unique voucher code (e.g., "SUMMER2024", "WELCOME10")'
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3],
                    'help' => 'Internal description for this voucher'
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Percentage Discount' => VoucherCode::TYPE_PERCENTAGE,
                        'Fixed Amount Discount' => VoucherCode::TYPE_FIXED,
                    ],
                    'help' => 'Choose between percentage (e.g., 10%) or fixed amount (e.g., $10 off)'
                ])
                ->add('discountValue', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Discount value: enter 10 for 10% or 10.00 for $10 fixed discount'
                ])
                ->add('minOrderValue', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Minimum cart total required to use this voucher (leave empty for no minimum)'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'help' => 'Enable/disable this voucher code'
                ])
                ->add('sortOrder', IntegerType::class, [
                    'help' => 'Display order (lower numbers appear first)'
                ])
            ->end()
            ->with('Usage Restrictions', ['class' => 'col-md-6'])
                ->add('isOneTime', CheckboxType::class, [
                    'required' => false,
                    'help' => 'If checked, this voucher can only be used once per customer'
                ])
                ->add('maxUses', IntegerType::class, [
                    'required' => false,
                    'help' => 'Maximum number of times this voucher can be used in total (leave empty for unlimited)'
                ])
                ->add('usedCount', IntegerType::class, [
                    'disabled' => true,
                    'help' => 'Number of times this voucher has been used (read-only)'
                ])
            ->end()
            ->with('Time Restrictions', ['class' => 'col-md-6'])
                ->add('validFrom', DateTimeType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'help' => 'Voucher becomes valid from this date/time (leave empty for immediate validity)'
                ])
                ->add('validUntil', DateTimeType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'help' => 'Voucher expires at this date/time (leave empty for no expiration)'
                ])
            ->end()
            ->with('Category & Article Filters', ['class' => 'col-md-12'])
                ->add('allowedCategories', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 4],
                    'help' => 'Enter category IDs separated by commas (e.g., "1,5,12"). Leave empty to allow all categories.',
                    'label' => 'Allowed Categories'
                ])
                ->add('allowedArticles', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 4],
                    'help' => 'Enter article/product IDs separated by commas (e.g., "23,45,67"). Leave empty to allow all articles.',
                    'label' => 'Allowed Articles'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('code')
            ->add('type', 'choice', [
                'choices' => [
                    VoucherCode::TYPE_PERCENTAGE => 'Percentage',
                    VoucherCode::TYPE_FIXED => 'Fixed',
                ]
            ])
            ->add('discountValue', 'decimal', [
                'label' => 'Discount'
            ])
            ->add('minOrderValue', 'decimal', [
                'label' => 'Min Order'
            ])
            ->add('usedCount', null, [
                'label' => 'Used'
            ])
            ->add('maxUses', null, [
                'label' => 'Max Uses'
            ])
            ->add('validFrom', 'datetime', [
                'label' => 'Valid From'
            ])
            ->add('validUntil', 'datetime', [
                'label' => 'Valid Until'
            ])
            ->add('isActive', null, [
                'editable' => true,
                'label' => 'Active'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'duplicate' => [
                        'template' => '@App/admin/voucher_code/list__action_duplicate.html.twig'
                    ],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code')
            ->add('type', null, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Percentage' => VoucherCode::TYPE_PERCENTAGE,
                        'Fixed' => VoucherCode::TYPE_FIXED,
                    ]
                ]
            ])
            ->add('isActive')
            ->add('isOneTime')
            ->add('discountValue')
            ->add('minOrderValue')
            ->add('validFrom')
            ->add('validUntil');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('code')
            ->add('description')
            ->add('type')
            ->add('discountValue')
            ->add('minOrderValue')
            ->add('maxUses')
            ->add('usedCount')
            ->add('isOneTime')
            ->add('validFrom')
            ->add('validUntil')
            ->add('allowedCategories')
            ->add('allowedArticles')
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
            throw $this->createNotFoundException(sprintf('Unable to find Voucher Code with id: %s', $id));
        }

        // Create a new voucher with copied data
        $duplicate = new VoucherCode();
        $duplicate->setCode($object->getCode() . '-COPY');
        $duplicate->setDescription($object->getDescription());
        $duplicate->setType($object->getType());
        $duplicate->setDiscountValue($object->getDiscountValue());
        $duplicate->setMinOrderValue($object->getMinOrderValue());
        $duplicate->setMaxUses($object->getMaxUses());
        $duplicate->setUsedCount(0); // Reset usage count
        $duplicate->setIsOneTime($object->isOneTime());
        $duplicate->setValidFrom($object->getValidFrom());
        $duplicate->setValidUntil($object->getValidUntil());
        $duplicate->setAllowedCategories($object->getAllowedCategories());
        $duplicate->setAllowedArticles($object->getAllowedArticles());
        $duplicate->setIsActive(false); // Inactive by default
        $duplicate->setSortOrder($object->getSortOrder());

        $this->getModelManager()->create($duplicate);

        $this->addFlash('sonata_flash_success', 'Voucher code duplicated successfully!');

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function toString(object $object): string
    {
        return $object instanceof VoucherCode
            ? $object->getCode() ?? 'Voucher Code'
            : 'Voucher Code';
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
    private function processJsonFields(VoucherCode $object): void
    {
        // Process allowed categories
        if (is_string($object->getAllowedCategories())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $categories = array_filter(array_map('trim', explode(',', $object->getAllowedCategories())));
            $object->setAllowedCategories($categories ?: null);
        }

        // Process allowed articles
        if (is_string($object->getAllowedArticles())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $articles = array_filter(array_map('trim', explode(',', $object->getAllowedArticles())));
            $object->setAllowedArticles($articles ?: null);
        }
    }

    protected function postLoad(object $object): void
    {
        // Convert arrays back to comma-separated strings for form display
        if (is_array($object->getAllowedCategories())) {
            $object->setAllowedCategories(implode(', ', $object->getAllowedCategories()));
        }

        if (is_array($object->getAllowedArticles())) {
            $object->setAllowedArticles(implode(', ', $object->getAllowedArticles()));
        }
    }
}
