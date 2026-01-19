<?php

namespace App\Admin;

use App\Entity\NewsletterCampaign;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

final class NewsletterCampaignAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Campaign Details', ['class' => 'col-md-8'])
                ->add('subject', TextType::class, [
                    'label' => 'Subject Line',
                    'help' => 'The subject line of the newsletter email'
                ])
                ->add('content', CKEditorType::class, [
                    'label' => 'Email Content (HTML)',
                    'required' => true,
                    'config' => [
                        'toolbar' => 'full',
                        'height' => 500,
                    ],
                    'help' => 'Use the editor to compose your newsletter content'
                ])
                ->add('plainTextContent', TextareaType::class, [
                    'label' => 'Plain Text Version',
                    'required' => false,
                    'attr' => ['rows' => 10],
                    'help' => 'Optional plain text version for email clients that don\'t support HTML'
                ])
            ->end()
            ->with('Sender Information', ['class' => 'col-md-4'])
                ->add('fromName', TextType::class, [
                    'label' => 'From Name',
                    'required' => false,
                    'help' => 'Leave empty to use default site name'
                ])
                ->add('fromEmail', TextType::class, [
                    'label' => 'From Email',
                    'required' => false,
                    'help' => 'Leave empty to use default site email'
                ])
            ->end()
            ->with('Scheduling', ['class' => 'col-md-4'])
                ->add('scheduledAt', DateTimeType::class, [
                    'label' => 'Schedule Send Time',
                    'required' => false,
                    'widget' => 'single_text',
                    'help' => 'Leave empty to send immediately when clicking "Send Now"'
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Status',
                    'choices' => [
                        'Draft' => NewsletterCampaign::STATUS_DRAFT,
                        'Sending' => NewsletterCampaign::STATUS_SENDING,
                        'Completed' => NewsletterCampaign::STATUS_COMPLETED,
                        'Failed' => NewsletterCampaign::STATUS_FAILED,
                    ],
                    'disabled' => true,
                    'help' => 'Status is automatically managed'
                ])
            ->end();

        // Show statistics if campaign has been sent
        if ($this->getSubject() && $this->getSubject()->getId()) {
            $campaign = $this->getSubject();
            $form
                ->with('Campaign Statistics', ['class' => 'col-md-4'])
                    ->add('totalRecipients', TextType::class, [
                        'label' => 'Total Recipients',
                        'disabled' => true,
                        'data' => $campaign->getTotalRecipients()
                    ])
                    ->add('sentCount', TextType::class, [
                        'label' => 'Successfully Sent',
                        'disabled' => true,
                        'data' => $campaign->getSentCount()
                    ])
                    ->add('failedCount', TextType::class, [
                        'label' => 'Failed',
                        'disabled' => true,
                        'data' => $campaign->getFailedCount()
                    ])
                ->end();
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('subject')
            ->add('status', null, [], ChoiceType::class, [
                'choices' => [
                    'Draft' => NewsletterCampaign::STATUS_DRAFT,
                    'Sending' => NewsletterCampaign::STATUS_SENDING,
                    'Completed' => NewsletterCampaign::STATUS_COMPLETED,
                    'Failed' => NewsletterCampaign::STATUS_FAILED,
                ]
            ])
            ->add('createdAt')
            ->add('sentAt');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('subject', null, [
                'label' => 'Subject'
            ])
            ->add('status', 'choice', [
                'label' => 'Status',
                'choices' => [
                    NewsletterCampaign::STATUS_DRAFT => 'Draft',
                    NewsletterCampaign::STATUS_SENDING => 'Sending',
                    NewsletterCampaign::STATUS_COMPLETED => 'Completed',
                    NewsletterCampaign::STATUS_FAILED => 'Failed',
                ],
                'template' => 'admin/newsletter_campaign/list_status.html.twig'
            ])
            ->add('totalRecipients', null, [
                'label' => 'Recipients'
            ])
            ->add('sentCount', null, [
                'label' => 'Sent'
            ])
            ->add('failedCount', null, [
                'label' => 'Failed'
            ])
            ->add('progressPercentage', null, [
                'label' => 'Progress',
                'template' => 'admin/newsletter_campaign/list_progress.html.twig'
            ])
            ->add('createdAt', null, [
                'label' => 'Created',
                'format' => 'Y-m-d H:i'
            ])
            ->add('sentAt', null, [
                'label' => 'Sent',
                'format' => 'Y-m-d H:i'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'preview' => [
                        'template' => 'admin/newsletter_campaign/list_action_preview.html.twig'
                    ],
                    'test' => [
                        'template' => 'admin/newsletter_campaign/list_action_test.html.twig'
                    ],
                    'send' => [
                        'template' => 'admin/newsletter_campaign/list_action_send.html.twig'
                    ],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('preview', $this->getRouterIdParameter().'/preview');
        $collection->add('test', $this->getRouterIdParameter().'/test');
        $collection->add('send', $this->getRouterIdParameter().'/send');
    }
}
