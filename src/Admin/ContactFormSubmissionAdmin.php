<?php
namespace App\Admin;

use App\Entity\ContactFormSubmission;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

final class ContactFormSubmissionAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove("create");
        $collection->remove("edit");
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add("form")
            ->add("submittedAt", "datetime")
            ->add("ipAddress")
            ->add("isRead", null, ["editable" => true])
            ->add(ListMapper::NAME_ACTIONS, null, ["actions" => ["show" => [], "delete" => []]]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add("form")
            ->add("data", "array")
            ->add("ipAddress")
            ->add("userAgent")
            ->add("submittedAt", "datetime")
            ->add("isRead");
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add("form")->add("isRead")->add("submittedAt");
    }

    public function toString(object $object): string
    {
        return $object instanceof ContactFormSubmission ? (string)$object : "Form Submission";
    }
}