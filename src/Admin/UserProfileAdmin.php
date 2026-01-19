<?php
namespace App\Admin;

use App\Entity\UserProfile;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, TextareaType, TextType};

final class UserProfileAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Profile", ["class" => "col-md-6"])
                ->add("user", ModelType::class, [
                    "class" => "App\Entity\User",
                    "property" => "username"
                ])
                ->add("biography", TextareaType::class, ["required" => false, "attr" => ["rows" => 5]])
                ->add("avatar", ModelType::class, [
                    "class" => "App\Entity\Media",
                    "property" => "filename",
                    "required" => false
                ])
                ->add("isPublic", CheckboxType::class, ["required" => false])
            ->end()
            ->with("Social Links", ["class" => "col-md-6"])
                ->add("website", TextType::class, ["required" => false])
                ->add("twitter", TextType::class, ["required" => false])
                ->add("facebook", TextType::class, ["required" => false])
                ->add("instagram", TextType::class, ["required" => false])
                ->add("linkedin", TextType::class, ["required" => false])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add("user")
            ->add("isPublic", null, ["editable" => true])
            ->add("createdAt", "datetime")
            ->add(ListMapper::NAME_ACTIONS, null, ["actions" => ["show" => [], "edit" => [], "delete" => []]]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add("user")->add("isPublic");
    }

    public function toString(object $object): string
    {
        return $object instanceof UserProfile ? (string)$object : "User Profile";
    }
}