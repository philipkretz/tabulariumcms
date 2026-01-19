<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Add login attempt tracking and account lockout features
 */
final class Version20260114120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add login_attempt table and account lockout fields to user table';
    }

    public function up(Schema $schema): void
    {
        // Create login_attempt table
        $this->addSql('CREATE TABLE login_attempt (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL,
            was_successful TINYINT(1) NOT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            INDEX idx_username_attempted (username, attempted_at),
            INDEX idx_ip_attempted (ip_address, attempted_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add lockout fields to user table
        $this->addSql('ALTER TABLE user
            ADD is_account_locked TINYINT(1) NOT NULL DEFAULT 0,
            ADD locked_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD failed_login_attempts INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // Drop login_attempt table
        $this->addSql('DROP TABLE login_attempt');

        // Remove lockout fields from user table
        $this->addSql('ALTER TABLE user
            DROP is_account_locked,
            DROP locked_until,
            DROP failed_login_attempts');
    }
}
