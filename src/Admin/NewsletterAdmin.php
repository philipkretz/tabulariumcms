<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class NewsletterAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Subscriber Information', ['class' => 'col-md-6'])
                ->add('email', EmailType::class, [
                    'label' => 'Email Address'
                ])
                ->add('name', TextType::class, [
                    'label' => 'Name',
                    'required' => false
                ])
                ->add('locale', ChoiceType::class, [
                    'label' => 'Language',
                    'choices' => [
                        'English' => 'en',
                        'Deutsch' => 'de',
                        'Español' => 'es',
                        'Français' => 'fr',
                        'Català' => 'ca',
                    ]
                ])
            ->end()
            ->with('Status', ['class' => 'col-md-6'])
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Active',
                    'required' => false
                ])
                ->add('isConfirmed', CheckboxType::class, [
                    'label' => 'Confirmed',
                    'required' => false
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('email')
            ->add('name')
            ->add('isActive', null, ['label' => 'Active'])
            ->add('isConfirmed', null, ['label' => 'Confirmed'])
            ->add('locale');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('email')
            ->add('name')
            ->add('locale', null, ['label' => 'Language'])
            ->add('isActive', null, [
                'label' => 'Active',
                'editable' => true
            ])
            ->add('isConfirmed', null, [
                'label' => 'Confirmed'
            ])
            ->add('subscribedAt', null, [
                'label' => 'Subscribed',
                'format' => 'Y-m-d H:i'
            ])
            ->add('confirmedAt', null, [
                'label' => 'Confirmed',
                'format' => 'Y-m-d H:i'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Subscriber Information')
                ->add('email')
                ->add('name')
                ->add('locale', null, ['label' => 'Language'])
            ->end()
            ->with('Status')
                ->add('isActive', null, ['label' => 'Active'])
                ->add('isConfirmed', null, ['label' => 'Confirmed'])
                ->add('token')
            ->end()
            ->with('Dates')
                ->add('subscribedAt')
                ->add('confirmedAt')
                ->add('unsubscribedAt')
            ->end()
            ->with('Technical')
                ->add('ipAddress', null, ['label' => 'IP Address'])
            ->end();
    }
}
