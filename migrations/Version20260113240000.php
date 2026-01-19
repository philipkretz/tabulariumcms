<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260113240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop product_recommendation table - no longer needed';
    }

    public function up(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE product_recommendation DROP FOREIGN KEY FK_F41C2F95953C1C61');
        $this->addSql('ALTER TABLE product_recommendation DROP FOREIGN KEY FK_F41C2F9596C4048E');

        // Drop the table
        $this->addSql('DROP TABLE product_recommendation');
    }

    public function down(Schema $schema): void
    {
        // Recreate table if needed to rollback
        $this->addSql('CREATE TABLE product_recommendation (
            id INT AUTO_INCREMENT NOT NULL,
            source_product_id INT DEFAULT NULL,
            recommended_product_id INT DEFAULT NULL,
            sort_order INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_F41C2F95953C1C61 (source_product_id),
            INDEX IDX_F41C2F9596C4048E (recommended_product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE product_recommendation ADD CONSTRAINT FK_F41C2F95953C1C61
            FOREIGN KEY (source_product_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_recommendation ADD CONSTRAINT FK_F41C2F9596C4048E
            FOREIGN KEY (recommended_product_id) REFERENCES article (id) ON DELETE CASCADE');
    }
}
