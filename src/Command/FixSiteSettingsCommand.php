<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-site-settings',
    description: 'Fixes the missing setting_name column in site_settings table'
)]
class FixSiteSettingsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();
        
        try {
            // Check if column exists
            $tableExists = $connection->fetchOne("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() 
                AND table_name = 'site_settings' 
                AND column_name = 'setting_name'
            ");
            
            if (!$tableExists) {
                $output->writeln('Adding setting_name column to site_settings table...');
                $connection->executeStatement('ALTER TABLE site_settings ADD COLUMN setting_name VARCHAR(255) DEFAULT NULL');
                $output->writeln('Column added successfully!');
            } else {
                $output->writeln('Column setting_name already exists.');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}