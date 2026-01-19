<?php

namespace App\Admin;

use App\Entity\Booking;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class BookingAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Booking Information', ['class' => 'col-md-6'])
                ->add('bookingType', ChoiceType::class, [
                    'choices' => [
                        'Room' => 'room',
                        'Event' => 'event',
                        'Timeslot' => 'timeslot',
                    ]
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Pending' => 'pending',
                        'Confirmed' => 'confirmed',
                        'Cancelled' => 'cancelled',
                        'Completed' => 'completed',
                    ]
                ])
                ->add('user', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'username',
                    'label' => 'Customer'
                ])
                ->add('quantity', IntegerType::class, [
                    'help' => 'Number of units booked'
                ])
            ->end()
            ->with('Schedule', ['class' => 'col-md-6'])
                ->add('startDate', DateTimeType::class, [
                    'widget' => 'single_text',
                    'html5' => true,
                ])
                ->add('endDate', DateTimeType::class, [
                    'widget' => 'single_text',
                    'html5' => true,
                ])
            ->end()
            ->with('Pricing', ['class' => 'col-md-6'])
                ->add('totalPrice', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Total price for this booking'
                ])
                ->add('deposit', NumberType::class, [
                    'required' => false,
                    'scale' => 2,
                    'help' => 'Deposit amount paid'
                ])
                ->add('currency', TextType::class, [
                    'help' => 'Currency code (e.g., EUR, USD)'
                ])
            ->end()
            ->with('Additional Information', ['class' => 'col-md-12'])
                ->add('notes', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 4],
                    'help' => 'Internal notes about this booking'
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('bookingType', 'choice', [
                'choices' => [
                    'room' => 'Room',
                    'event' => 'Event',
                    'timeslot' => 'Timeslot',
                ]
            ])
            ->add('user')
            ->add('startDate')
            ->add('endDate')
            ->add('totalPrice', 'currency', [
                'currency' => 'EUR'
            ])
            ->add('status', 'choice', [
                'choices' => [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                ],
                'editable' => true,
            ])
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('bookingType')
            ->add('status')
            ->add('user')
            ->add('startDate')
            ->add('endDate')
            ->add('totalPrice');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('bookingType')
            ->add('status')
            ->add('user')
            ->add('startDate')
            ->add('endDate')
            ->add('totalPrice')
            ->add('deposit')
            ->add('currency')
            ->add('quantity')
            ->add('details')
            ->add('notes')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Booking
            ? 'Booking #' . $object->getId() . ' - ' . $object->getBookingType()
            : 'Booking';
    }
}
