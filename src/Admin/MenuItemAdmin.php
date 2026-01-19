<?php

namespace App\Admin;

use App\Entity\MenuItem;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class MenuItemAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Menu Item', ['class' => 'col-md-8'])
                ->add('title', TextType::class)
                ->add('url', TextType::class, [
                    'required' => false,
                    'help' => 'Direct URL (takes priority over page/route)'
                ])
                ->add('page', ModelType::class, [
                    'class' => 'App\Entity\Page',
                    'property' => 'title',
                    'required' => false,
                    'help' => 'Link to a page (used if no URL is set)'
                ])
                ->add('route', TextType::class, [
                    'required' => false,
                    'help' => 'Symfony route name'
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-4'])
                ->add('menu', ModelType::class, [
                    'class' => 'App\Entity\Menu',
                    'property' => 'name'
                ])
                ->add('parent', ModelType::class, [
                    'class' => 'App\Entity\MenuItem',
                    'property' => 'title',
                    'required' => false,
                    'help' => 'Parent menu item for nested menus'
                ])
                ->add('sortOrder', IntegerType::class, [
                    'help' => 'Display order (lower numbers appear first)'
                ])
                ->add('isActive', CheckboxType::class, ['required' => false])
                ->add('openInNewTab', CheckboxType::class, ['required' => false])
            ->end()
            ->with('Styling', ['class' => 'col-md-12'])
                ->add('cssClass', TextType::class, [
                    'required' => false,
                    'help' => 'CSS classes for styling'
                ])
                ->add('icon', TextType::class, [
                    'required' => false,
                    'help' => 'Icon class or identifier'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('menu')
            ->addIdentifier('title')
            ->add('parent')
            ->add('url')
            ->add('page')
            ->add('sortOrder')
            ->add('isActive', null, ['editable' => true])
            ->add('createdAt')
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
            ->add('menu')
            ->add('title')
            ->add('parent')
            ->add('isActive');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('menu')
            ->add('title')
            ->add('url')
            ->add('page')
            ->add('route')
            ->add('parent')
            ->add('sortOrder')
            ->add('isActive')
            ->add('openInNewTab')
            ->add('cssClass')
            ->add('icon')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof MenuItem
            ? $object->getTitle() ?? 'Menu Item'
            : 'Menu Item';
    }
}
