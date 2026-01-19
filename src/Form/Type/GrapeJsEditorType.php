<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrapeJsEditorType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['class'] = ($view->vars['attr']['class'] ?? '') . ' grapesjs-content-field';
        $view->vars['attr']['data-grapesjs'] = 'true';
        $view->vars['attr']['data-editor-height'] = $options['editor_height'];
        $view->vars['attr']['style'] = 'display: none;';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'editor_height' => '650px',
            'required' => false,
        ]);

        $resolver->setAllowedTypes('editor_height', 'string');
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'grapesjs_editor';
    }
}
