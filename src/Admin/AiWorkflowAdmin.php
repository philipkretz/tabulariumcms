<?php
namespace App\Admin;

use App\Entity\AiWorkflow;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, ChoiceType, TextareaType, TextType};

final class AiWorkflowAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with("Workflow Info", ["class" => "col-md-6"])
                ->add("name", TextType::class)
                ->add("description", TextareaType::class, ["required" => false, "attr" => ["rows" => 3]])
                ->add("aiProvider", ChoiceType::class, [
                    "choices" => [
                        "OpenAI" => "openai",
                        "Google Gemini" => "gemini",
                        "Anthropic Claude" => "claude"
                    ]
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-6"])
                ->add("isActive", CheckboxType::class, ["required" => false])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("name")
            ->add("aiProvider")
            ->add("isActive", null, ["editable" => true])
            ->add("createdAt", "datetime")
            ->add(ListMapper::NAME_ACTIONS, null, ["actions" => ["show" => [], "edit" => [], "delete" => []]]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add("name")->add("aiProvider")->add("isActive");
    }

    public function toString(object $object): string
    {
        return $object instanceof AiWorkflow ? $object->getName() : "AI Workflow";
    }
}