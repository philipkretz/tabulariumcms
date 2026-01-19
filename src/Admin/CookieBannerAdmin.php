<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

final class CookieBannerAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Banner Content', ['class' => 'col-md-12'])
                ->add('title', TextType::class, [
                    'label' => 'Title'
                ])
                ->add('message', TextareaType::class, [
                    'label' => 'Message',
                    'help' => 'Main cookie banner message',
                    'attr' => ['rows' => 4]
                ])
            ->end()
            ->with('Button Labels', ['class' => 'col-md-6'])
                ->add('acceptButtonText', TextType::class, [
                    'label' => 'Accept Button Text'
                ])
                ->add('declineButtonText', TextType::class, [
                    'label' => 'Decline Button Text'
                ])
                ->add('settingsButtonText', TextType::class, [
                    'label' => 'Settings Button Text'
                ])
            ->end()
            ->with('Links & Settings', ['class' => 'col-md-6'])
                ->add('privacyPolicyUrl', UrlType::class, [
                    'label' => 'Privacy Policy URL',
                    'required' => false,
                    'help' => 'Link to your privacy policy page'
                ])
                ->add('imprintUrl', UrlType::class, [
                    'label' => 'Imprint URL',
                    'required' => false,
                    'help' => 'Link to your imprint/legal notice page'
                ])
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Active',
                    'required' => false,
                    'help' => 'Show cookie banner to visitors'
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('title')
            ->add('isActive', null, ['label' => 'Active']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title')
            ->add('message', null, [
                'template' => '@SonataAdmin/CRUD/list_string_truncate.html.twig',
            ])
            ->add('isActive', null, [
                'label' => 'Active',
                'editable' => true
            ])
            ->add('updatedAt', null, [
                'label' => 'Last Updated',
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
            ->with('Banner Content')
                ->add('title')
                ->add('message')
            ->end()
            ->with('Button Labels')
                ->add('acceptButtonText')
                ->add('declineButtonText')
                ->add('settingsButtonText')
            ->end()
            ->with('Links & Settings')
                ->add('privacyPolicyUrl')
                ->add('imprintUrl')
                ->add('isActive', null, ['label' => 'Active'])
            ->end()
            ->with('Metadata')
                ->add('createdAt')
                ->add('updatedAt')
            ->end();
    }
}
