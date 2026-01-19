<?php

namespace App\Admin;

use App\Entity\OrderItem;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class OrderItemAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('article', ModelType::class, [
                'class' => 'App\Entity\Article',
                'required' => true,
                'label' => 'Product',
                'property' => 'name',
                'help' => 'Select product from catalog'
            ])
            ->add('quantity', IntegerType::class, [
                'required' => true,
                'label' => 'Quantity',
                'attr' => ['min' => 1]
            ])
            ->add('unitPrice', MoneyType::class, [
                'required' => true,
                'currency' => 'EUR',
                'label' => 'Unit Price',
                'help' => 'Price per item (auto-filled from product)'
            ])
            ->add('taxRate', MoneyType::class, [
                'required' => true,
                'currency' => false,
                'label' => 'Tax Rate %',
                'help' => 'Tax percentage (auto-filled from product)'
            ])
            ->add('articleName', TextType::class, [
                'required' => false,
                'label' => 'Product Name Override',
                'help' => 'Leave empty to use product name'
            ])
            ->add('articleSku', TextType::class, [
                'required' => false,
                'label' => 'SKU Override',
                'help' => 'Leave empty to use product SKU'
            ]);
    }
}
