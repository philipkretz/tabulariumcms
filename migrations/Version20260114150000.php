<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260114150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add theme settings to site_settings table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings
            ADD logo_path VARCHAR(255) DEFAULT \'tabulariumcms.png\',
            ADD site_name VARCHAR(100) DEFAULT \'TabulariumCMS\',
            ADD primary_color VARCHAR(7) NOT NULL DEFAULT \'#d97706\',
            ADD secondary_color VARCHAR(7) NOT NULL DEFAULT \'#b45309\',
            ADD accent_color VARCHAR(7) NOT NULL DEFAULT \'#92400e\',
            ADD navigation_bg_color VARCHAR(7) NOT NULL DEFAULT \'#fef3c7\',
            ADD navigation_text_color VARCHAR(7) NOT NULL DEFAULT \'#92400e\',
            ADD button_color VARCHAR(7) NOT NULL DEFAULT \'#d97706\',
            ADD button_hover_color VARCHAR(7) NOT NULL DEFAULT \'#b45309\',
            ADD breakpoint_mobile INT NOT NULL DEFAULT 768,
            ADD breakpoint_tablet INT NOT NULL DEFAULT 1024,
            ADD breakpoint_desktop INT NOT NULL DEFAULT 1280,
            ADD breakpoint_xl INT NOT NULL DEFAULT 1536,
            ADD container_max_width INT NOT NULL DEFAULT 1280,
            ADD logo_size_multiplier INT NOT NULL DEFAULT 3
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings
            DROP logo_path,
            DROP site_name,
            DROP primary_color,
            DROP secondary_color,
            DROP accent_color,
            DROP navigation_bg_color,
            DROP navigation_text_color,
            DROP button_color,
            DROP button_hover_color,
            DROP breakpoint_mobile,
            DROP breakpoint_tablet,
            DROP breakpoint_desktop,
            DROP breakpoint_xl,
            DROP container_max_width,
            DROP logo_size_multiplier
        ');
    }
}
