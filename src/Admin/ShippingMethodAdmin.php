<?php

namespace App\Admin;

use App\Entity\ShippingMethod;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ShippingMethodAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Basic Information', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'help' => 'Name of the shipping method (e.g., "Standard Shipping", "Express Delivery")'
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3],
                    'help' => 'Description shown to customers during checkout'
                ])
                ->add('price', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Base shipping cost'
                ])
                ->add('deliveryTime', TextType::class, [
                    'required' => false,
                    'help' => 'Estimated delivery time (e.g., "2-3 business days")'
                ])
                ->add('logo', ModelType::class, [
                    'class' => 'App\Entity\Media',
                    'property' => 'filename',
                    'required' => false,
                    'help' => 'Optional logo image for this shipping method'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'help' => 'Enable/disable this shipping method'
                ])
                ->add('requiresStoreSelection', CheckboxType::class, [
                    'label' => 'Requires Store Selection',
                    'required' => false,
                    'help' => 'Check this if customers must select a pickup store (e.g., "Fetch at Store" shipping method)'
                ])
                ->add('sortOrder', IntegerType::class, [
                    'help' => 'Display order (lower numbers appear first)'
                ])
            ->end()
            ->with('Price Range Restrictions', ['class' => 'col-md-12'])
                ->add('minPrice', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Minimum cart total required for this shipping method (leave empty for no minimum)'
                ])
                ->add('maxPrice', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Maximum cart total allowed for this shipping method (leave empty for no maximum)'
                ])
            ->end()
            ->with('Country & Postcode Filters', ['class' => 'col-md-6'])
                ->add('allowedCountries', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 5],
                    'help' => 'Enter country codes separated by commas (e.g., "DE,AT,CH"). Leave empty to allow all countries.',
                    'label' => 'Allowed Countries'
                ])
                ->add('allowedPostcodes', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 5],
                    'help' => 'Enter postcodes/ZIP codes separated by commas (e.g., "10115,10117,10119"). Leave empty to allow all postcodes.',
                    'label' => 'Allowed Postcodes'
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
            ->add('price', 'decimal', [
                'label' => 'Base Price'
            ])
            ->add('deliveryTime')
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
            ->add('requiresStoreSelection', null, [
                'label' => 'Requires Store'
            ])
            ->add('sortOrder')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'duplicate' => [
                        'template' => '@App/admin/shipping_method/list__action_duplicate.html.twig'
                    ],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('isActive')
            ->add('requiresStoreSelection')
            ->add('price')
            ->add('minPrice')
            ->add('maxPrice');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('deliveryTime')
            ->add('minPrice')
            ->add('maxPrice')
            ->add('allowedCountries')
            ->add('allowedPostcodes')
            ->add('allowedCategories')
            ->add('logo')
            ->add('isActive')
            ->add('requiresStoreSelection')
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
            throw $this->createNotFoundException(sprintf('Unable to find Shipping Method with id: %s', $id));
        }

        // Create a new shipping method with copied data
        $duplicate = new ShippingMethod();
        $duplicate->setName($object->getName() . ' (Copy)');
        $duplicate->setDescription($object->getDescription());
        $duplicate->setPrice($object->getPrice());
        $duplicate->setDeliveryTime($object->getDeliveryTime());
        $duplicate->setMinPrice($object->getMinPrice());
        $duplicate->setMaxPrice($object->getMaxPrice());
        $duplicate->setAllowedCountries($object->getAllowedCountries());
        $duplicate->setAllowedPostcodes($object->getAllowedPostcodes());
        $duplicate->setAllowedCategories($object->getAllowedCategories());
        $duplicate->setLogo($object->getLogo());
        $duplicate->setIsActive(false); // Inactive by default
        $duplicate->setRequiresStoreSelection($object->isRequiresStoreSelection());
        $duplicate->setSortOrder($object->getSortOrder());

        $this->getModelManager()->create($duplicate);

        $this->addFlash('sonata_flash_success', 'Shipping method duplicated successfully!');

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function toString(object $object): string
    {
        return $object instanceof ShippingMethod
            ? $object->getName() ?? 'Shipping Method'
            : 'Shipping Method';
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
    private function processJsonFields(ShippingMethod $object): void
    {
        // Process allowed countries
        if (is_string($object->getAllowedCountries())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $countries = array_filter(array_map('trim', explode(',', $object->getAllowedCountries())));
            $object->setAllowedCountries($countries ?: null);
        }

        // Process allowed postcodes
        if (is_string($object->getAllowedPostcodes())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $postcodes = array_filter(array_map('trim', explode(',', $object->getAllowedPostcodes())));
            $object->setAllowedPostcodes($postcodes ?: null);
        }

        // Process allowed categories
        if (is_string($object->getAllowedCategories())) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callbacks 'trim' and closure for filtering
            $categories = array_filter(array_map('trim', explode(',', $object->getAllowedCategories())));
            $object->setAllowedCategories($categories ?: null);
        }
    }

    protected function postLoad(object $object): void
    {
        // Convert arrays back to comma-separated strings for form display
        if (is_array($object->getAllowedCountries())) {
            $object->setAllowedCountries(implode(', ', $object->getAllowedCountries()));
        }

        if (is_array($object->getAllowedPostcodes())) {
            $object->setAllowedPostcodes(implode(', ', $object->getAllowedPostcodes()));
        }

        if (is_array($object->getAllowedCategories())) {
            $object->setAllowedCategories(implode(', ', $object->getAllowedCategories()));
        }
    }
}
