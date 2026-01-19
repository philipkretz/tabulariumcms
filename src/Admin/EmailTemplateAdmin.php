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
use Sonata\Form\Type\BooleanType;

final class EmailTemplateAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Template Name',
                    'help' => 'Descriptive name for this email template'
                ])
                ->add('slug', TextType::class, [
                    'label' => 'Slug',
                    'help' => 'Unique identifier (e.g., user-registration, order-confirmation)'
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Internal description of when this template is used',
                    'attr' => ['rows' => 3]
                ])
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Active',
                    'required' => false,
                    'help' => 'Only active templates will be sent'
                ])
            ->end()
            ->with('Email Settings', ['class' => 'col-md-6'])
                ->add('fromEmail', TextType::class, [
                    'label' => 'From Email',
                    'required' => false,
                    'help' => 'Leave empty to use system default'
                ])
                ->add('fromName', TextType::class, [
                    'label' => 'From Name',
                    'required' => false,
                    'help' => 'Leave empty to use system default'
                ])
                ->add('bccEmails', TextType::class, [
                    'label' => 'BCC Emails',
                    'required' => false,
                    'help' => 'Comma-separated list of BCC recipients'
                ])
            ->end()
            ->with('Content', ['class' => 'col-md-12'])
                ->add('subject', TextType::class, [
                    'label' => 'Email Subject',
                    'help' => 'Variables: {{user.name}}, {{order.number}}, etc.'
                ])
                ->add('bodyHtml', TextareaType::class, [
                    'label' => 'HTML Body',
                    'help' => 'HTML email content. Variables: {{user.name}}, {{order.number}}, etc.',
                    'attr' => ['rows' => 15, 'class' => 'code-editor']
                ])
                ->add('bodyText', TextareaType::class, [
                    'label' => 'Plain Text Body',
                    'required' => false,
                    'help' => 'Plain text fallback (optional)',
                    'attr' => ['rows' => 10]
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('name')
            ->add('slug')
            ->add('isActive', null, ['label' => 'Active']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, ['label' => 'Template Name'])
            ->add('slug')
            ->add('subject')
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
            ->with('General')
                ->add('name')
                ->add('slug')
                ->add('description')
                ->add('isActive', null, ['label' => 'Active'])
            ->end()
            ->with('Email Settings')
                ->add('fromEmail')
                ->add('fromName')
                ->add('bccEmails')
            ->end()
            ->with('Content')
                ->add('subject')
                ->add('bodyHtml', null, ['safe' => false])
                ->add('bodyText')
            ->end()
            ->with('Metadata')
                ->add('createdAt')
                ->add('updatedAt')
            ->end();
    }
}
