<?php

namespace App\Admin;

use App\Entity\Theme;
use App\Form\Type\ColorPickerType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ThemeAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Theme Information', ['class' => 'col-md-6'])
                ->add('name', TextType::class)
                ->add('displayName', TextType::class)
                ->add('author', TextType::class)
                ->add('version', TextType::class)
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('category', ChoiceType::class, [
                    'choices' => [
                        'Default' => 'default',
                        'User' => 'user',
                        'Premium' => 'premium',
                    ]
                ])
                ->add('isActive', CheckboxType::class, ['required' => false])
                ->add('isDefault', CheckboxType::class, ['required' => false])
            ->end()
            ->with('Details', ['class' => 'col-md-12'])
                ->add('description', TextareaType::class, ['required' => false])
                ->add('thumbnailPath', TextType::class, ['required' => false])
            ->end()
            ->with('Colors', ['class' => 'col-md-6'])
                ->add('primaryColor', ColorPickerType::class, [
                    'label' => 'Primary Color',
                    'required' => false,
                    'help' => 'Main brand color used for buttons, links, etc.'
                ])
                ->add('secondaryColor', ColorPickerType::class, [
                    'label' => 'Secondary Color',
                    'required' => false,
                    'help' => 'Accent color for secondary elements'
                ])
                ->add('accentColor', ColorPickerType::class, [
                    'label' => 'Accent Color',
                    'required' => false,
                    'help' => 'Highlight color for important elements'
                ])
                ->add('backgroundColor', ColorPickerType::class, [
                    'label' => 'Background Color',
                    'required' => false,
                    'help' => 'Main background color'
                ])
                ->add('textColor', ColorPickerType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'help' => 'Default text color'
                ])
            ->end()
            ->with('Typography', ['class' => 'col-md-6'])
                ->add('headingFont', ChoiceType::class, [
                    'label' => 'Heading Font',
                    'required' => false,
                    'choices' => [
                        'Arial' => 'Arial, sans-serif',
                        'Georgia' => 'Georgia, serif',
                        'Helvetica' => 'Helvetica, sans-serif',
                        'Times New Roman' => '"Times New Roman", serif',
                        'Inter' => 'Inter, sans-serif',
                        'Roboto' => 'Roboto, sans-serif',
                        'Open Sans' => '"Open Sans", sans-serif',
                        'Lato' => 'Lato, sans-serif',
                        'Montserrat' => 'Montserrat, sans-serif',
                        'Poppins' => 'Poppins, sans-serif',
                    ],
                    'placeholder' => 'Default Font',
                    'help' => 'Font family for headings (h1, h2, etc.)'
                ])
                ->add('bodyFont', ChoiceType::class, [
                    'label' => 'Body Font',
                    'required' => false,
                    'choices' => [
                        'Arial' => 'Arial, sans-serif',
                        'Georgia' => 'Georgia, serif',
                        'Helvetica' => 'Helvetica, sans-serif',
                        'Times New Roman' => '"Times New Roman", serif',
                        'Inter' => 'Inter, sans-serif',
                        'Roboto' => 'Roboto, sans-serif',
                        'Open Sans' => '"Open Sans", sans-serif',
                        'Lato' => 'Lato, sans-serif',
                        'Montserrat' => 'Montserrat, sans-serif',
                        'Poppins' => 'Poppins, sans-serif',
                    ],
                    'placeholder' => 'Default Font',
                    'help' => 'Font family for body text'
                ])
                ->add('fontSize', ChoiceType::class, [
                    'label' => 'Base Font Size',
                    'required' => false,
                    'choices' => [
                        '14px' => '14px',
                        '16px' => '16px',
                        '18px' => '18px',
                        '20px' => '20px',
                    ],
                    'placeholder' => 'Default Size',
                    'help' => 'Base font size for body text'
                ])
            ->end()
            ->with('Layout', ['class' => 'col-md-6'])
                ->add('sidebarPosition', ChoiceType::class, [
                    'label' => 'Sidebar Position',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Right' => 'right',
                        'None' => 'none',
                    ],
                    'placeholder' => 'Default Position',
                    'help' => 'Position of the sidebar'
                ])
                ->add('headerStyle', ChoiceType::class, [
                    'label' => 'Header Style',
                    'required' => false,
                    'choices' => [
                        'Fixed' => 'fixed',
                        'Sticky' => 'sticky',
                        'Static' => 'static',
                        'Minimal' => 'minimal',
                    ],
                    'placeholder' => 'Default Style',
                    'help' => 'Header display style'
                ])
                ->add('containerWidth', ChoiceType::class, [
                    'label' => 'Container Width',
                    'required' => false,
                    'choices' => [
                        '1140px' => '1140px',
                        '1320px' => '1320px',
                        '1400px' => '1400px',
                        'Full Width' => '100%',
                    ],
                    'placeholder' => 'Default Width',
                    'help' => 'Maximum width of the content container'
                ])
            ->end()
            ->with('Advanced CSS', ['class' => 'col-md-12'])
                ->add('customCss', TextareaType::class, [
                    'label' => 'Custom CSS',
                    'required' => false,
                    'attr' => [
                        'rows' => 10,
                        'class' => 'code-editor',
                        'placeholder' => '/* Add your custom CSS here */'
                    ],
                    'help' => 'Advanced: Add custom CSS to override theme styles'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('displayName')
            ->add('name')
            ->add('author')
            ->add('version')
            ->add('category')
            ->add('isActive', null, ['editable' => true])
            ->add('isDefault', null, ['editable' => true])
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'preview' => [
                        'template' => 'admin/theme/list_action_preview.html.twig'
                    ],
                    'copy' => [
                        'template' => 'admin/theme/list_action_copy.html.twig'
                    ],
                    'downloadTheme' => [
                        'template' => 'admin/theme/list_action_export.html.twig'
                    ],
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('preview', $this->getRouterIdParameter().'/preview');
        $collection->add('copy', $this->getRouterIdParameter().'/copy');
        $collection->add('downloadTheme', $this->getRouterIdParameter().'/download');
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if (in_array($action, ['edit', 'show']) && $object) {
            $buttonList['preview'] = [
                'template' => 'admin/theme/edit_action_preview.html.twig',
            ];
            $buttonList['copy'] = [
                'template' => 'admin/theme/edit_action_copy.html.twig',
            ];
            $buttonList['downloadTheme'] = [
                'template' => 'admin/theme/edit_action_export.html.twig',
            ];
        }

        if ($action === 'list') {
            $buttonList['import'] = [
                'template' => 'admin/theme/list_button_import.html.twig',
            ];
        }

        return $buttonList;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('displayName')
            ->add('author')
            ->add('category')
            ->add('isActive')
            ->add('isDefault');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('name')
            ->add('displayName')
            ->add('description')
            ->add('author')
            ->add('version')
            ->add('category')
            ->add('thumbnailPath')
            ->add('isActive')
            ->add('isDefault')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Theme
            ? $object->getDisplayName() ?? $object->getName() ?? 'Theme'
            : 'Theme';
    }
}
