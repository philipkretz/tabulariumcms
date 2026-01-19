<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260113234500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create customer_review table for customer testimonials';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer_review (
            id INT AUTO_INCREMENT NOT NULL,
            product_id INT DEFAULT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_title VARCHAR(255) DEFAULT NULL,
            review_text LONGTEXT NOT NULL,
            rating INT NOT NULL,
            is_active TINYINT(1) NOT NULL,
            is_featured TINYINT(1) NOT NULL,
            customer_image VARCHAR(255) DEFAULT NULL,
            customer_location VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            is_verified TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            INDEX IDX_A98173A94584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE customer_review ADD CONSTRAINT FK_A98173A94584665A
            FOREIGN KEY (product_id) REFERENCES article (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_review DROP FOREIGN KEY FK_A98173A94584665A');
        $this->addSql('DROP TABLE customer_review');
    }
}
