<?php

namespace App\Admin;

use App\Entity\TaxRate;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TaxRateAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Tax Rate Information", ["class" => "col-md-6"])
                ->add("name", TextType::class, [
                    "label" => "Name",
                    "help" => "Tax rate name (e.g., \"Standard VAT\", \"Reduced VAT\", \"Zero Rate\")"
                ])
                ->add("rate", NumberType::class, [
                    "label" => "Rate (%)",
                    "scale" => 2,
                    "help" => "Tax rate percentage (e.g., 19.00 for 19%)"
                ])
                ->add("description", TextareaType::class, [
                    "label" => "Description",
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Optional description of when this rate applies"
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-6"])
                ->add("countryCode", ChoiceType::class, [
                    "label" => "Country",
                    "required" => false,
                    "choices" => [
                        "Germany" => "DE",
                        "Austria" => "AT",
                        "Switzerland" => "CH",
                        "United States" => "US",
                        "United Kingdom" => "GB",
                        "France" => "FR",
                        "Italy" => "IT",
                        "Spain" => "ES",
                        "Netherlands" => "NL",
                        "Belgium" => "BE",
                    ],
                    "help" => "Country where this tax rate applies (optional)"
                ])
                ->add("isDefault", CheckboxType::class, [
                    "label" => "Default Tax Rate",
                    "required" => false,
                    "help" => "Set as the default tax rate for new products"
                ])
                ->add("isActive", CheckboxType::class, [
                    "label" => "Active",
                    "required" => false,
                    "help" => "Only active tax rates can be selected"
                ])
                ->add("sortOrder", NumberType::class, [
                    "label" => "Sort Order",
                    "required" => false,
                    "help" => "Display order (lower numbers appear first)"
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("name")
            ->add("rate")
            ->add("countryCode", null, ["label" => "Country"])
            ->add("isDefault", null, ["label" => "Is Default"])
            ->add("isActive", null, ["label" => "Active"]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("name", null, [
                "label" => "Tax Rate Name"
            ])
            ->add("rate", "decimal", [
                "label" => "Rate (%)"
            ])
            ->add("countryCode", null, [
                "label" => "Country"
            ])
            ->add("isDefault", null, [
                "label" => "Default",
                "editable" => true
            ])
            ->add("isActive", null, [
                "label" => "Active",
                "editable" => true
            ])
            ->add("sortOrder", null, [
                "label" => "Sort"
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
            ->with("Tax Rate Information")
                ->add("name")
                ->add("rate", "decimal", ["label" => "Rate (%)"])
                ->add("description")
            ->end()
            ->with("Settings")
                ->add("countryCode", null, ["label" => "Country Code"])
                ->add("isDefault", null, ["label" => "Is Default"])
                ->add("isActive", null, ["label" => "Active"])
                ->add("sortOrder")
            ->end()
            ->with("Timestamps")
                ->add("createdAt", null, ["format" => "Y-m-d H:i:s"])
                ->add("updatedAt", null, ["format" => "Y-m-d H:i:s"])
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof TaxRate && $object->getName()
            ? sprintf("%s (%s%%)", $object->getName(), $object->getRate())
            : "Tax Rate";
    }
}
