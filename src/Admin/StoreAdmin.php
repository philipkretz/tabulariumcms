<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class StoreAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('General')
                ->with('Store Information', ['class' => 'col-md-6'])
                    ->add('name', TextType::class, [
                        'label' => 'Store Name',
                        'help' => 'Name of the store/pickup location'
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                        'required' => false,
                        'attr' => ['rows' => 3],
                        'help' => 'Additional information about this store'
                    ])
                    ->add('managerName', TextType::class, [
                        'label' => 'Manager Name',
                        'required' => false
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Phone Number',
                        'required' => false
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email',
                        'required' => false
                    ])
                ->end()
                ->with('Status', ['class' => 'col-md-6'])
                    ->add('isActive', CheckboxType::class, [
                        'label' => 'Active',
                        'required' => false,
                        'help' => 'Only active stores are shown to customers'
                    ])
                    ->add('sortOrder', IntegerType::class, [
                        'label' => 'Sort Order',
                        'required' => false,
                        'help' => 'Lower numbers appear first'
                    ])
                ->end()
            ->end()
            ->tab('Address')
                ->with('Location', ['class' => 'col-md-12'])
                    ->add('address', TextType::class, [
                        'label' => 'Street Address',
                        'help' => 'Full street address'
                    ])
                    ->add('city', TextType::class, [
                        'label' => 'City'
                    ])
                    ->add('postalCode', TextType::class, [
                        'label' => 'Postal Code'
                    ])
                    ->add('country', ChoiceType::class, [
                        'label' => 'Country',
                        'choices' => [
                            'Germany' => 'DE',
                            'Austria' => 'AT',
                            'Switzerland' => 'CH',
                            'France' => 'FR',
                            'Italy' => 'IT',
                            'Spain' => 'ES',
                            'Netherlands' => 'NL',
                            'Belgium' => 'BE',
                            'Poland' => 'PL',
                            'Czech Republic' => 'CZ',
                            'United Kingdom' => 'GB',
                        ]
                    ])
                ->end()
            ->end()
            ->tab('GPS Coordinates')
                ->with('Location Coordinates', ['class' => 'col-md-12'])
                    ->add('latitude', NumberType::class, [
                        'label' => 'Latitude',
                        'scale' => 8,
                        'help' => 'GPS latitude coordinate (e.g., 48.1351253). You can find this on Google Maps by right-clicking on the location.'
                    ])
                    ->add('longitude', NumberType::class, [
                        'label' => 'Longitude',
                        'scale' => 8,
                        'help' => 'GPS longitude coordinate (e.g., 11.5819805)'
                    ])
                ->end()
            ->end()
            ->tab('Opening Hours')
                ->with('Schedule', ['class' => 'col-md-12'])
                    ->add('openingHours', TextareaType::class, [
                        'label' => 'Opening Hours',
                        'required' => false,
                        'attr' => ['rows' => 8],
                        'help' => 'Enter opening hours (e.g., "Mon-Fri: 9:00-18:00, Sat: 10:00-14:00, Sun: Closed")'
                    ])
                ->end()
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('name', null, ['label' => 'Store Name'])
            ->add('city', null, ['label' => 'City'])
            ->add('country', null, ['label' => 'Country'])
            ->add('isActive', null, ['label' => 'Active']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Store Name'
            ])
            ->add('address', null, [
                'label' => 'Address'
            ])
            ->add('city', null, [
                'label' => 'City'
            ])
            ->add('country', null, [
                'label' => 'Country'
            ])
            ->add('phone', null, [
                'label' => 'Phone'
            ])
            ->add('isActive', null, [
                'label' => 'Active',
                'editable' => true
            ])
            ->add('sortOrder', null, [
                'label' => 'Sort Order',
                'editable' => true
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
                'label' => 'Actions'
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('General')
                ->with('Store Information')
                    ->add('id', null, ['label' => 'ID'])
                    ->add('name', null, ['label' => 'Store Name'])
                    ->add('description', null, ['label' => 'Description'])
                    ->add('managerName', null, ['label' => 'Manager'])
                    ->add('phone', null, ['label' => 'Phone'])
                    ->add('email', null, ['label' => 'Email'])
                    ->add('isActive', null, ['label' => 'Active'])
                    ->add('sortOrder', null, ['label' => 'Sort Order'])
                ->end()
            ->end()
            ->tab('Location')
                ->with('Address')
                    ->add('address', null, ['label' => 'Street Address'])
                    ->add('city', null, ['label' => 'City'])
                    ->add('postalCode', null, ['label' => 'Postal Code'])
                    ->add('country', null, ['label' => 'Country'])
                ->end()
                ->with('GPS Coordinates')
                    ->add('latitude', null, ['label' => 'Latitude'])
                    ->add('longitude', null, ['label' => 'Longitude'])
                ->end()
            ->end()
            ->tab('Details')
                ->with('Opening Hours')
                    ->add('openingHours', null, ['label' => 'Opening Hours'])
                ->end()
                ->with('Timestamps')
                    ->add('createdAt', null, ['label' => 'Created At'])
                    ->add('updatedAt', null, ['label' => 'Updated At'])
                ->end()
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof \App\Entity\Store
            ? $object->getName() ?? 'Store'
            : 'Store';
    }
}
