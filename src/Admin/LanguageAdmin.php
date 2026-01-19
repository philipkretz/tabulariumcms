<?php

namespace App\Admin;

use App\Entity\Language;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class LanguageAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Language Information', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'help' => 'ISO language code (e.g., en, de, es, ca)'
                ])
                ->add('name', TextType::class, [
                    'help' => 'Language name in English'
                ])
                ->add('nativeName', TextType::class, [
                    'help' => 'Language name in native language'
                ])
                ->add('flagEmoji', TextType::class, [
                    'required' => false,
                    'help' => 'Flag emoji (e.g., 🇺🇸, 🇩🇪, 🇪🇸)'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('urlPath', TextType::class, [
                    'required' => false,
                    'help' => 'URL path prefix (e.g., /en, /de)'
                ])
                ->add('sortOrder', IntegerType::class)
                ->add('isDefault', CheckboxType::class, ['required' => false])
                ->add('isActive', CheckboxType::class, ['required' => false])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('flagEmoji', null, ['label' => '🏳️'])
            ->addIdentifier('name')
            ->add('nativeName')
            ->add('code')
            ->add('urlPath')
            ->add('sortOrder')
            ->add('isDefault', null, ['editable' => true])
            ->add('isActive', null, ['editable' => true])
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
            ->add('code')
            ->add('name')
            ->add('nativeName')
            ->add('isDefault')
            ->add('isActive');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('code')
            ->add('name')
            ->add('nativeName')
            ->add('flagEmoji')
            ->add('urlPath')
            ->add('sortOrder')
            ->add('isDefault')
            ->add('isActive')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Language
            ? ($object->getFlagEmoji() ?? '') . ' ' . ($object->getName() ?? 'Language')
            : 'Language';
    }
}
