<?php

namespace App\Admin;

use App\Entity\Page;
use App\Form\Type\GrapeJsEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class PageAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Page Content", ["class" => "col-md-8"])
                ->add("title", TextType::class)
                ->add("slug", TextType::class)
                ->add("content", GrapeJsEditorType::class, [
                    "label" => "Page Content - Visual Editor",
                    "editor_height" => "700px",
                    "help" => "Drag & drop editor with live preview.",
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-4"])
                ->add("template", ChoiceType::class, [
                    "choices" => [
                        "Default" => "default",
                        "Full Width" => "full-width",
                        "Landing Page" => "landing",
                        "Sidebar Left" => "sidebar-left",
                        "Sidebar Right" => "sidebar-right",
                    ]
                ])
                ->add("category", ModelType::class, [
                    "class" => "App\\Entity\\Category",
                    "property" => "name",
                    "required" => false
                ])
                ->add("language", ModelType::class, [
                    "class" => "App\\Entity\\Language",
                    "property" => "name",
                    "required" => false,
                    "help" => "Select the language for this page"
                ])
                ->add("author", ModelType::class, [
                    "class" => "App\\Entity\\User",
                    "property" => "username"
                ])
                ->add("sortOrder", IntegerType::class)
                ->add("isPublished", CheckboxType::class, ["required" => false])
                ->add("isHomePage", CheckboxType::class, ["required" => false])
            ->end()
            ->with("Basic SEO", ["class" => "col-md-6"])
                ->add("metaTitle", TextType::class, [
                    "required" => false,
                    "help" => "Title for search engines (50-60 characters)"
                ])
                ->add("metaDescription", TextareaType::class, [
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Description for search results (150-160 characters)"
                ])
            ->end()
            ->with("Open Graph (Facebook)", ["class" => "col-md-6"])
                ->add("ogTitle", TextType::class, [
                    "label" => "OG Title",
                    "required" => false,
                    "help" => "Title when shared on Facebook"
                ])
                ->add("ogDescription", TextareaType::class, [
                    "label" => "OG Description",
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Description when shared on Facebook"
                ])
                ->add("ogImage", TextType::class, [
                    "label" => "OG Image URL",
                    "required" => false,
                    "help" => "Image URL for Facebook sharing (1200x630px recommended)"
                ])
                ->add("ogType", ChoiceType::class, [
                    "label" => "OG Type",
                    "required" => false,
                    "choices" => [
                        "Website" => "website",
                        "Article" => "article",
                        "Product" => "product",
                    ]
                ])
            ->end()
            ->with("Twitter Card", ["class" => "col-md-6"])
                ->add("twitterCard", ChoiceType::class, [
                    "label" => "Card Type",
                    "required" => false,
                    "choices" => [
                        "Summary" => "summary",
                        "Summary Large Image" => "summary_large_image",
                        "App" => "app",
                        "Player" => "player",
                    ],
                    "help" => "Twitter card layout"
                ])
                ->add("twitterTitle", TextType::class, [
                    "label" => "Twitter Title",
                    "required" => false,
                    "help" => "Title when shared on Twitter"
                ])
                ->add("twitterDescription", TextareaType::class, [
                    "label" => "Twitter Description",
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Description when shared on Twitter"
                ])
                ->add("twitterImage", TextType::class, [
                    "label" => "Twitter Image URL",
                    "required" => false,
                    "help" => "Image URL for Twitter card"
                ])
            ->end()
            ->with("Intelligent Agents / Schema.org", ["class" => "col-md-6"])
                ->add("schemaType", ChoiceType::class, [
                    "label" => "Schema Type",
                    "required" => false,
                    "choices" => [
                        "WebPage" => "WebPage",
                        "Article" => "Article",
                        "BlogPosting" => "BlogPosting",
                        "AboutPage" => "AboutPage",
                        "ContactPage" => "ContactPage",
                        "FAQPage" => "FAQPage",
                        "Product" => "Product",
                        "Service" => "Service",
                    ],
                    "help" => "Schema.org type for structured data"
                ])
                ->add("structuredData", TextareaType::class, [
                    "label" => "Structured Data (JSON-LD)",
                    "required" => false,
                    "attr" => ["rows" => 8, "style" => "font-family: monospace;"],
                    "help" => "Custom JSON-LD structured data for AI agents and rich snippets"
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("title")
            ->add("slug")
            ->add("language")
            ->add("template")
            ->add("author")
            ->add("sortOrder")
            ->add("isPublished", null, ["editable" => true])
            ->add("isHomePage", null, ["editable" => true])
            ->add("createdAt")
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "duplicate" => [
                        "template" => "@App/admin/page/list__action_duplicate.html.twig"
                    ],
                    "delete" => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("title")
            ->add("slug")
            ->add("template")
            ->add("author")
            ->add("category")
            ->add("isPublished")
            ->add("isHomePage");
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Page Info")
                ->add("id")
                ->add("title")
                ->add("slug")
                ->add("content")
                ->add("template")
                ->add("category")
                ->add("author")
                ->add("sortOrder")
                ->add("isPublished")
                ->add("isHomePage")
            ->end()
            ->with("Basic SEO")
                ->add("metaTitle")
                ->add("metaDescription")
            ->end()
            ->with("Open Graph")
                ->add("ogTitle")
                ->add("ogDescription")
                ->add("ogImage")
                ->add("ogType")
            ->end()
            ->with("Twitter Card")
                ->add("twitterCard")
                ->add("twitterTitle")
                ->add("twitterDescription")
                ->add("twitterImage")
            ->end()
            ->with("Structured Data")
                ->add("schemaType")
                ->add("structuredData", null, [
                    "template" => "@SonataAdmin/CRUD/show_html.html.twig"
                ])
            ->end()
            ->with("Timestamps")
                ->add("createdAt")
                ->add("updatedAt")
            ->end();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add("duplicate", $this->getRouterIdParameter()."/duplicate");
    }

    public function duplicateAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf("Unable to find Page with id: %s", $id));
        }

        // Create a new page with copied data
        $duplicate = new Page();
        $duplicate->setTitle($object->getTitle() . " (Copy)");
        $duplicate->setSlug($object->getSlug() . "-copy-" . time());
        $duplicate->setContent($object->getContent());
        $duplicate->setTemplate($object->getTemplate());
        
        // Copy SEO fields
        $duplicate->setMetaTitle($object->getMetaTitle());
        $duplicate->setMetaDescription($object->getMetaDescription());
        $duplicate->setMetaKeywords($object->getMetaKeywords());
        $duplicate->setOgTitle($object->getOgTitle());
        $duplicate->setOgDescription($object->getOgDescription());
        $duplicate->setOgImage($object->getOgImage());
        $duplicate->setOgType($object->getOgType());
        $duplicate->setTwitterCard($object->getTwitterCard());
        $duplicate->setTwitterTitle($object->getTwitterTitle());
        $duplicate->setTwitterDescription($object->getTwitterDescription());
        $duplicate->setTwitterImage($object->getTwitterImage());
        $duplicate->setStructuredData($object->getStructuredData());
        $duplicate->setSchemaType($object->getSchemaType());
        
        $duplicate->setIsPublished(false); // Set to draft by default
        $duplicate->setIsHomePage(false);
        $duplicate->setSortOrder($object->getSortOrder());
        $duplicate->setAuthor($object->getAuthor());
        $duplicate->setCategory($object->getCategory());
        $duplicate->setLanguage($object->getLanguage());

        $this->getModelManager()->create($duplicate);

        $this->addFlash("sonata_flash_success", "Page duplicated successfully!");

        return new RedirectResponse($this->generateUrl("list"));
    }

    public function toString(object $object): string
    {
        return $object instanceof Page
            ? $object->getTitle() ?? "Page"
            : "Page";
    }
}
