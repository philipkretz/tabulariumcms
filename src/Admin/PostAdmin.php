<?php

namespace App\Admin;

use App\Entity\Post;
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class PostAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Post Content", ["class" => "col-md-8"])
                ->add("title", TextType::class)
                ->add("slug", TextType::class)
                ->add("excerpt", TextareaType::class, [
                    "required" => false,
                    "attr" => ["rows" => 3]
                ])
                ->add("content", GrapeJsEditorType::class, [
                    "label" => "Post Content - Visual Editor",
                    "editor_height" => "700px",
                    "help" => "Visual editor with drag & drop blocks and live preview.",
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-4"])
                ->add("status", ChoiceType::class, [
                    "choices" => [
                        "Draft" => "draft",
                        "Published" => "published",
                        "Pending Review" => "pending",
                        "Scheduled" => "scheduled",
                        "Archived" => "archived",
                    ]
                ])
                ->add("language", ModelType::class, [
                    "class" => "App\\Entity\\Language",
                    "property" => "name",
                    "required" => false,
                    "help" => "Select the language for this post"
                ])
                ->add("author", ModelType::class, [
                    "class" => "App\\Entity\\User",
                    "property" => "username"
                ])
                ->add("page", ModelType::class, [
                    "class" => "App\\Entity\\Page",
                    "property" => "title",
                    "required" => false
                ])
                ->add("featuredImage", ModelType::class, [
                    "class" => "App\\Entity\\Media",
                    "property" => "filename",
                    "required" => false
                ])
                ->add("isCommentEnabled", CheckboxType::class, ["required" => false])
                ->add("isPinned", CheckboxType::class, ["required" => false])
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
                        "Article" => "article",
                        "Website" => "website",
                        "Blog" => "blog",
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
                        "BlogPosting" => "BlogPosting",
                        "Article" => "Article",
                        "NewsArticle" => "NewsArticle",
                        "TechArticle" => "TechArticle",
                        "ScholarlyArticle" => "ScholarlyArticle",
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
            ->add("status", "choice", [
                "choices" => [
                    "draft" => "Draft",
                    "published" => "Published",
                    "pending" => "Pending Review",
                    "scheduled" => "Scheduled",
                    "archived" => "Archived",
                ]
            ])
            ->add("author")
            ->add("viewCount")
            ->add("isPinned", null, ["editable" => true])
            ->add("createdAt")
            ->add("publishedAt")
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "duplicate" => [
                        "template" => "@App/admin/post/list__action_duplicate.html.twig"
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
            ->add("status", null, [
                "field_type" => ChoiceType::class,
                "field_options" => [
                    "choices" => [
                        "Draft" => "draft",
                        "Published" => "published",
                        "Pending Review" => "pending",
                        "Scheduled" => "scheduled",
                        "Archived" => "archived",
                    ]
                ]
            ])
            ->add("author")
            ->add("language")
            ->add("isPinned")
            ->add("isCommentEnabled");
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Post Info")
                ->add("id")
                ->add("title")
                ->add("slug")
                ->add("excerpt")
                ->add("content")
                ->add("featuredImage")
                ->add("status")
                ->add("author")
                ->add("language")
                ->add("viewCount")
                ->add("isPinned")
                ->add("isCommentEnabled")
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
                ->add("publishedAt")
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
            throw $this->createNotFoundException(sprintf("Unable to find Post with id: %s", $id));
        }

        // Create a new post with copied data
        $duplicate = new Post();
        $duplicate->setTitle($object->getTitle() . " (Copy)");
        $duplicate->setSlug($object->getSlug() . "-copy-" . time());
        $duplicate->setExcerpt($object->getExcerpt());
        $duplicate->setContent($object->getContent());
        $duplicate->setFeaturedImage($object->getFeaturedImage());
        
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
        
        $duplicate->setStatus("draft"); // Set to draft by default
        $duplicate->setPinned(false);
        $duplicate->setAuthor($object->getAuthor());
        $duplicate->setLanguage($object->getLanguage());

        $this->getModelManager()->create($duplicate);

        $this->addFlash("sonata_flash_success", "Post duplicated successfully!");

        return new RedirectResponse($this->generateUrl("list"));
    }

    public function toString(object $object): string
    {
        return $object instanceof Post
            ? $object->getTitle() ?? "Post"
            : "Post";
    }
}
