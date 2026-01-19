<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BracketFunctionContentType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['help_html'] = true;
        $view->vars['attr']['class'] = ($view->vars['attr']['class'] ?? '') . ' monaco-editor-field';
        $view->vars['attr']['data-monaco'] = 'true';
        $view->vars['help'] = '
            <div class="bracket-functions-help">
                <h4>Available Bracket Functions:</h4>
                <ul class="list-unstyled">
                    <li><code>[menu id="1"]</code> or <code>[menu name="main-menu"]</code> - Render a menu</li>
                    <li><code>[page id="1"]</code> or <code>[page slug="about"]</code> - Render a page</li>
                    <li><code>[post id="1"]</code> or <code>[post slug="my-post"]</code> - Render a single post</li>
                    <li><code>[posts limit="5" category="news"]</code> - List posts with optional filters</li>
                    <li><code>[comments post_id="1" limit="10"]</code> - Show comments for a post</li>
                    <li><code>[site_title]</code> - Display site title</li>
                    <li><code>[current_year]</code> - Display current year (e.g., for copyright)</li>
                </ul>
                <p class="text-muted small mt-2">
                    <strong>Note:</strong> Functions are processed at render time.
                    Use <code>id</code>, <code>name</code>, or <code>slug</code> parameters to identify entities.
                </p>
            </div>
        ';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'rows' => 15,
                'class' => 'font-monospace',
                'style' => 'font-family: monospace; font-size: 13px;',
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
