<?php

namespace App\Admin;

use App\Entity\FrontendTemplate;
use App\Form\Type\GrapeJsEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class FrontendTemplateAdmin extends AbstractAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return "template";
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return "admin_app_template";
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEdit = $this->getSubject() && $this->getSubject()->getId();

        $form
            ->with("Basic Information", ["class" => "col-md-6"])
                ->add("name", TextType::class, [
                    "label" => "Template Name",
                    "help" => "Human-readable name for this template"
                ])
                ->add("templateKey", TextType::class, [
                    "label" => "Template Key",
                    "help" => "Unique identifier (e.g., \"cart_view\", \"product_detail\")",
                    "disabled" => $isEdit
                ])
                ->add("category", ChoiceType::class, [
                    "label" => "Category",
                    "choices" => [
                        "General" => "general",
                        "E-Commerce" => "ecommerce",
                        "User Account" => "account",
                        "Seller" => "seller",
                        "Checkout" => "checkout",
                        "Email" => "email",
                    ],
                    "help" => "Template category for organization"
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-6"])
                ->add("isActive", CheckboxType::class, [
                    "required" => false,
                    "label" => "Active",
                    "help" => "Enable this template for use on the frontend"
                ])
                ->add("isEditable", CheckboxType::class, [
                    "required" => false,
                    "label" => "Editable",
                    "help" => "Mark this template as editable (informational only - admin users can always edit)"
                ])
            ->end()
            ->with("Description & Documentation", ["class" => "col-md-12"])
                ->add("description", TextareaType::class, [
                    "required" => false,
                    "label" => "Description",
                    "help" => "Describe what this template is used for",
                    "attr" => ["rows" => 3]
                ])
            ->end()
            ->with("Template Code", ["class" => "col-md-12"])
                ->add("content", GrapeJsEditorType::class, [
                    "label" => "Twig Template Code",
                    "help" => "Enter the Twig template code. Use the visual editor or code view. Supports Twig syntax: {{ variable }}, {% if %}, {% for %}",
                    "editor_height" => "650px"
                ])
            ->end()
            ->with("Basic SEO", ["class" => "col-md-6"])
                ->add("metaTitle", TextType::class, [
                    "required" => false,
                    "help" => "Default title for pages using this template (50-60 characters)"
                ])
                ->add("metaDescription", TextareaType::class, [
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Default description for search results (150-160 characters)"
                ])
            ->end()
            ->with("Open Graph (Facebook)", ["class" => "col-md-6"])
                ->add("ogTitle", TextType::class, [
                    "label" => "OG Title",
                    "required" => false,
                    "help" => "Default title when pages with this template are shared on Facebook"
                ])
                ->add("ogDescription", TextareaType::class, [
                    "label" => "OG Description",
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Default description for Facebook sharing"
                ])
                ->add("ogImage", TextType::class, [
                    "label" => "OG Image URL",
                    "required" => false,
                    "help" => "Default image URL for Facebook sharing (1200x630px recommended)"
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
                    "help" => "Default Twitter card layout"
                ])
                ->add("twitterTitle", TextType::class, [
                    "label" => "Twitter Title",
                    "required" => false,
                    "help" => "Default title when shared on Twitter"
                ])
                ->add("twitterDescription", TextareaType::class, [
                    "label" => "Twitter Description",
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Default description for Twitter sharing"
                ])
                ->add("twitterImage", TextType::class, [
                    "label" => "Twitter Image URL",
                    "required" => false,
                    "help" => "Default image URL for Twitter card"
                ])
            ->end()
            ->with("Intelligent Agents / Schema.org", ["class" => "col-md-6"])
                ->add("schemaType", ChoiceType::class, [
                    "label" => "Schema Type",
                    "required" => false,
                    "choices" => [
                        "WebPage" => "WebPage",
                        "Article" => "Article",
                        "Product" => "Product",
                        "Service" => "Service",
                        "FAQPage" => "FAQPage",
                    ],
                    "help" => "Default Schema.org type for structured data"
                ])
                ->add("structuredData", TextareaType::class, [
                    "label" => "Structured Data (JSON-LD)",
                    "required" => false,
                    "attr" => ["rows" => 8, "style" => "font-family: monospace;"],
                    "help" => "Default JSON-LD structured data for AI agents and rich snippets"
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("name")
            ->add("templateKey")
            ->add("category", null, [
                "field_type" => ChoiceType::class,
                "field_options" => [
                    "choices" => [
                        "General" => "general",
                        "E-Commerce" => "ecommerce",
                        "User Account" => "account",
                        "Seller" => "seller",
                        "Checkout" => "checkout",
                        "Email" => "email",
                    ],
                ]
            ])
            ->add("isActive", null, ["label" => "Active"])
            ->add("isEditable", null, ["label" => "Editable"])
            ->add("createdAt");
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("name", null, [
                "label" => "Template Name"
            ])
            ->add("templateKey", null, [
                "label" => "Key"
            ])
            ->add("category", "choice", [
                "label" => "Category",
                "choices" => [
                    "general" => "General",
                    "ecommerce" => "E-Commerce",
                    "account" => "User Account",
                    "seller" => "Seller",
                    "checkout" => "Checkout",
                    "email" => "Email",
                ]
            ])
            ->add("isActive", null, [
                "label" => "Active",
                "editable" => true
            ])
            ->add("isEditable", null, [
                "label" => "Editable",
                "editable" => true
            ])
            ->add("updatedAt", null, [
                "label" => "Last Updated",
                "format" => "Y-m-d H:i"
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "delete" => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Basic Information")
                ->add("name")
                ->add("templateKey")
                ->add("category")
                ->add("description")
            ->end()
            ->with("Settings")
                ->add("isActive", null, ["label" => "Active"])
                ->add("isEditable", null, ["label" => "Editable"])
            ->end()
            ->with("Template Code")
                ->add("content", "text", [
                    "template" => "@SonataAdmin/CRUD/show_html.html.twig"
                ])
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
            ->with("Metadata")
                ->add("createdAt")
                ->add("updatedAt")
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof FrontendTemplate && $object->getName()
            ? $object->getName()
            : "Template";
    }
}
