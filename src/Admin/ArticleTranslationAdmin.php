<?php

namespace App\Admin;

use App\Entity\ArticleTranslation;
use App\Form\Type\GrapeJsEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class ArticleTranslationAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add("language", ModelType::class, [
                "class" => "App\Entity\Language",
                "property" => "name",
                "required" => true,
                "help" => "Select language for this translation"
            ])
            ->add("name", TextType::class, [
                "required" => true,
                "help" => "Translated article name"
            ])
            ->add("slug", TextType::class, [
                "required" => true,
                "help" => "Translated URL slug"
            ])
            ->add("shortDescription", TextareaType::class, [
                "required" => false,
                "attr" => ["rows" => 3],
                "help" => "Short description in this language"
            ])
            ->add("description", GrapeJsEditorType::class, [
                "label" => "Full Description",
                "editor_height" => "400px",
                "required" => false,
                "help" => "Full description in this language"
            ])
            ->add("metaTitle", TextType::class, [
                "required" => false,
                "help" => "SEO title in this language"
            ])
            ->add("metaDescription", TextareaType::class, [
                "required" => false,
                "attr" => ["rows" => 3],
                "help" => "SEO description in this language"
            ]);
    }

    public function toString(object $object): string
    {
        return $object instanceof ArticleTranslation
            ? $object->getName() . " (" . ($object->getLanguage() ? $object->getLanguage()->getCode() : "?") . ")"
            : "Translation";
    }
}
