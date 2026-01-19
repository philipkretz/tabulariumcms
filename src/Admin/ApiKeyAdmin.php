<?php

namespace App\Admin;

use App\Entity\ApiKey;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ApiKeyAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('API Key Information', ['class' => 'col-md-6'])
                ->add('name', TextType::class, [
                    'label' => 'Key Name',
                    'required' => true,
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['rows' => 3],
                ])
            ->end()
            ->with('Security Settings', ['class' => 'col-md-6'])
                ->add('isActive', CheckboxType::class, [
                    'label' => 'Active',
                    'required' => false,
                ])
                ->add('expiresAt', DateTimeType::class, [
                    'label' => 'Expires At',
                    'required' => false,
                    'widget' => 'single_text',
                ])
            ->end()
            ->with('Rate Limiting', ['class' => 'col-md-12'])
                ->add('rateLimit', IntegerType::class, [
                    'label' => 'Rate Limit (requests per hour)',
                    'required' => false,
                    'help' => 'Leave empty for unlimited requests',
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, [
                'label' => 'Name',
            ])
            ->add('apiKey', null, [
                'label' => 'API Key',
                'template' => '@App/admin/api_key_field.html.twig',
            ])
            ->add('isActive', null, [
                'label' => 'Active',
                'editable' => true,
            ])
            ->add('rateLimit', null, [
                'label' => 'Rate Limit',
            ])
            ->add('lastUsedAt', null, [
                'label' => 'Last Used',
            ])
            ->add('expiresAt', null, [
                'label' => 'Expires',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('isActive')
            ->add('expiresAt');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('API Key Information')
                ->add('name')
                ->add('description')
                ->add('apiKey')
            ->end()
            ->with('Security')
                ->add('isActive')
                ->add('expiresAt')
                ->add('lastUsedAt')
            ->end()
            ->with('Rate Limiting')
                ->add('rateLimit')
                ->add('requestCount')
                ->add('lastRequestAt')
            ->end()
            ->with('Timestamps')
                ->add('createdAt')
                ->add('updatedAt')
            ->end();
    }

    protected function prePersist(object $object): void
    {
        if ($object instanceof ApiKey) {
            if (!$object->getApiKey()) {
                $object->generateApiKey();
            }
        }
    }
}
