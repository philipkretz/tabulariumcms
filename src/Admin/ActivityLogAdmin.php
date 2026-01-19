<?php

namespace App\Admin;

use App\Entity\ActivityLog;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class ActivityLogAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 100, // Show 100 items per page for big log view
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Read-only admin - no create, edit, or delete
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('actionType', null, [
                'label' => 'Action Type',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Admin Login' => ActivityLog::TYPE_ADMIN_LOGIN,
                        'Admin Logout' => ActivityLog::TYPE_ADMIN_LOGOUT,
                        'User Registration' => ActivityLog::TYPE_USER_REGISTER,
                        'User Login' => ActivityLog::TYPE_USER_LOGIN,
                        'User Logout' => ActivityLog::TYPE_USER_LOGOUT,
                        'Seller Registration' => ActivityLog::TYPE_SELLER_REGISTER,
                        'Seller Approved' => ActivityLog::TYPE_SELLER_APPROVED,
                        'Seller Rejected' => ActivityLog::TYPE_SELLER_REJECTED,
                        'Product Created' => ActivityLog::TYPE_PRODUCT_CREATED,
                        'Product Updated' => ActivityLog::TYPE_PRODUCT_UPDATED,
                        'Product Deleted' => ActivityLog::TYPE_PRODUCT_DELETED,
                        'Order Created' => ActivityLog::TYPE_ORDER_CREATED,
                        'Order Updated' => ActivityLog::TYPE_ORDER_UPDATED,
                        'Order Paid' => ActivityLog::TYPE_ORDER_PAID,
                        'Order Shipped' => ActivityLog::TYPE_ORDER_SHIPPED,
                        'Order Completed' => ActivityLog::TYPE_ORDER_COMPLETED,
                        'Order Cancelled' => ActivityLog::TYPE_ORDER_CANCELLED,
                        'Payment Success' => ActivityLog::TYPE_PAYMENT_SUCCESS,
                        'Payment Failed' => ActivityLog::TYPE_PAYMENT_FAILED,
                        'Seller Sale' => ActivityLog::TYPE_SELLER_SALE,
                        'Seller Payout' => ActivityLog::TYPE_SELLER_PAYOUT,
                        'Page Created' => ActivityLog::TYPE_PAGE_CREATED,
                        'Page Updated' => ActivityLog::TYPE_PAGE_UPDATED,
                        'Page Deleted' => ActivityLog::TYPE_PAGE_DELETED,
                        'Post Created' => ActivityLog::TYPE_POST_CREATED,
                        'Post Updated' => ActivityLog::TYPE_POST_UPDATED,
                        'Post Deleted' => ActivityLog::TYPE_POST_DELETED,
                        'Settings Updated' => ActivityLog::TYPE_SETTINGS_UPDATED,
                        'Admin Action' => ActivityLog::TYPE_ADMIN_ACTION,
                    ],
                    'multiple' => true,
                ]
            ])
            ->add('user', null, ['label' => 'User'])
            ->add('description', null, ['label' => 'Description'])
            ->add('entityType', null, ['label' => 'Entity Type'])
            ->add('ipAddress', null, ['label' => 'IP Address'])
            ->add('createdAt', DateTimeRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateTimeRangePickerType::class,
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('createdAt', null, [
                'label' => 'Time',
                'format' => 'Y-m-d H:i:s',
                'template' => '@App/admin/activity_log/list_created_at.html.twig'
            ])
            ->add('actionType', 'choice', [
                'label' => 'Action',
                'choices' => [
                    ActivityLog::TYPE_ADMIN_LOGIN => 'Admin Login',
                    ActivityLog::TYPE_ADMIN_LOGOUT => 'Admin Logout',
                    ActivityLog::TYPE_USER_REGISTER => 'User Registration',
                    ActivityLog::TYPE_USER_LOGIN => 'User Login',
                    ActivityLog::TYPE_USER_LOGOUT => 'User Logout',
                    ActivityLog::TYPE_SELLER_REGISTER => 'Seller Registration',
                    ActivityLog::TYPE_SELLER_APPROVED => 'Seller Approved',
                    ActivityLog::TYPE_SELLER_REJECTED => 'Seller Rejected',
                    ActivityLog::TYPE_PRODUCT_CREATED => 'Product Created',
                    ActivityLog::TYPE_PRODUCT_UPDATED => 'Product Updated',
                    ActivityLog::TYPE_PRODUCT_DELETED => 'Product Deleted',
                    ActivityLog::TYPE_ORDER_CREATED => 'Order Created',
                    ActivityLog::TYPE_ORDER_UPDATED => 'Order Updated',
                    ActivityLog::TYPE_ORDER_PAID => 'Order Paid',
                    ActivityLog::TYPE_ORDER_SHIPPED => 'Order Shipped',
                    ActivityLog::TYPE_ORDER_COMPLETED => 'Order Completed',
                    ActivityLog::TYPE_ORDER_CANCELLED => 'Order Cancelled',
                    ActivityLog::TYPE_PAYMENT_SUCCESS => 'Payment Success',
                    ActivityLog::TYPE_PAYMENT_FAILED => 'Payment Failed',
                    ActivityLog::TYPE_SELLER_SALE => 'Seller Sale',
                    ActivityLog::TYPE_SELLER_PAYOUT => 'Seller Payout',
                    ActivityLog::TYPE_PAGE_CREATED => 'Page Created',
                    ActivityLog::TYPE_PAGE_UPDATED => 'Page Updated',
                    ActivityLog::TYPE_PAGE_DELETED => 'Page Deleted',
                    ActivityLog::TYPE_POST_CREATED => 'Post Created',
                    ActivityLog::TYPE_POST_UPDATED => 'Post Updated',
                    ActivityLog::TYPE_POST_DELETED => 'Post Deleted',
                    ActivityLog::TYPE_SETTINGS_UPDATED => 'Settings Updated',
                    ActivityLog::TYPE_ADMIN_ACTION => 'Admin Action',
                ],
                'template' => '@App/admin/activity_log/list_action_type.html.twig'
            ])
            ->add('user', null, [
                'label' => 'User',
                'template' => '@App/admin/activity_log/list_user.html.twig'
            ])
            ->add('description', null, [
                'label' => 'Description',
                'template' => '@App/admin/activity_log/list_description.html.twig'
            ])
            ->add('entityType', null, [
                'label' => 'Entity'
            ])
            ->add('entityId', null, [
                'label' => 'ID'
            ])
            ->add('ipAddress', null, [
                'label' => 'IP'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Activity Details', ['class' => 'col-md-6'])
                ->add('actionType', 'choice', [
                    'label' => 'Action Type',
                    'choices' => [
                        ActivityLog::TYPE_USER_REGISTER => 'User Registration',
                        ActivityLog::TYPE_USER_LOGIN => 'User Login',
                        ActivityLog::TYPE_SELLER_REGISTER => 'Seller Registration',
                        ActivityLog::TYPE_PRODUCT_CREATED => 'Product Created',
                        ActivityLog::TYPE_ORDER_CREATED => 'Order Created',
                    ]
                ])
                ->add('description')
                ->add('user')
                ->add('entityType', null, ['label' => 'Related Entity Type'])
                ->add('entityId', null, ['label' => 'Related Entity ID'])
            ->end()
            ->with('Request Information', ['class' => 'col-md-6'])
                ->add('ipAddress', null, ['label' => 'IP Address'])
                ->add('userAgent', null, ['label' => 'User Agent'])
                ->add('createdAt', null, ['label' => 'Timestamp', 'format' => 'Y-m-d H:i:s'])
            ->end()
            ->with('Metadata')
                ->add('metadata', 'array', [
                    'label' => 'Additional Data'
                ])
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof ActivityLog
            ? sprintf('[%s] %s', $object->getCreatedAt()?->format('Y-m-d H:i:s'), $object->getDescription())
            : 'Activity Log';
    }
}
