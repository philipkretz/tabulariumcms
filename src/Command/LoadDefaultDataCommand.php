<?php

namespace App\Command;

use App\Entity\ShippingMethod;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-default-data',
    description: 'Load default shipping and payment methods',
)]
class LoadDefaultDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Load Shipping Methods
        $io->section('Loading Shipping Methods');
        $this->loadShippingMethods($io);

        // Load Payment Methods
        $io->section('Loading Payment Methods');
        $this->loadPaymentMethods($io);

        $this->entityManager->flush();

        $io->success('Default data loaded successfully!');

        return Command::SUCCESS;
    }

    private function loadShippingMethods(SymfonyStyle $io): void
    {
        $shippingData = [
            [
                'name' => 'Standard Shipping',
                'description' => 'Standard delivery within 5-7 business days',
                'price' => '4.99',
                'deliveryDays' => 7,
                'deliveryTime' => '5-7 business days',
                'sortOrder' => 1,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Fast delivery within 1-2 business days',
                'price' => '12.99',
                'deliveryDays' => 2,
                'deliveryTime' => '1-2 business days',
                'sortOrder' => 2,
            ],
        ];

        foreach ($shippingData as $data) {
            $existing = $this->entityManager->getRepository(ShippingMethod::class)
                ->findOneBy(['name' => $data['name']]);

            if (!$existing) {
                $method = new ShippingMethod();
                $method->setName($data['name'])
                    ->setDescription($data['description'])
                    ->setPrice($data['price'])
                    ->setDeliveryDays($data['deliveryDays'])
                    ->setDeliveryTime($data['deliveryTime'])
                    ->setSortOrder($data['sortOrder'])
                    ->setIsActive(true);

                $this->entityManager->persist($method);
                $io->writeln(sprintf('  - Created: %s', $data['name']));
            } else {
                $io->writeln(sprintf('  - Exists: %s', $data['name']));
            }
        }
    }

    private function loadPaymentMethods(SymfonyStyle $io): void
    {
        $paymentData = [
            [
                'name' => 'Prepayment',
                'type' => PaymentMethod::TYPE_PREPAYMENT,
                'description' => 'Pay in advance via bank transfer',
                'fee' => '0.00',
                'sortOrder' => 1,
            ],
            [
                'name' => 'Payment at Store',
                'type' => PaymentMethod::TYPE_AT_STORE,
                'description' => 'Pay when picking up at the store',
                'fee' => '0.00',
                'sortOrder' => 2,
            ],
            [
                'name' => 'Stripe',
                'type' => PaymentMethod::TYPE_STRIPE,
                'description' => 'Credit/Debit card payment via Stripe',
                'fee' => '0.00',
                'sortOrder' => 3,
            ],
            [
                'name' => 'PayPal',
                'type' => PaymentMethod::TYPE_PAYPAL,
                'description' => 'Pay securely with PayPal',
                'fee' => '0.00',
                'sortOrder' => 4,
            ],
            [
                'name' => 'Amazon Pay',
                'type' => PaymentMethod::TYPE_AMAZON_PAY,
                'description' => 'Use your Amazon account to pay',
                'fee' => '0.00',
                'sortOrder' => 5,
            ],
            [
                'name' => 'Klarna',
                'type' => PaymentMethod::TYPE_KLARNA,
                'description' => 'Buy now, pay later with Klarna',
                'fee' => '0.00',
                'sortOrder' => 6,
            ],
            [
                'name' => 'AliPay',
                'type' => PaymentMethod::TYPE_ALIPAY,
                'description' => 'Pay with Alipay',
                'fee' => '0.00',
                'sortOrder' => 7,
            ],
            [
                'name' => 'BitPay (Bitcoin)',
                'type' => PaymentMethod::TYPE_BITPAY,
                'description' => 'Pay with Bitcoin via BitPay',
                'fee' => '0.00',
                'sortOrder' => 8,
            ],
            [
                'name' => 'Google Pay',
                'type' => PaymentMethod::TYPE_GOOGLE_PAY,
                'description' => 'Fast and secure payment with Google Pay',
                'fee' => '0.00',
                'sortOrder' => 9,
            ],
        ];

        foreach ($paymentData as $data) {
            $existing = $this->entityManager->getRepository(PaymentMethod::class)
                ->findOneBy(['type' => $data['type']]);

            if (!$existing) {
                $method = new PaymentMethod();
                $method->setName($data['name'])
                    ->setType($data['type'])
                    ->setDescription($data['description'])
                    ->setFee($data['fee'])
                    ->setSortOrder($data['sortOrder'])
                    ->setIsActive(true);

                $this->entityManager->persist($method);
                $io->writeln(sprintf('  - Created: %s', $data['name']));
            } else {
                $io->writeln(sprintf('  - Exists: %s', $data['name']));
            }
        }
    }
}
