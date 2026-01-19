<?php
namespace App\Admin;

use App\Entity\ContactForm;
use App\Entity\ContactFormField;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, TextareaType, TextType};

final class ContactFormAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Form Info", ["class" => "col-md-6"])
                ->add("name", TextType::class)
                ->add("identifier", TextType::class, ["help" => "Unique identifier for embedding"])
                ->add("description", TextareaType::class, ["required" => false, "attr" => ["rows" => 3]])
            ->end()
            ->with("Settings", ["class" => "col-md-6"])
                ->add("submitButtonText", TextType::class)
                ->add("successMessage", TextType::class)
                ->add("sendEmail", CheckboxType::class, ["required" => false, "help" => "Send submissions to admin email"])
                ->add("isActive", CheckboxType::class, ["required" => false])
            ->end()
            ->with("Form Fields", ["class" => "col-md-12"])
                ->add("fields", CollectionType::class, [
                    "by_reference" => false,
                    "type_options" => ["delete" => true],
                ], [
                    "edit" => "inline",
                    "inline" => "table",
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("name")
            ->add("identifier")
            ->add("isActive", null, ["editable" => true])
            ->add("createdAt", "datetime")
            ->add(ListMapper::NAME_ACTIONS, null, ["actions" => ["show" => [], "edit" => [], "delete" => []]]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add("name")->add("identifier")->add("isActive");
    }

    public function toString(object $object): string
    {
        return $object instanceof ContactForm ? $object->getName() : "Contact Form";
    }
}