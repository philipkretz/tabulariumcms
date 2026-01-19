<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260117100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lookingFor and offering fields to user_profile table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_profile
            ADD looking_for LONGTEXT DEFAULT NULL,
            ADD offering LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_profile
            DROP looking_for,
            DROP offering
        ');
    }
}
