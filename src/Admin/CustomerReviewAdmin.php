<?php

namespace App\Admin;

use App\Entity\CustomerReview;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper, ProxyQueryInterface};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\{TextType, TextareaType, IntegerType, CheckboxType, ChoiceType};

final class CustomerReviewAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('deleteAll', 'delete-all');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Customer Information", ["class" => "col-md-6"])
                ->add("customerName", TextType::class, [
                    "label" => "Customer Name",
                    "help" => "Name of the customer giving the review"
                ])
                ->add("customerTitle", TextType::class, [
                    "label" => "Customer Title/Position",
                    "required" => false,
                    "help" => "e.g., CEO, Marketing Manager, etc."
                ])
                ->add("customerLocation", TextType::class, [
                    "label" => "Location",
                    "required" => false,
                    "help" => "e.g., New York, USA"
                ])
                ->add("customerImage", TextType::class, [
                    "label" => "Customer Image URL",
                    "required" => false,
                    "help" => "URL to customer profile image"
                ])
            ->end()
            ->with("Review Details", ["class" => "col-md-6"])
                ->add("reviewText", TextareaType::class, [
                    "label" => "Review Text",
                    "attr" => ["rows" => 6],
                    "help" => "The customer's testimonial"
                ])
                ->add("rating", ChoiceType::class, [
                    "label" => "Rating",
                    "choices" => [
                        "⭐ 1 Star" => 1,
                        "⭐⭐ 2 Stars" => 2,
                        "⭐⭐⭐ 3 Stars" => 3,
                        "⭐⭐⭐⭐ 4 Stars" => 4,
                        "⭐⭐⭐⭐⭐ 5 Stars" => 5,
                    ]
                ])
                ->add("product", ModelType::class, [
                    "class" => "App\\Entity\\Article",
                    "property" => "name",
                    "label" => "Related Product (Optional)",
                    "required" => false,
                    "help" => "Link to a specific product if applicable"
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-12"])
                ->add("isActive", CheckboxType::class, [
                    "required" => false,
                    "label" => "Active",
                    "help" => "Show this review on the site"
                ])
                ->add("isFeatured", CheckboxType::class, [
                    "required" => false,
                    "label" => "Featured",
                    "help" => "Mark as featured review"
                ])
                ->add("isVerified", CheckboxType::class, [
                    "required" => false,
                    "label" => "Verified",
                    "help" => "Mark as verified customer"
                ])
                ->add("sortOrder", IntegerType::class, [
                    "label" => "Sort Order",
                    "help" => "Higher numbers appear first (0-100)",
                    "attr" => ["min" => 0, "max" => 100]
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add("customerName", null, [
                "label" => "Customer"
            ])
            ->add("rating", "choice", [
                "label" => "Rating",
                "choices" => [
                    1 => "⭐",
                    2 => "⭐⭐",
                    3 => "⭐⭐⭐",
                    4 => "⭐⭐⭐⭐",
                    5 => "⭐⭐⭐⭐⭐",
                ]
            ])
            ->add("product", null, [
                "label" => "Product",
                "associated_property" => "name"
            ])
            ->add("isVerified", null, [
                "label" => "Verified",
                "editable" => true
            ])
            ->add("isFeatured", null, [
                "label" => "Featured",
                "editable" => true
            ])
            ->add("isActive", null, [
                "label" => "Active",
                "editable" => true
            ])
            ->add("createdAt", null, [
                "label" => "Created",
                "format" => "Y-m-d"
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "delete" => []
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("customerName", null, [
                "label" => "Customer Name"
            ])
            ->add("rating", null, [
                "label" => "Rating"
            ])
            ->add("isActive", null, [
                "label" => "Active"
            ])
            ->add("isFeatured", null, [
                "label" => "Featured"
            ])
            ->add("isVerified", null, [
                "label" => "Verified"
            ])
            ->add("product", null, [
                "label" => "Product"
            ])
            ->add("createdAt", null, [
                "label" => "Created Date"
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Customer", ["class" => "col-md-6"])
                ->add("id")
                ->add("customerName", null, [
                    "label" => "Name"
                ])
                ->add("customerTitle", null, [
                    "label" => "Title/Position"
                ])
                ->add("customerLocation", null, [
                    "label" => "Location"
                ])
                ->add("customerImage", null, [
                    "label" => "Image URL"
                ])
            ->end()
            ->with("Review", ["class" => "col-md-6"])
                ->add("reviewText", null, [
                    "label" => "Review Text"
                ])
                ->add("rating", "choice", [
                    "label" => "Rating",
                    "choices" => [
                        1 => "⭐ 1 Star",
                        2 => "⭐⭐ 2 Stars",
                        3 => "⭐⭐⭐ 3 Stars",
                        4 => "⭐⭐⭐⭐ 4 Stars",
                        5 => "⭐⭐⭐⭐⭐ 5 Stars",
                    ]
                ])
                ->add("product", null, [
                    "label" => "Related Product",
                    "associated_property" => "name"
                ])
            ->end()
            ->with("Status & Settings", ["class" => "col-md-12"])
                ->add("isActive", null, [
                    "label" => "Active"
                ])
                ->add("isFeatured", null, [
                    "label" => "Featured"
                ])
                ->add("isVerified", null, [
                    "label" => "Verified Customer"
                ])
                ->add("sortOrder", null, [
                    "label" => "Sort Order"
                ])
            ->end()
            ->with("Timestamps", ["class" => "col-md-12"])
                ->add("createdAt")
                ->add("updatedAt")
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof CustomerReview
            ? $object->__toString()
            : "Customer Review";
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'createdAt';
        $sortValues['_sort_order'] = 'DESC';
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions['delete_all'] = [
            'label' => 'Delete All Reviews',
            'ask_confirmation' => true
        ];

        return $actions;
    }
}
