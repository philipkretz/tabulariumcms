<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260113205107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE allow_comments allow_comments TINYINT NOT NULL');
        $this->addSql('ALTER TABLE customer_review CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_review RENAME INDEX idx_a98173a94584665a TO IDX_C8865D974584665A');
        $this->addSql('ALTER TABLE email_log CHANGE status status VARCHAR(50) NOT NULL, CHANGE retry_count retry_count INT DEFAULT NULL, CHANGE sent_at sent_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL, CHANGE opened_at opened_at DATETIME DEFAULT NULL, CHANGE clicked_at clicked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE email_log RENAME INDEX idx_6fb48e0f5da0fb8 TO IDX_6FB48835DA0FB8');
        $this->addSql('DROP INDEX idx_intelligent_agent_active ON intelligent_agent');
        $this->addSql('DROP INDEX idx_intelligent_agent_trigger ON intelligent_agent');
        $this->addSql('DROP INDEX idx_intelligent_agent_slug ON intelligent_agent');
        $this->addSql('DROP INDEX idx_intelligent_agent_type ON intelligent_agent');
        $this->addSql('ALTER TABLE intelligent_agent CHANGE type type VARCHAR(50) NOT NULL, CHANGE model model VARCHAR(100) DEFAULT NULL, CHANGE temperature temperature DOUBLE PRECISION DEFAULT NULL, CHANGE max_tokens max_tokens INT DEFAULT NULL, CHANGE is_active is_active TINYINT NOT NULL, CHANGE priority priority INT NOT NULL, CHANGE execution_count execution_count INT NOT NULL, CHANGE success_count success_count INT NOT NULL, CHANGE failure_count failure_count INT NOT NULL, CHANGE last_executed_at last_executed_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE intelligent_agent RENAME INDEX slug TO UNIQ_3151A1A9989D9B62');
        $this->addSql('ALTER TABLE `order` ADD title VARCHAR(20) DEFAULT NULL, ADD first_name VARCHAR(100) DEFAULT NULL, ADD last_name VARCHAR(100) DEFAULT NULL, ADD customer_name VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD shipping_address_line2 LONGTEXT DEFAULT NULL, ADD billing_address_line2 LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_settings CHANGE ecommerce_enabled ecommerce_enabled TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE allow_comments allow_comments TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE customer_review CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE customer_review RENAME INDEX idx_c8865d974584665a TO IDX_A98173A94584665A');
        $this->addSql('ALTER TABLE email_log CHANGE status status VARCHAR(50) DEFAULT \'pending\' NOT NULL, CHANGE retry_count retry_count INT DEFAULT 0, CHANGE sent_at sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE opened_at opened_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE clicked_at clicked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email_log RENAME INDEX idx_6fb48835da0fb8 TO IDX_6FB48E0F5DA0FB8');
        $this->addSql('ALTER TABLE intelligent_agent CHANGE type type VARCHAR(50) DEFAULT \'custom\' NOT NULL, CHANGE model model VARCHAR(100) DEFAULT \'gpt-4\', CHANGE temperature temperature DOUBLE PRECISION DEFAULT \'0.7\', CHANGE max_tokens max_tokens INT DEFAULT 2000, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL, CHANGE priority priority INT DEFAULT 0 NOT NULL, CHANGE execution_count execution_count INT DEFAULT 0 NOT NULL, CHANGE success_count success_count INT DEFAULT 0 NOT NULL, CHANGE failure_count failure_count INT DEFAULT 0 NOT NULL, CHANGE last_executed_at last_executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX idx_intelligent_agent_active ON intelligent_agent (is_active)');
        $this->addSql('CREATE INDEX idx_intelligent_agent_trigger ON intelligent_agent (trigger_event)');
        $this->addSql('CREATE INDEX idx_intelligent_agent_slug ON intelligent_agent (slug)');
        $this->addSql('CREATE INDEX idx_intelligent_agent_type ON intelligent_agent (type)');
        $this->addSql('ALTER TABLE intelligent_agent RENAME INDEX uniq_3151a1a9989d9b62 TO slug');
        $this->addSql('ALTER TABLE `order` DROP title, DROP first_name, DROP last_name, DROP customer_name, DROP email, DROP phone, DROP shipping_address_line2, DROP billing_address_line2');
        $this->addSql('ALTER TABLE site_settings CHANGE ecommerce_enabled ecommerce_enabled TINYINT DEFAULT 1 NOT NULL');
    }
}
