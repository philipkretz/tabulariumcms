<?php

namespace App\Admin;

use App\Entity\SeoUrl;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class SeoUrlAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('URL Configuration', ['class' => 'col-md-6'])
                ->add('url', TextType::class, [
                    'help' => 'The URL path (e.g., /old-page)'
                ])
                ->add('route', TextType::class, [
                    'help' => 'Symfony route name (e.g., app_page_show)'
                ])
                ->add('locale', ChoiceType::class, [
                    'choices' => [
                        'English' => 'en',
                        'German' => 'de',
                        'Spanish' => 'es',
                        'Catalan' => 'ca',
                    ]
                ])
            ->end()
            ->with('Settings', ['class' => 'col-md-6'])
                ->add('statusCode', ChoiceType::class, [
                    'choices' => [
                        '301 Permanent Redirect' => '301',
                        '302 Temporary Redirect' => '302',
                        '200 Direct' => '200',
                    ]
                ])
                ->add('priority', IntegerType::class, [
                    'help' => 'Higher priority URLs are matched first'
                ])
                ->add('isActive', CheckboxType::class, ['required' => false])
            ->end()
            ->with('SEO Meta', ['class' => 'col-md-12'])
                ->add('title', TextType::class, ['required' => false])
                ->add('description', TextareaType::class, ['required' => false])
                ->add('canonicalUrl', TextType::class, ['required' => false])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('url')
            ->add('route')
            ->add('locale')
            ->add('statusCode')
            ->add('priority')
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
            ->add('url')
            ->add('route')
            ->add('locale')
            ->add('statusCode')
            ->add('isActive');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('url')
            ->add('route')
            ->add('parameters')
            ->add('locale')
            ->add('statusCode')
            ->add('priority')
            ->add('isActive')
            ->add('title')
            ->add('description')
            ->add('canonicalUrl')
            ->add('metaTags')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof SeoUrl
            ? $object->getUrl() ?? 'SEO URL'
            : 'SEO URL';
    }
}
