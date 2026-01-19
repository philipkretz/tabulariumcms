<?php

namespace App\Admin;

use App\Entity\Seller;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class SellerAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Company Information', ['class' => 'col-md-6'])
                ->add('user', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'email',
                    'help' => 'Link to user account for seller login'
                ])
                ->add('companyName', TextType::class, [
                    'help' => 'Official company name'
                ])
                ->add('businessName', TextType::class, [
                    'required' => false,
                    'help' => 'Trading name (if different from company name)'
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 4],
                    'help' => 'About this seller/company'
                ])
                ->add('logo', ModelType::class, [
                    'class' => 'App\Entity\Media',
                    'property' => 'filename',
                    'required' => false,
                    'help' => 'Company logo'
                ])
            ->end()
            ->with('Contact Information', ['class' => 'col-md-6'])
                ->add('phone', TextType::class, [
                    'required' => false,
                    'help' => 'Business phone number'
                ])
                ->add('website', TextType::class, [
                    'required' => false,
                    'help' => 'Company website URL'
                ])
            ->end()
            ->with('Business Address', ['class' => 'col-md-12'])
                ->add('businessAddress', TextType::class, [
                    'required' => false,
                    'help' => 'Street address'
                ])
                ->add('businessCity', TextType::class, [
                    'required' => false,
                    'help' => 'City'
                ])
                ->add('businessPostcode', TextType::class, [
                    'required' => false,
                    'help' => 'Postal/ZIP code'
                ])
                ->add('businessCountry', ChoiceType::class, [
                    'required' => false,
                    'choices' => [
                        'Germany' => 'DE',
                        'Austria' => 'AT',
                        'Switzerland' => 'CH',
                        'United States' => 'US',
                        'United Kingdom' => 'GB',
                    ],
                    'help' => 'Country (ISO code)'
                ])
            ->end()
            ->with('Tax & Legal', ['class' => 'col-md-6'])
                ->add('taxId', TextType::class, [
                    'required' => false,
                    'help' => 'Tax identification number'
                ])
                ->add('vatNumber', TextType::class, [
                    'required' => false,
                    'help' => 'VAT registration number'
                ])
            ->end()
            ->with('Bank Account', ['class' => 'col-md-6'])
                ->add('bankName', TextType::class, [
                    'required' => false,
                    'help' => 'Bank name'
                ])
                ->add('bankAccountNumber', TextType::class, [
                    'required' => false,
                    'help' => 'Account number'
                ])
                ->add('bankIban', TextType::class, [
                    'required' => false,
                    'help' => 'IBAN'
                ])
                ->add('bankSwift', TextType::class, [
                    'required' => false,
                    'help' => 'SWIFT/BIC code'
                ])
            ->end()
            ->with('Substore', ['class' => 'col-md-12'])
                ->add('substoreName', TextType::class, [
                    'required' => false,
                    'help' => 'Name of seller\'s substore (e.g., "Electronics by ABC")'
                ])
            ->end()
            ->with('Rates & Commission', ['class' => 'col-md-12'])
                ->add('commissionRate', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Platform commission rate on sales (e.g., 10.00 for 10%)',
                    'data' => '10.00'
                ])
                ->add('discountRate', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Discount rate seller gets when buying from main store (e.g., 5.00 for 5%)',
                    'data' => '5.00'
                ])
                ->add('resaleCommissionRate', NumberType::class, [
                    'scale' => 2,
                    'help' => 'Bonus percentage seller earns when reselling products (e.g., 3.00 for 3%)',
                    'data' => '3.00'
                ])
            ->end()
            ->with('Status', ['class' => 'col-md-12'])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Pending Review' => Seller::STATUS_PENDING,
                        'Approved' => Seller::STATUS_APPROVED,
                        'Active' => Seller::STATUS_ACTIVE,
                        'Suspended' => Seller::STATUS_SUSPENDED,
                        'Rejected' => Seller::STATUS_REJECTED,
                    ],
                    'help' => 'Seller account status'
                ])
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'help' => 'Active status (can log in and manage)'
                ])
                ->add('canSellProducts', CheckboxType::class, [
                    'required' => false,
                    'help' => 'Allow seller to list and sell products'
                ])
                ->add('rejectionReason', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 3],
                    'help' => 'Reason for rejection (shown to seller if status is rejected)'
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('companyName')
            ->add('businessName')
            ->add('status', null, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Pending' => Seller::STATUS_PENDING,
                        'Approved' => Seller::STATUS_APPROVED,
                        'Active' => Seller::STATUS_ACTIVE,
                        'Suspended' => Seller::STATUS_SUSPENDED,
                        'Rejected' => Seller::STATUS_REJECTED,
                    ],
                ]
            ])
            ->add('isActive', null, ['label' => 'Active'])
            ->add('canSellProducts', null, ['label' => 'Can Sell'])
            ->add('commissionRate')
            ->add('registeredAt');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('companyName', null, [
                'label' => 'Company / Substore'
            ])
            ->add('user', null, [
                'label' => 'User Account'
            ])
            ->add('substoreName', null, [
                'label' => 'Substore Name'
            ])
            ->add('totalOrders', null, [
                'label' => 'Orders'
            ])
            ->add('totalSales', 'currency', [
                'currency' => 'EUR',
                'label' => 'Total Sales'
            ])
            ->add('totalRevenue', 'currency', [
                'currency' => 'EUR',
                'label' => 'Revenue'
            ])
            ->add('commissionRate', 'decimal', [
                'label' => 'Comm. %'
            ])
            ->add('discountRate', 'decimal', [
                'label' => 'Disc. %'
            ])
            ->add('status', 'choice', [
                'label' => 'Status',
                'choices' => [
                    Seller::STATUS_PENDING => 'Pending',
                    Seller::STATUS_APPROVED => 'Approved',
                    Seller::STATUS_ACTIVE => 'Active',
                    Seller::STATUS_SUSPENDED => 'Suspended',
                    Seller::STATUS_REJECTED => 'Rejected',
                ]
            ])
            ->add('isActive', null, [
                'label' => 'Active',
                'editable' => true
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'approve' => [
                        'template' => '@App/admin/seller/list__action_approve.html.twig'
                    ],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Company Information')
                ->add('companyName')
                ->add('businessName')
                ->add('description')
                ->add('logo')
                ->add('user')
            ->end()
            ->with('Contact')
                ->add('phone')
                ->add('website')
            ->end()
            ->with('Business Address')
                ->add('businessAddress')
                ->add('businessCity')
                ->add('businessPostcode')
                ->add('businessCountry')
            ->end()
            ->with('Tax & Legal')
                ->add('taxId')
                ->add('vatNumber')
            ->end()
            ->with('Bank Account')
                ->add('bankName')
                ->add('bankAccountNumber')
                ->add('bankIban')
                ->add('bankSwift')
            ->end()
            ->with('Commission & Status')
                ->add('commissionRate', 'decimal')
                ->add('status')
                ->add('isActive')
                ->add('canSellProducts')
                ->add('rejectionReason')
            ->end()
            ->with('Dates')
                ->add('registeredAt')
                ->add('approvedAt')
            ->end();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('approve', $this->getRouterIdParameter().'/approve');
    }

    public function approveAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find Seller with id: %s', $id));
        }

        // Approve seller
        $object->setStatus(Seller::STATUS_APPROVED);
        $object->setIsActive(true);
        $object->setCanSellProducts(true);
        $object->setApprovedAt(new \DateTimeImmutable());

        $this->getModelManager()->update($object);

        $this->addFlash('sonata_flash_success', sprintf('Seller "%s" has been approved!', $object->getCompanyName()));

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function toString(object $object): string
    {
        return $object instanceof Seller && $object->getCompanyName()
            ? $object->getCompanyName()
            : 'Seller';
    }
}
