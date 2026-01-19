<?php

namespace App\Admin;

use App\Entity\ArticleVariant;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\{TextType, IntegerType, MoneyType, CheckboxType};

final class ArticleVariantAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, [
                'required' => false,
                'help' => 'Variant name (e.g., "Large", "Pack of 6")'
            ])
            ->add('size', TextType::class, [
                'required' => false,
                'help' => 'Size specification (e.g., "XL", "10cm x 20cm")'
            ])
            ->add('amount', TextType::class, [
                'required' => false,
                'help' => 'Amount/quantity (e.g., "500ml", "6 pieces")'
            ])
            ->add('color', TextType::class, [
                'required' => false,
                'help' => 'Color variation (e.g., "Red", "Blue")'
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'help' => 'Variant-specific SKU'
            ])
            ->add('priceModifier', MoneyType::class, [
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Price difference from base product (e.g., +5.00 or -2.50)'
            ])
            ->add('stock', IntegerType::class, [
                'help' => 'Available stock for this variant'
            ])
            ->add('isDefault', CheckboxType::class, [
                'required' => false,
                'help' => 'Set as default variant'
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'help' => 'Enable this variant'
            ])
            ->add('sortOrder', IntegerType::class, [
                'required' => false,
                'help' => 'Display order (lower numbers first)'
            ]);
    }

    public function toString(object $object): string
    {
        return $object instanceof ArticleVariant
            ? (string) $object
            : 'Variant';
    }
}
