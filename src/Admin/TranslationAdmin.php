<?php

namespace App\Admin;

use App\Entity\Translation;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TranslationAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Translation', ['class' => 'col-md-8'])
                ->add('transKey', TextType::class, [
                    'label' => 'Translation Key',
                    'help' => 'Unique identifier for this translation'
                ])
                ->add('value', TextareaType::class, [
                    'label' => 'Translation Value',
                    'attr' => ['rows' => 5]
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3],
                    'help' => 'Context or notes about this translation'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-4'])
                ->add('language', ModelType::class, [
                    'class' => 'App\Entity\Language',
                    'property' => 'name',
                    'help' => 'Target language for this translation'
                ])
                ->add('domain', TextType::class, [
                    'help' => 'Translation domain (e.g., messages, validators)',
                    'data' => $this->getSubject()->getDomain() ?? 'messages'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('transKey', null, ['label' => 'Key'])
            ->add('language')
            ->add('domain')
            ->add('value', null, [
                'template' => '@SonataAdmin/CRUD/list_string.html.twig'
            ])
            ->add('createdAt')
            ->add('updatedAt')
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
            ->add('transKey')
            ->add('language')
            ->add('domain')
            ->add('value');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('transKey', null, ['label' => 'Translation Key'])
            ->add('language')
            ->add('domain')
            ->add('value')
            ->add('description')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Translation
            ? $object->getTransKey() . ' (' . ($object->getLanguage()?->getCode() ?? 'unknown') . ')'
            : 'Translation';
    }
}
