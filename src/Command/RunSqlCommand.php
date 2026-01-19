<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:run-sql',
    description: 'Run SQL command directly'
)]
class RunSqlCommand extends Command
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Check if column exists using raw SQL
            $sql = "SELECT COUNT(*) FROM information_schema.columns 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'site_settings' 
                     AND column_name = 'setting_name'";
            
            $count = $this->connection->fetchOne($sql);
            
            if ($count == 0) {
                $output->writeln('Adding setting_name column...');
                $this->connection->executeStatement('ALTER TABLE site_settings ADD COLUMN setting_name VARCHAR(255) DEFAULT NULL');
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