<?php

namespace App\Admin;

use App\Entity\Order;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class OrderAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $order = $this->getSubject();
        $isEdit = $order && $order->getId();

        $form
            ->with("Order Information", ["class" => "col-md-6"])
                ->add("orderNumber", TextType::class, [
                    "disabled" => $isEdit,
                    "help" => "Unique order number (auto-generated)"
                ])
                ->add("status", ChoiceType::class, [
                    "choices" => [
                        "Pending" => Order::STATUS_PENDING,
                        "Payment Received" => Order::STATUS_PAYMENT_RECEIVED,
                        "Processing" => Order::STATUS_PROCESSING,
                        "Shipped" => Order::STATUS_SHIPPED,
                        "Delivered" => Order::STATUS_DELIVERED,
                        "Cancelled" => Order::STATUS_CANCELLED,
                        "Refunded" => Order::STATUS_REFUNDED,
                    ],
                    "help" => "Current order status"
                ])
                ->add("customer", ModelType::class, [
                    "class" => "App\Entity\User",
                    "required" => false,
                    "property" => "email",
                    "help" => "Registered customer (leave empty for guest checkout)"
                ])
            ->end()
            ->with("Customer Details", ["class" => "col-md-6"])
                ->add("guestName", TextType::class, [
                    "required" => false,
                    "help" => "Guest customer name"
                ])
                ->add("guestEmail", TextType::class, [
                    "required" => false,
                    "help" => "Guest customer email"
                ])
                ->add("guestPhone", TextType::class, [
                    "required" => false,
                    "help" => "Guest customer phone"
                ])
            ->end()
            ->with("Shipping Address", ["class" => "col-md-6"])
                ->add("shippingAddress", TextareaType::class, [
                    "attr" => ["rows" => 3]
                ])
                ->add("shippingCity", TextType::class)
                ->add("shippingPostcode", TextType::class)
                ->add("shippingCountry", TextType::class, [
                    "help" => "2-letter country code (e.g., DE, US)"
                ])
            ->end()
            ->with("Billing Address", ["class" => "col-md-6"])
                ->add("billingAddress", TextareaType::class, [
                    "attr" => ["rows" => 3]
                ])
                ->add("billingCity", TextType::class)
                ->add("billingPostcode", TextType::class)
                ->add("billingCountry", TextType::class, [
                    "help" => "2-letter country code (e.g., DE, US)"
                ])
            ->end()
            ->with("Payment & Shipping", ["class" => "col-md-6"])
                ->add("paymentMethod", ModelType::class, [
                    "class" => "App\Entity\PaymentMethod",
                    "property" => "name",
                    "help" => "Payment method selected by customer"
                ])
                ->add("shippingMethod", ModelType::class, [
                    "class" => "App\Entity\ShippingMethod",
                    "property" => "name",
                    "help" => "Shipping method selected by customer"
                ])
                ->add("voucherCode", ModelType::class, [
                    "class" => "App\Entity\VoucherCode",
                    "property" => "code",
                    "required" => false,
                    "help" => "Discount voucher used (if any)"
                ])
                ->add("trackingNumber", TextType::class, [
                    "required" => false,
                    "help" => "Shipment tracking number"
                ])
            ->end()
            ->with("Notes", ["class" => "col-md-6"])
                ->add("customerNotes", TextareaType::class, [
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Customer comments/instructions"
                ])
                ->add("adminNotes", TextareaType::class, [
                    "required" => false,
                    "attr" => ["rows" => 3],
                    "help" => "Internal admin notes"
                ])
            ->end()
            ->with("Order Items", ["class" => "col-md-12"])
                ->add("items", CollectionType::class, [
                    "by_reference" => false,
                    "required" => false,
                    "label" => "Products",
                    "btn_add" => "Add Product",
                ], [
                    "edit" => "inline",
                    "inline" => "table",
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("orderNumber")
            ->add("status", "choice", [
                "choices" => [
                    Order::STATUS_PENDING => "Pending",
                    Order::STATUS_PAYMENT_RECEIVED => "Payment Received",
                    Order::STATUS_PROCESSING => "Processing",
                    Order::STATUS_SHIPPED => "Shipped",
                    Order::STATUS_DELIVERED => "Delivered",
                    Order::STATUS_CANCELLED => "Cancelled",
                    Order::STATUS_REFUNDED => "Refunded",
                ]
            ])
            ->add("customer", null, [
                "label" => "Customer"
            ])
            ->add("total", "decimal", [
                "label" => "Total Amount"
            ])
            ->add("paymentMethod")
            ->add("shippingMethod")
            ->add("createdAt", "datetime", [
                "label" => "Order Date"
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => [
                    "show" => [],
                    "edit" => [],
                    "mark_paid" => [
                        "template" => "@App/admin/order/list__action_mark_paid.html.twig"
                    ],
                    "mark_shipped" => [
                        "template" => "@App/admin/order/list__action_mark_shipped.html.twig"
                    ],
                    "delete" => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("orderNumber")
            ->add("status", null, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        "Pending" => Order::STATUS_PENDING,
                        "Payment Received" => Order::STATUS_PAYMENT_RECEIVED,
                        "Processing" => Order::STATUS_PROCESSING,
                        "Shipped" => Order::STATUS_SHIPPED,
                        "Delivered" => Order::STATUS_DELIVERED,
                        "Cancelled" => Order::STATUS_CANCELLED,
                        "Refunded" => Order::STATUS_REFUNDED,
                    ]
                ]
            ])
            ->add("customer")
            ->add("total")
            ->add("createdAt");
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with("Order Information", ["class" => "col-md-6"])
                ->add("id")
                ->add("orderNumber")
                ->add("status")
                ->add("customer")
                ->add("guestName")
                ->add("guestEmail")
                ->add("guestPhone")
            ->end()
            ->with("Addresses", ["class" => "col-md-6"])
                ->add("shippingAddress")
                ->add("shippingCity")
                ->add("shippingPostcode")
                ->add("shippingCountry")
                ->add("billingAddress")
                ->add("billingCity")
                ->add("billingPostcode")
                ->add("billingCountry")
            ->end()
            ->with("Payment & Shipping", ["class" => "col-md-6"])
                ->add("paymentMethod")
                ->add("shippingMethod")
                ->add("voucherCode")
                ->add("trackingNumber")
            ->end()
            ->with("Amounts", ["class" => "col-md-6"])
                ->add("subtotal")
                ->add("shippingCost")
                ->add("discount")
                ->add("taxAmount")
                ->add("total")
            ->end()
            ->with("Notes", ["class" => "col-md-12"])
                ->add("customerNotes")
                ->add("adminNotes")
            ->end()
            ->with("Dates", ["class" => "col-md-12"])
                ->add("createdAt")
                ->add("updatedAt")
                ->add("completedAt")
            ->end();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add("mark_paid", $this->getRouterIdParameter()."/mark-paid");
        $collection->add("mark_shipped", $this->getRouterIdParameter()."/mark-shipped");
    }

    public function markPaidAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf("Unable to find Order with id: %s", $id));
        }

        $object->setStatus(Order::STATUS_PAYMENT_RECEIVED);
        $this->getModelManager()->update($object);

        $this->addFlash("sonata_flash_success", sprintf("Order %s marked as paid!", $object->getOrderNumber()));

        return new RedirectResponse($this->generateUrl("list"));
    }

    public function markShippedAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf("Unable to find Order with id: %s", $id));
        }

        $object->setStatus(Order::STATUS_SHIPPED);
        $this->getModelManager()->update($object);

        $this->addFlash("sonata_flash_success", sprintf("Order %s marked as shipped!", $object->getOrderNumber()));

        return new RedirectResponse($this->generateUrl("list"));
    }

    public function toString(object $object): string
    {
        return $object instanceof Order
            ? $object->getOrderNumber() ?? "Order"
            : "Order";
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues["_sort_by"] = "createdAt";
        $sortValues["_sort_order"] = "DESC";
    }
}
