<?php

namespace App\Admin;

use App\Entity\Address;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class AddressAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Personal Info', ['class' => 'col-md-6'])
                ->add('user', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'username'
                ])
                ->add('title', TextType::class, ['required' => false])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Personal' => 'personal',
                        'Shipping' => 'shipping',
                        'Billing' => 'billing',
                    ]
                ])
                ->add('firstName', TextType::class)
                ->add('lastName', TextType::class)
                ->add('company', TextType::class, ['required' => false])
                ->add('phone', TextType::class, ['required' => false])
            ->end()
            ->with('Address Details', ['class' => 'col-md-6'])
                ->add('addressLine1', TextType::class)
                ->add('addressLine2', TextType::class, ['required' => false])
                ->add('city', TextType::class)
                ->add('state', TextType::class, ['required' => false])
                ->add('postalCode', TextType::class)
                ->add('country', TextType::class)
                ->add('isDefault', CheckboxType::class, ['required' => false])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('user')
            ->add('type')
            ->addIdentifier('fullName', null, [
                'label' => 'Name',
                'template' => '@App/admin/address/list_full_name.html.twig',
            ])
            ->add('city')
            ->add('country')
            ->add('isDefault', null, ['editable' => true])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('user')
            ->add('type')
            ->add('firstName')
            ->add('lastName')
            ->add('city')
            ->add('country')
            ->add('isDefault');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('user')
            ->add('title')
            ->add('type')
            ->add('firstName')
            ->add('lastName')
            ->add('company')
            ->add('addressLine1')
            ->add('addressLine2')
            ->add('city')
            ->add('state')
            ->add('postalCode')
            ->add('country')
            ->add('phone')
            ->add('isDefault')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Address
            ? $object->getFirstName() . ' ' . $object->getLastName() . ' - ' . $object->getCity()
            : 'Address';
    }
}
