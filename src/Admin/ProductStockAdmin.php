<?php

namespace App\Admin;

use App\Entity\ProductStock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ProductStockAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Product & Location", ["class" => "col-md-6"])
                ->add("article", ModelType::class, [
                    "class" => "App\Entity\Article",
                    "property" => "name",
                    "help" => "Product to manage stock for"
                ])
                ->add("store", ModelType::class, [
                    "class" => "App\Entity\Store",
                    "property" => "name",
                    "help" => "Store location"
                ])
            ->end()
            ->with("Stock Settings", ["class" => "col-md-6"])
                ->add("trackStock", CheckboxType::class, [
                    "required" => false,
                    "help" => "Enable stock tracking (uncheck for unlimited stock)"
                ])
                ->add("allowBackorder", CheckboxType::class, [
                    "required" => false,
                    "help" => "Allow orders when out of stock"
                ])
            ->end()
            ->with("Inventory", ["class" => "col-md-12"])
                ->add("quantity", IntegerType::class, [
                    "help" => "Available quantity in this store"
                ])
                ->add("reservedQuantity", IntegerType::class, [
                    "required" => false,
                    "help" => "Quantity reserved for pending orders"
                ])
                ->add("minQuantity", IntegerType::class, [
                    "required" => false,
                    "help" => "Minimum quantity threshold for alerts"
                ])
            ->end()
            ->with("Pricing & POS", ["class" => "col-md-12"])
                ->add("storePrice", NumberType::class, [
                    "required" => false,
                    "scale" => 2,
                    "help" => "Store-specific price (overrides default product price)"
                ])
                ->add("posProductId", TextType::class, [
                    "required" => false,
                    "help" => "Product ID in POS system"
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add("article", null, [
                "label" => "Product"
            ])
            ->add("store")
            ->add("quantity", null, [
                "label" => "In Stock"
            ])
            ->add("reservedQuantity", null, [
                "label" => "Reserved"
            ])
            ->add("availableQuantity", "integer", [
                "label" => "Available",
                "template" => "@SonataAdmin/CRUD/list_integer.html.twig"
            ])
            ->add("trackStock", null, [
                "editable" => true,
                "label" => "Track"
            ])
            ->add("allowBackorder", null, [
                "label" => "Backorder"
            ])
            ->add("storePrice", "currency", [
                "label" => "Store Price",
                "currency" => "EUR"
            ])
            ->add("lastSyncAt", "datetime", [
                "label" => "Last Sync"
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "delete" => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("article")
            ->add("store")
            ->add("trackStock")
            ->add("quantity");
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add("id")
            ->add("article")
            ->add("store")
            ->add("quantity")
            ->add("reservedQuantity")
            ->add("minQuantity")
            ->add("trackStock")
            ->add("allowBackorder")
            ->add("storePrice")
            ->add("posProductId")
            ->add("lastSyncAt")
            ->add("updatedAt");
    }

    public function toString(object $object): string
    {
        return $object instanceof ProductStock
            ? $object->__toString()
            : "Product Stock";
    }
}
