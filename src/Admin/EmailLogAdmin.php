<?php

namespace App\Admin;

use App\Entity\EmailLog;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextareaType, TextType};

final class EmailLogAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        // Email logs are read-only, but show fields for viewing
        $form
            ->with("Email Details", ["class" => "col-md-6"])
                ->add("recipient", TextType::class, [
                    "disabled" => true,
                    "label" => "Recipient Email"
                ])
                ->add("recipientName", TextType::class, [
                    "required" => false,
                    "disabled" => true,
                    "label" => "Recipient Name"
                ])
                ->add("subject", TextType::class, [
                    "disabled" => true,
                    "label" => "Subject"
                ])
                ->add("status", ChoiceType::class, [
                    "disabled" => true,
                    "choices" => [
                        "Pending" => EmailLog::STATUS_PENDING,
                        "Sent" => EmailLog::STATUS_SENT,
                        "Failed" => EmailLog::STATUS_FAILED,
                        "Bounced" => EmailLog::STATUS_BOUNCED,
                    ]
                ])
            ->end()
            ->with("Sender Information", ["class" => "col-md-6"])
                ->add("fromEmail", TextType::class, [
                    "required" => false,
                    "disabled" => true,
                    "label" => "From Email"
                ])
                ->add("fromName", TextType::class, [
                    "required" => false,
                    "disabled" => true,
                    "label" => "From Name"
                ])
                ->add("templateCode", TextType::class, [
                    "required" => false,
                    "disabled" => true,
                    "label" => "Template Code"
                ])
            ->end()
            ->with("Email Body", ["class" => "col-md-12"])
                ->add("body", TextareaType::class, [
                    "disabled" => true,
                    "label" => "HTML Body",
                    "attr" => ["rows" => 15, "class" => "code-editor"]
                ])
            ->end();

        if ($this->getSubject() && $this->getSubject()->getErrorMessage()) {
            $form->with("Error Information", ["class" => "col-md-12"])
                ->add("errorMessage", TextareaType::class, [
                    "disabled" => true,
                    "label" => "Error Message",
                    "attr" => ["rows" => 5]
                ])
            ->end();
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add("id", null, [
                "label" => "ID"
            ])
            ->add("sentAt", "datetime", [
                "label" => "Sent At",
                "format" => "Y-m-d H:i:s"
            ])
            ->add("recipient", null, [
                "label" => "Recipient"
            ])
            ->add("subject", null, [
                "label" => "Subject"
            ])
            ->add("templateCode", null, [
                "label" => "Template"
            ])
            ->add("status", "choice", [
                "label" => "Status",
                "choices" => [
                    EmailLog::STATUS_PENDING => "Pending",
                    EmailLog::STATUS_SENT => "Sent",
                    EmailLog::STATUS_FAILED => "Failed",
                    EmailLog::STATUS_BOUNCED => "Bounced",
                ],
                "template" => "@App/admin/email_log/list_status.html.twig"
            ])
            ->add("openedAt", "datetime", [
                "label" => "Opened",
                "format" => "Y-m-d H:i",
                "template" => "@App/admin/email_log/list_opened.html.twig"
            ])
            ->add("retryCount", null, [
                "label" => "Retries"
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "delete" => []
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("recipient", null, [
                "label" => "Recipient Email"
            ])
            ->add("subject", null, [
                "label" => "Subject"
            ])
            ->add("status", null, [
                "label" => "Status"
            ])
            ->add("templateCode", null, [
                "label" => "Template Code"
            ])
            ->add("sentAt", null, [
                "label" => "Sent Date"
            ])
            ->add("fromEmail", null, [
                "label" => "From Email"
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Email Information", ["class" => "col-md-6"])
                ->add("id")
                ->add("recipient", null, [
                    "label" => "Recipient Email"
                ])
                ->add("recipientName", null, [
                    "label" => "Recipient Name"
                ])
                ->add("subject")
                ->add("status", "choice", [
                    "choices" => [
                        EmailLog::STATUS_PENDING => "Pending",
                        EmailLog::STATUS_SENT => "Sent",
                        EmailLog::STATUS_FAILED => "Failed",
                        EmailLog::STATUS_BOUNCED => "Bounced",
                    ]
                ])
                ->add("templateCode", null, [
                    "label" => "Template Code"
                ])
                ->add("template", null, [
                    "label" => "Template"
                ])
            ->end()
            ->with("Sender Information", ["class" => "col-md-6"])
                ->add("fromEmail", null, [
                    "label" => "From Email"
                ])
                ->add("fromName", null, [
                    "label" => "From Name"
                ])
            ->end()
            ->with("Timestamps", ["class" => "col-md-6"])
                ->add("sentAt", null, [
                    "label" => "Sent At",
                    "format" => "Y-m-d H:i:s"
                ])
                ->add("deliveredAt", null, [
                    "label" => "Delivered At",
                    "format" => "Y-m-d H:i:s"
                ])
                ->add("openedAt", null, [
                    "label" => "Opened At",
                    "format" => "Y-m-d H:i:s"
                ])
                ->add("clickedAt", null, [
                    "label" => "Clicked At",
                    "format" => "Y-m-d H:i:s"
                ])
            ->end()
            ->with("Tracking", ["class" => "col-md-6"])
                ->add("retryCount", null, [
                    "label" => "Retry Count"
                ])
                ->add("relatedEntity", null, [
                    "label" => "Related Entity"
                ])
                ->add("relatedEntityId", null, [
                    "label" => "Related Entity ID"
                ])
            ->end()
            ->with("Email Content", ["class" => "col-md-12"])
                ->add("body", "html", [
                    "label" => "HTML Body"
                ])
                ->add("plainTextBody", null, [
                    "label" => "Plain Text Body"
                ])
            ->end();

        if ($this->getSubject() && $this->getSubject()->getErrorMessage()) {
            $show->with("Error Information", ["class" => "col-md-12"])
                ->add("errorMessage", null, [
                    "label" => "Error Message"
                ])
            ->end();
        }

        if ($this->getSubject() && $this->getSubject()->getAttachments()) {
            $show->with("Attachments", ["class" => "col-md-12"])
                ->add("attachments", null, [
                    "label" => "Attachments"
                ])
            ->end();
        }
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Remove create and edit routes - email logs are read-only
        $collection->remove('create');
        $collection->remove('edit');

        // Add custom routes
        $collection->add('resend', $this->getRouterIdParameter().'/resend');
        $collection->add('statistics');
    }

    public function toString(object $object): string
    {
        return $object instanceof EmailLog
            ? $object->__toString()
            : "Email Log";
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'sentAt';
        $sortValues['_sort_order'] = 'DESC';
    }

    protected function configureBatchActions(array $actions): array
    {
        // Remove edit from batch actions
        unset($actions['edit']);

        // Keep delete for cleanup
        return $actions;
    }
}
