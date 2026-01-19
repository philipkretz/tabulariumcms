<?php

namespace App\Admin;

use App\Entity\Template;
use App\Form\Type\BracketFunctionContentType;
use App\Form\Type\GrapeJsEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TemplateAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Template Information', ['class' => 'col-md-6'])
                ->add('name', TextType::class)
                ->add('identifier', TextType::class, [
                    'help' => 'Unique identifier for this template'
                ])
                ->add('position', ChoiceType::class, [
                    'choices' => [
                        'Header' => 'header',
                        'Footer' => 'footer',
                        'Content' => 'content',
                        'Sidebar Left' => 'sidebar_left',
                        'Sidebar Right' => 'sidebar_right',
                        'Custom' => 'custom',
                    ]
                ])
                ->add('isActive', CheckboxType::class, ['required' => false])
            ->end()
            ->with('Template Content', ['class' => 'col-md-12'])
                ->add('content', GrapeJsEditorType::class, [
                    'label' => 'Template Content - Visual Editor',
                    'editor_height' => '700px',
                    'help' => 'Drag & drop visual editor with live preview. Click blocks on the left to add elements.',
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3]
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('identifier')
            ->add('position')
            ->add('isActive', null, ['editable' => true])
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'preview' => [
                        'template' => '@App/admin/template/list__action_preview.html.twig'
                    ],
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('identifier')
            ->add('position')
            ->add('isActive');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('identifier')
            ->add('position')
            ->add('content')
            ->add('description')
            ->add('isActive')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Template
            ? $object->getName() ?? 'Template'
            : 'Template';
    }
}
