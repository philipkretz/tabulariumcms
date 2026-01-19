<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * TabulariumCMS - Complete Database Schema Migration
 *
 * This migration creates the complete database structure for TabulariumCMS
 * including all tables, indexes, and foreign key constraints.
 *
 * Generated: 2026-01-12
 */
final class Version20260112160019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Complete TabulariumCMS database schema - all tables, indexes, and foreign keys';
    }

    public function up(Schema $schema): void
    {
        // Create all tables in dependency order

        // Core tables (no foreign keys)
        $this->addSql('CREATE TABLE IF NOT EXISTS language (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            locale VARCHAR(10) NOT NULL,
            is_default TINYINT(1) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            flag_code VARCHAR(10) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_D4DB71B54180C698 (locale)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS user (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            first_name VARCHAR(255) DEFAULT NULL,
            last_name VARCHAR(255) DEFAULT NULL,
            is_verified TINYINT(1) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            locale VARCHAR(10) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS category (
            id INT AUTO_INCREMENT NOT NULL,
            parent_id INT DEFAULT NULL,
            language_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug),
            INDEX IDX_64C19C1727ACA70 (parent_id),
            INDEX IDX_64C19C182F1BAF4 (language_id),
            CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE CASCADE,
            CONSTRAINT FK_64C19C182F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS media (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            is_public TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_6A2CA10CA76ED395 (user_id),
            CONSTRAINT FK_6A2CA10CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS page (
            id INT AUTO_INCREMENT NOT NULL,
            language_id INT DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            is_published TINYINT(1) NOT NULL,
            published_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_140AB620989D9B62 (slug),
            INDEX IDX_140AB62082F1BAF4 (language_id),
            INDEX IDX_140AB620727ACA70 (parent_id),
            CONSTRAINT FK_140AB62082F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id),
            CONSTRAINT FK_140AB620727ACA70 FOREIGN KEY (parent_id) REFERENCES page (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS post (
            id INT AUTO_INCREMENT NOT NULL,
            author_id INT DEFAULT NULL,
            language_id INT DEFAULT NULL,
            featured_image_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT DEFAULT NULL,
            excerpt LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            is_published TINYINT(1) NOT NULL,
            is_featured TINYINT(1) NOT NULL,
            published_at DATETIME DEFAULT NULL,
            view_count INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug),
            INDEX IDX_5A8A6C8DF675F31B (author_id),
            INDEX IDX_5A8A6C8D82F1BAF4 (language_id),
            INDEX IDX_5A8A6C8D5B42DC0F (featured_image_id),
            CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL,
            CONSTRAINT FK_5A8A6C8D82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id),
            CONSTRAINT FK_5A8A6C8D5B42DC0F FOREIGN KEY (featured_image_id) REFERENCES media (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS seller (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            company_email VARCHAR(255) NOT NULL,
            company_phone VARCHAR(50) DEFAULT NULL,
            tax_id VARCHAR(50) DEFAULT NULL,
            commission_rate NUMERIC(5, 2) NOT NULL,
            status VARCHAR(50) NOT NULL,
            is_verified TINYINT(1) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            logo_path VARCHAR(255) DEFAULT NULL,
            banner_path VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_FB1AD3FCA76ED395 (user_id),
            CONSTRAINT FK_FB1AD3FCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS shipping_method (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            price NUMERIC(10, 2) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            min_delivery_days INT DEFAULT NULL,
            max_delivery_days INT DEFAULT NULL,
            sort_order INT NOT NULL,
            icon VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article (
            id INT AUTO_INCREMENT NOT NULL,
            main_image_id INT DEFAULT NULL,
            download_file_id INT DEFAULT NULL,
            category_id INT DEFAULT NULL,
            category_page_id INT DEFAULT NULL,
            language_id INT DEFAULT NULL,
            seller_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            short_description LONGTEXT DEFAULT NULL,
            net_price NUMERIC(10, 2) NOT NULL,
            tax_rate NUMERIC(5, 2) NOT NULL,
            gross_price NUMERIC(10, 2) NOT NULL,
            stock INT NOT NULL,
            sku VARCHAR(50) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            is_featured TINYINT(1) NOT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            ignore_stock TINYINT(1) NOT NULL,
            size VARCHAR(100) DEFAULT NULL,
            weight NUMERIC(10, 3) DEFAULT NULL,
            is_dangerous_goods TINYINT(1) NOT NULL,
            is_oversize_package TINYINT(1) NOT NULL,
            requires_special_delivery TINYINT(1) NOT NULL,
            package_amount INT NOT NULL,
            is_request_only TINYINT(1) NOT NULL,
            request_email VARCHAR(255) DEFAULT NULL,
            allow_comments TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug),
            INDEX IDX_23A0E66E4873418 (main_image_id),
            INDEX IDX_23A0E663FB578A9 (download_file_id),
            INDEX IDX_23A0E6612469DE2 (category_id),
            INDEX IDX_23A0E66CB9E239A (category_page_id),
            INDEX IDX_23A0E6682F1BAF4 (language_id),
            INDEX IDX_23A0E668DE820D9 (seller_id),
            CONSTRAINT FK_23A0E66E4873418 FOREIGN KEY (main_image_id) REFERENCES media (id),
            CONSTRAINT FK_23A0E663FB578A9 FOREIGN KEY (download_file_id) REFERENCES media (id),
            CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id),
            CONSTRAINT FK_23A0E66CB9E239A FOREIGN KEY (category_page_id) REFERENCES page (id),
            CONSTRAINT FK_23A0E6682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id),
            CONSTRAINT FK_23A0E668DE820D9 FOREIGN KEY (seller_id) REFERENCES seller (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS `order` (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            order_number VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            payment_status VARCHAR(50) NOT NULL,
            shipping_status VARCHAR(50) NOT NULL,
            subtotal NUMERIC(10, 2) NOT NULL,
            shipping_cost NUMERIC(10, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) NOT NULL,
            total NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            payment_method VARCHAR(100) DEFAULT NULL,
            shipping_method VARCHAR(100) DEFAULT NULL,
            tracking_number VARCHAR(255) DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            guest_email VARCHAR(255) DEFAULT NULL,
            guest_name VARCHAR(255) DEFAULT NULL,
            shipping_address JSON DEFAULT NULL,
            billing_address JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            paid_at DATETIME DEFAULT NULL,
            shipped_at DATETIME DEFAULT NULL,
            delivered_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_F5299398551F0F81 (order_number),
            INDEX IDX_F5299398A76ED395 (user_id),
            CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Additional core tables
        $this->addSql('CREATE TABLE IF NOT EXISTS address (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(255) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            company VARCHAR(255) NOT NULL,
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255) DEFAULT NULL,
            city VARCHAR(255) NOT NULL,
            postal_code VARCHAR(100) NOT NULL,
            country VARCHAR(100) NOT NULL,
            state VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            is_default TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_D4E6F81A76ED395 (user_id),
            CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            action_type VARCHAR(50) NOT NULL,
            description VARCHAR(255) NOT NULL,
            entity_type VARCHAR(100) DEFAULT NULL,
            entity_id INT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX idx_activity_created (created_at),
            INDEX idx_activity_type (action_type),
            INDEX idx_activity_user (user_id),
            CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS ai_workflow (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            ai_provider VARCHAR(50) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS ai_workflow_step (
            id INT AUTO_INCREMENT NOT NULL,
            workflow_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            prompt LONGTEXT NOT NULL,
            sort_order INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            parameters JSON DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_3713D2212C7C2CBA (workflow_id),
            CONSTRAINT FK_3713D2212C7C2CBA FOREIGN KEY (workflow_id) REFERENCES ai_workflow (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS api_key (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            api_key VARCHAR(64) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            rate_limit INT DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            last_used_at DATETIME DEFAULT NULL,
            request_count INT NOT NULL,
            last_request_at DATETIME DEFAULT NULL,
            ip_whitelist JSON DEFAULT NULL,
            permissions JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_C912ED9DC912ED9D (api_key),
            INDEX IDX_C912ED9DA76ED395 (user_id),
            CONSTRAINT FK_C912ED9DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_bundle_items (
            bundle_id INT NOT NULL,
            article_id INT NOT NULL,
            PRIMARY KEY(bundle_id, article_id),
            INDEX IDX_A90A2B77F1FAD9D3 (bundle_id),
            INDEX IDX_A90A2B777294869C (article_id),
            CONSTRAINT FK_A90A2B77F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES article (id),
            CONSTRAINT FK_A90A2B777294869C FOREIGN KEY (article_id) REFERENCES article (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_images (
            article_id INT NOT NULL,
            media_id INT NOT NULL,
            PRIMARY KEY(article_id, media_id),
            INDEX IDX_8AD829EA7294869C (article_id),
            INDEX IDX_8AD829EAEA9FDD75 (media_id),
            CONSTRAINT FK_8AD829EA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_8AD829EAEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_shipping_methods (
            article_id INT NOT NULL,
            shipping_method_id INT NOT NULL,
            PRIMARY KEY(article_id, shipping_method_id),
            INDEX IDX_A7FE8ADE7294869C (article_id),
            INDEX IDX_A7FE8ADE5F7D6850 (shipping_method_id),
            CONSTRAINT FK_A7FE8ADE7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_A7FE8ADE5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES shipping_method (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_translation (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            language_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            short_description LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX article_language_unique (article_id, language_id),
            INDEX IDX_2EEA2F087294869C (article_id),
            INDEX IDX_2EEA2F0882F1BAF4 (language_id),
            CONSTRAINT FK_2EEA2F087294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_2EEA2F0882F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_variant (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            name VARCHAR(100) DEFAULT NULL,
            size VARCHAR(100) DEFAULT NULL,
            amount VARCHAR(100) DEFAULT NULL,
            color VARCHAR(100) DEFAULT NULL,
            sku VARCHAR(50) DEFAULT NULL,
            price_modifier NUMERIC(10, 2) DEFAULT NULL,
            stock INT NOT NULL,
            is_default TINYINT(1) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_1C91FB1A7294869C (article_id),
            CONSTRAINT FK_1C91FB1A7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS article_videos (
            article_id INT NOT NULL,
            media_id INT NOT NULL,
            PRIMARY KEY(article_id, media_id),
            INDEX IDX_436DF3B27294869C (article_id),
            INDEX IDX_436DF3B2EA9FDD75 (media_id),
            CONSTRAINT FK_436DF3B27294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_436DF3B2EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS booking (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            booking_type VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            total_price NUMERIC(10, 2) NOT NULL,
            deposit NUMERIC(10, 2) DEFAULT NULL,
            currency VARCHAR(255) DEFAULT NULL,
            quantity INT NOT NULL,
            details JSON NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_E00CEDDEA76ED395 (user_id),
            CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS bundle_product_items (
            id INT AUTO_INCREMENT NOT NULL,
            bundle_id INT NOT NULL,
            article_id INT NOT NULL,
            quantity INT NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_83239557F1FAD9D3 (bundle_id),
            INDEX IDX_832395577294869C (article_id),
            CONSTRAINT FK_83239557F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_832395577294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS bundle_products (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            discount_amount NUMERIC(10, 2) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_17C947E77294869C (article_id),
            CONSTRAINT FK_17C947E77294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            session_id VARCHAR(255) DEFAULT NULL,
            currency VARCHAR(10) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_BA388B7A76ED395 (user_id),
            INDEX idx_cart_session (session_id),
            CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS cart_item (
            id INT AUTO_INCREMENT NOT NULL,
            cart_id INT NOT NULL,
            article_id INT NOT NULL,
            variant_id INT DEFAULT NULL,
            quantity INT NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_F0FE25271AD5CDBF (cart_id),
            INDEX IDX_F0FE25277294869C (article_id),
            INDEX IDX_F0FE25273B69A9AF (variant_id),
            CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE,
            CONSTRAINT FK_F0FE25277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_F0FE25273B69A9AF FOREIGN KEY (variant_id) REFERENCES article_variant (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS comment (
            id INT AUTO_INCREMENT NOT NULL,
            author_id INT DEFAULT NULL,
            post_id INT DEFAULT NULL,
            article_id INT DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            content LONGTEXT NOT NULL,
            is_approved TINYINT(1) NOT NULL,
            rating INT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_9474526CF675F31B (author_id),
            INDEX IDX_9474526C4B89032C (post_id),
            INDEX IDX_9474526C7294869C (article_id),
            INDEX IDX_9474526C727ACA70 (parent_id),
            CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL,
            CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE,
            CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_9474526C727ACA70 FOREIGN KEY (parent_id) REFERENCES comment (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS contact_form (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            submit_button_text VARCHAR(255) DEFAULT NULL,
            success_message LONGTEXT DEFAULT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_5284714F989D9B62 (slug)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS contact_form_field (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            label VARCHAR(255) NOT NULL,
            field_type VARCHAR(50) NOT NULL,
            field_name VARCHAR(100) NOT NULL,
            placeholder VARCHAR(255) DEFAULT NULL,
            default_value VARCHAR(255) DEFAULT NULL,
            is_required TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            validation_rules LONGTEXT DEFAULT NULL,
            options JSON DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_15E64FF5FF69B7D (form_id),
            CONSTRAINT FK_15E64FF5FF69B7D FOREIGN KEY (form_id) REFERENCES contact_form (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS contact_form_submission (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            data JSON NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_6BA3ED925FF69B7D (form_id),
            CONSTRAINT FK_6BA3ED925FF69B7D FOREIGN KEY (form_id) REFERENCES contact_form (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS cookie_banner (
            id INT AUTO_INCREMENT NOT NULL,
            language_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            accept_button_text VARCHAR(100) NOT NULL,
            decline_button_text VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            position VARCHAR(50) NOT NULL,
            style JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_D8C86A5582F1BAF4 (language_id),
            CONSTRAINT FK_D8C86A5582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS email_template (
            id INT AUTO_INCREMENT NOT NULL,
            language_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            variables JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_9C0600CA989D9B62 (slug),
            INDEX IDX_9C0600CA82F1BAF4 (language_id),
            CONSTRAINT FK_9C0600CA82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS email_log (
            id INT AUTO_INCREMENT NOT NULL,
            template_id INT DEFAULT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255) DEFAULT NULL,
            subject VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            error_message LONGTEXT DEFAULT NULL,
            sent_at DATETIME NOT NULL,
            delivered_at DATETIME DEFAULT NULL,
            opened_at DATETIME DEFAULT NULL,
            clicked_at DATETIME DEFAULT NULL,
            bounced_at DATETIME DEFAULT NULL,
            retry_count INT DEFAULT NULL,
            related_entity VARCHAR(100) DEFAULT NULL,
            related_entity_id INT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_6FB48835DA0FB8 (template_id),
            INDEX idx_email_status (status),
            INDEX idx_email_recipient (recipient_email),
            INDEX idx_email_sent (sent_at),
            CONSTRAINT FK_6FB48835DA0FB8 FOREIGN KEY (template_id) REFERENCES email_template (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS friend (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            status VARCHAR(50) NOT NULL,
            requested_at DATETIME NOT NULL,
            accepted_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_55EEAC61A76ED395 (user_id),
            INDEX IDX_55EEAC616A5458E8 (friend_id),
            UNIQUE INDEX friend_unique (user_id, friend_id),
            CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
            CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS frontend_template (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            content LONGTEXT NOT NULL,
            template_type VARCHAR(50) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            variables JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_47A1B862989D9B62 (slug)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS intelligent_agent (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            system_prompt LONGTEXT DEFAULT NULL,
            configuration JSON DEFAULT NULL,
            tools JSON DEFAULT NULL,
            workflow JSON DEFAULT NULL,
            model VARCHAR(100) DEFAULT NULL,
            temperature DOUBLE PRECISION DEFAULT NULL,
            max_tokens INT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            priority INT NOT NULL,
            trigger_event VARCHAR(100) DEFAULT NULL,
            trigger_conditions JSON DEFAULT NULL,
            execution_count INT NOT NULL,
            success_count INT NOT NULL,
            failure_count INT NOT NULL,
            last_executed_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_3151A1A9989D9B62 (slug)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS menu (
            id INT AUTO_INCREMENT NOT NULL,
            language_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            location VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_7D053A93989D9B62 (slug),
            INDEX IDX_7D053A9382F1BAF4 (language_id),
            CONSTRAINT FK_7D053A9382F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS menu_item (
            id INT AUTO_INCREMENT NOT NULL,
            menu_id INT NOT NULL,
            parent_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            url VARCHAR(255) DEFAULT NULL,
            target VARCHAR(20) DEFAULT NULL,
            icon VARCHAR(100) DEFAULT NULL,
            css_class VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_D754D550CCD7E912 (menu_id),
            INDEX IDX_D754D550727ACA70 (parent_id),
            CONSTRAINT FK_D754D550CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE,
            CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS message (
            id INT AUTO_INCREMENT NOT NULL,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            content LONGTEXT NOT NULL,
            is_read TINYINT(1) NOT NULL,
            read_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_B6BD307FF624B39D (sender_id),
            INDEX IDX_B6BD307FCD53EDB6 (receiver_id),
            CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE,
            CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (
            id BIGINT AUTO_INCREMENT NOT NULL,
            body LONGTEXT NOT NULL,
            headers LONGTEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL,
            available_at DATETIME NOT NULL,
            delivered_at DATETIME DEFAULT NULL,
            INDEX IDX_75EA56E0FB7336F0 (queue_name),
            INDEX IDX_75EA56E0E3BD61CE (available_at),
            INDEX IDX_75EA56E016BA31DB (delivered_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS newsletter (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL,
            first_name VARCHAR(255) DEFAULT NULL,
            last_name VARCHAR(255) DEFAULT NULL,
            language VARCHAR(10) DEFAULT NULL,
            subscribed_at DATETIME NOT NULL,
            unsubscribed_at DATETIME DEFAULT NULL,
            verification_token VARCHAR(255) DEFAULT NULL,
            is_verified TINYINT(1) NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_7E8585C8E7927C74 (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS newsletter_campaign (
            id INT AUTO_INCREMENT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            plain_text_content LONGTEXT DEFAULT NULL,
            from_name VARCHAR(255) DEFAULT NULL,
            from_email VARCHAR(255) DEFAULT NULL,
            total_recipients INT NOT NULL,
            sent_count INT NOT NULL,
            failed_count INT NOT NULL,
            status VARCHAR(50) NOT NULL,
            scheduled_at DATETIME DEFAULT NULL,
            sent_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS order_item (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            article_id INT DEFAULT NULL,
            variant_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            sku VARCHAR(50) DEFAULT NULL,
            quantity INT NOT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            tax_rate NUMERIC(5, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) NOT NULL,
            total NUMERIC(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_52EA1F098D9F6D38 (order_id),
            INDEX IDX_52EA1F097294869C (article_id),
            INDEX IDX_52EA1F093B69A9AF (variant_id),
            CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE,
            CONSTRAINT FK_52EA1F097294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE SET NULL,
            CONSTRAINT FK_52EA1F093B69A9AF FOREIGN KEY (variant_id) REFERENCES article_variant (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS page_visit (
            id INT AUTO_INCREMENT NOT NULL,
            page_id INT DEFAULT NULL,
            user_id INT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            referer VARCHAR(255) DEFAULT NULL,
            visited_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_B0FE0D73C4663E4 (page_id),
            INDEX IDX_B0FE0D73A76ED395 (user_id),
            CONSTRAINT FK_B0FE0D73C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE,
            CONSTRAINT FK_B0FE0D73A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS payment_method (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            sort_order INT NOT NULL,
            icon VARCHAR(255) DEFAULT NULL,
            configuration JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS product_recommendation (
            id INT AUTO_INCREMENT NOT NULL,
            source_article_id INT NOT NULL,
            recommended_article_id INT NOT NULL,
            recommendation_type VARCHAR(50) NOT NULL,
            priority INT NOT NULL,
            is_active TINYINT(1) NOT NULL,
            click_count INT NOT NULL,
            purchase_count INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX recommendation_unique (source_article_id, recommended_article_id, recommendation_type),
            INDEX IDX_105B5AFE3930177E (source_article_id),
            INDEX IDX_105B5AFE52343A6A (recommended_article_id),
            CONSTRAINT FK_105B5AFE3930177E FOREIGN KEY (source_article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_105B5AFE52343A6A FOREIGN KEY (recommended_article_id) REFERENCES article (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS product_stocks (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            store_id INT NOT NULL,
            stock INT NOT NULL,
            reserved_stock INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX stock_unique (article_id, store_id),
            INDEX IDX_A6B7ADE57294869C (article_id),
            INDEX IDX_A6B7ADE5B092A811 (store_id),
            CONSTRAINT FK_A6B7ADE57294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE,
            CONSTRAINT FK_A6B7ADE5B092A811 FOREIGN KEY (store_id) REFERENCES store (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS seo_url (
            id INT AUTO_INCREMENT NOT NULL,
            entity_type VARCHAR(100) NOT NULL,
            entity_id INT NOT NULL,
            language_id INT DEFAULT NULL,
            url VARCHAR(255) NOT NULL,
            canonical_url VARCHAR(255) DEFAULT NULL,
            redirect_type INT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_80FAD95EF47645AE (url),
            INDEX IDX_80FAD95E82F1BAF4 (language_id),
            INDEX idx_entity (entity_type, entity_id),
            CONSTRAINT FK_80FAD95E82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            default_currency VARCHAR(10) NOT NULL,
            user_profiles_enabled TINYINT(1) NOT NULL,
            public_profiles_enabled TINYINT(1) NOT NULL,
            require_profile_approval TINYINT(1) NOT NULL,
            friend_system_enabled TINYINT(1) NOT NULL,
            messaging_enabled TINYINT(1) NOT NULL,
            user_blocking_enabled TINYINT(1) NOT NULL,
            user_media_enabled TINYINT(1) NOT NULL,
            max_media_per_user INT NOT NULL,
            max_media_size_kb INT NOT NULL,
            seller_system_enabled TINYINT(1) NOT NULL,
            two_factor_enabled_for_users TINYINT(1) NOT NULL,
            two_factor_enabled_for_sellers TINYINT(1) NOT NULL,
            two_factor_enabled_for_admins TINYINT(1) NOT NULL,
            two_factor_required TINYINT(1) NOT NULL,
            use_custom_smtp_settings TINYINT(1) NOT NULL,
            admin_notification_email VARCHAR(255) DEFAULT NULL,
            smtp_host VARCHAR(255) DEFAULT NULL,
            smtp_port INT DEFAULT NULL,
            smtp_username VARCHAR(255) DEFAULT NULL,
            smtp_password VARCHAR(255) DEFAULT NULL,
            smtp_encryption VARCHAR(20) DEFAULT NULL,
            notify_admin_on_stock_low TINYINT(1) NOT NULL,
            notify_admin_on_new_user TINYINT(1) NOT NULL,
            notify_admin_on_contact_form TINYINT(1) NOT NULL,
            notify_admin_on_comment_moderation TINYINT(1) NOT NULL,
            notify_admin_on_seller_registration TINYINT(1) NOT NULL,
            ecommerce_enabled TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS social_connection (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            provider VARCHAR(50) NOT NULL,
            provider_id VARCHAR(255) NOT NULL,
            access_token VARCHAR(500) DEFAULT NULL,
            refresh_token VARCHAR(500) DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX social_unique (user_id, provider),
            INDEX IDX_7E9A0EA2A76ED395 (user_id),
            CONSTRAINT FK_7E9A0EA2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS store (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(255) DEFAULT NULL,
            postal_code VARCHAR(100) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS tax_rate (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            rate NUMERIC(5, 2) NOT NULL,
            country VARCHAR(100) DEFAULT NULL,
            state VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            is_default TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS template (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS theme (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            display_name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            author VARCHAR(255) NOT NULL,
            version VARCHAR(20) NOT NULL,
            category VARCHAR(100) NOT NULL,
            thumbnail_path LONGTEXT NOT NULL,
            config JSON NOT NULL,
            files JSON NOT NULL,
            is_active TINYINT(1) NOT NULL,
            is_default TINYINT(1) NOT NULL,
            primary_color VARCHAR(7) DEFAULT NULL,
            secondary_color VARCHAR(7) DEFAULT NULL,
            accent_color VARCHAR(7) DEFAULT NULL,
            background_color VARCHAR(7) DEFAULT NULL,
            text_color VARCHAR(7) DEFAULT NULL,
            heading_font VARCHAR(255) DEFAULT NULL,
            body_font VARCHAR(255) DEFAULT NULL,
            font_size VARCHAR(10) DEFAULT NULL,
            sidebar_position VARCHAR(20) DEFAULT NULL,
            header_style VARCHAR(20) DEFAULT NULL,
            container_width VARCHAR(20) DEFAULT NULL,
            custom_css LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_9775E7085E237E06 (name)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS translation (
            id INT AUTO_INCREMENT NOT NULL,
            language_id INT NOT NULL,
            translation_key VARCHAR(255) NOT NULL,
            translation_value LONGTEXT NOT NULL,
            domain VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX translation_unique (language_id, translation_key, domain),
            INDEX IDX_B469456F82F1BAF4 (language_id),
            CONSTRAINT FK_B469456F82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS user_block (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            blocked_user_id INT NOT NULL,
            reason LONGTEXT DEFAULT NULL,
            blocked_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX block_unique (user_id, blocked_user_id),
            INDEX IDX_69C56E0DA76ED395 (user_id),
            INDEX IDX_69C56E0D908FA005 (blocked_user_id),
            CONSTRAINT FK_69C56E0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
            CONSTRAINT FK_69C56E0D908FA005 FOREIGN KEY (blocked_user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS user_media (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            media_id INT NOT NULL,
            sort_order INT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_6A2E32E8A76ED395 (user_id),
            INDEX IDX_6A2E32E8EA9FDD75 (media_id),
            CONSTRAINT FK_6A2E32E8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
            CONSTRAINT FK_6A2E32E8EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS user_profile (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            bio LONGTEXT DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            location VARCHAR(255) DEFAULT NULL,
            birth_date DATE DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            avatar_path VARCHAR(255) DEFAULT NULL,
            cover_path VARCHAR(255) DEFAULT NULL,
            is_public TINYINT(1) NOT NULL,
            is_approved TINYINT(1) NOT NULL,
            view_count INT NOT NULL,
            social_links JSON DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id),
            CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS voucher_code (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            discount_type VARCHAR(20) NOT NULL,
            discount_amount NUMERIC(10, 2) NOT NULL,
            min_purchase_amount NUMERIC(10, 2) DEFAULT NULL,
            max_uses INT DEFAULT NULL,
            uses_count INT NOT NULL,
            is_active TINYINT(1) NOT NULL,
            valid_from DATETIME DEFAULT NULL,
            valid_until DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_E488A76177153098 (code)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Drop all tables in reverse dependency order
        $this->addSql('DROP TABLE IF EXISTS user_profile');
        $this->addSql('DROP TABLE IF EXISTS user_media');
        $this->addSql('DROP TABLE IF EXISTS user_block');
        $this->addSql('DROP TABLE IF EXISTS translation');
        $this->addSql('DROP TABLE IF EXISTS voucher_code');
        $this->addSql('DROP TABLE IF EXISTS tax_rate');
        $this->addSql('DROP TABLE IF EXISTS template');
        $this->addSql('DROP TABLE IF EXISTS theme');
        $this->addSql('DROP TABLE IF EXISTS store');
        $this->addSql('DROP TABLE IF EXISTS social_connection');
        $this->addSql('DROP TABLE IF EXISTS site_settings');
        $this->addSql('DROP TABLE IF EXISTS seo_url');
        $this->addSql('DROP TABLE IF EXISTS product_stocks');
        $this->addSql('DROP TABLE IF EXISTS product_recommendation');
        $this->addSql('DROP TABLE IF EXISTS payment_method');
        $this->addSql('DROP TABLE IF EXISTS page_visit');
        $this->addSql('DROP TABLE IF EXISTS order_item');
        $this->addSql('DROP TABLE IF EXISTS newsletter_campaign');
        $this->addSql('DROP TABLE IF EXISTS newsletter');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        $this->addSql('DROP TABLE IF EXISTS message');
        $this->addSql('DROP TABLE IF EXISTS menu_item');
        $this->addSql('DROP TABLE IF EXISTS menu');
        $this->addSql('DROP TABLE IF EXISTS intelligent_agent');
        $this->addSql('DROP TABLE IF EXISTS frontend_template');
        $this->addSql('DROP TABLE IF EXISTS friend');
        $this->addSql('DROP TABLE IF EXISTS email_log');
        $this->addSql('DROP TABLE IF EXISTS email_template');
        $this->addSql('DROP TABLE IF EXISTS cookie_banner');
        $this->addSql('DROP TABLE IF EXISTS contact_form_submission');
        $this->addSql('DROP TABLE IF EXISTS contact_form_field');
        $this->addSql('DROP TABLE IF EXISTS contact_form');
        $this->addSql('DROP TABLE IF EXISTS comment');
        $this->addSql('DROP TABLE IF EXISTS cart_item');
        $this->addSql('DROP TABLE IF EXISTS cart');
        $this->addSql('DROP TABLE IF EXISTS bundle_products');
        $this->addSql('DROP TABLE IF EXISTS bundle_product_items');
        $this->addSql('DROP TABLE IF EXISTS booking');
        $this->addSql('DROP TABLE IF EXISTS article_videos');
        $this->addSql('DROP TABLE IF EXISTS article_variant');
        $this->addSql('DROP TABLE IF EXISTS article_translation');
        $this->addSql('DROP TABLE IF EXISTS article_shipping_methods');
        $this->addSql('DROP TABLE IF EXISTS article_images');
        $this->addSql('DROP TABLE IF EXISTS article_bundle_items');
        $this->addSql('DROP TABLE IF EXISTS api_key');
        $this->addSql('DROP TABLE IF EXISTS ai_workflow_step');
        $this->addSql('DROP TABLE IF EXISTS ai_workflow');
        $this->addSql('DROP TABLE IF EXISTS activity_log');
        $this->addSql('DROP TABLE IF EXISTS address');
        $this->addSql('DROP TABLE IF EXISTS `order`');
        $this->addSql('DROP TABLE IF EXISTS article');
        $this->addSql('DROP TABLE IF EXISTS shipping_method');
        $this->addSql('DROP TABLE IF EXISTS seller');
        $this->addSql('DROP TABLE IF EXISTS post');
        $this->addSql('DROP TABLE IF EXISTS page');
        $this->addSql('DROP TABLE IF EXISTS media');
        $this->addSql('DROP TABLE IF EXISTS category');
        $this->addSql('DROP TABLE IF EXISTS user');
        $this->addSql('DROP TABLE IF EXISTS language');
    }
}
