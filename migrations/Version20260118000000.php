<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Complete database structure migration for TabulariumCMS
 * Creates all 54 tables with relationships, indexes, and constraints
 */
final class Version20260118000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the complete database structure for TabulariumCMS';
    }

    public function up(Schema $schema): void
    {
        // ============================================
        // CORE TABLES (No foreign key dependencies)
        // ============================================

        // User table
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            first_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            locale VARCHAR(10) DEFAULT \'en\' NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_email_verified TINYINT(1) DEFAULT 0 NOT NULL,
            two_factor_secret VARCHAR(255) DEFAULT NULL,
            two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL,
            trusted_token VARCHAR(255) DEFAULT NULL,
            trusted_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            last_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User Profile table
        $this->addSql('CREATE TABLE user_profile (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            bio LONGTEXT DEFAULT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            cover_photo VARCHAR(255) DEFAULT NULL,
            tagline VARCHAR(255) DEFAULT NULL,
            location VARCHAR(255) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            interests JSON DEFAULT NULL,
            social_links JSON DEFAULT NULL,
            custom_template LONGTEXT DEFAULT NULL,
            looking_for LONGTEXT DEFAULT NULL,
            offering LONGTEXT DEFAULT NULL,
            profile_slug VARCHAR(255) DEFAULT NULL,
            privacy_level VARCHAR(20) DEFAULT \'public\' NOT NULL,
            allow_messages TINYINT(1) DEFAULT 1 NOT NULL,
            allow_friend_requests TINYINT(1) DEFAULT 1 NOT NULL,
            profile_views INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id),
            UNIQUE INDEX UNIQ_D95AB405F4A9B58E (profile_slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Media table
        $this->addSql('CREATE TABLE media (
            id INT AUTO_INCREMENT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            upload_path VARCHAR(255) NOT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Category table
        $this->addSql('CREATE TABLE category (
            id INT AUTO_INCREMENT NOT NULL,
            parent_id INT DEFAULT NULL,
            image_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            icon VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug),
            INDEX IDX_64C19C1727ACA70 (parent_id),
            INDEX IDX_64C19C13DA5256D (image_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Article table
        $this->addSql('CREATE TABLE article (
            id INT AUTO_INCREMENT NOT NULL,
            featured_image_id INT DEFAULT NULL,
            seller_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            short_description LONGTEXT DEFAULT NULL,
            price NUMERIC(10, 2) NOT NULL,
            compare_at_price NUMERIC(10, 2) DEFAULT NULL,
            cost NUMERIC(10, 2) DEFAULT NULL,
            sku VARCHAR(100) DEFAULT NULL,
            barcode VARCHAR(100) DEFAULT NULL,
            stock INT DEFAULT 0 NOT NULL,
            low_stock_threshold INT DEFAULT 5 NOT NULL,
            weight NUMERIC(10, 3) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_featured TINYINT(1) DEFAULT 0 NOT NULL,
            is_digital TINYINT(1) DEFAULT 0 NOT NULL,
            digital_file VARCHAR(255) DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            tax_class VARCHAR(50) DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            views INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug),
            INDEX IDX_23A0E663569D950 (featured_image_id),
            INDEX IDX_23A0E668DE820D9 (seller_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Article Category junction table
        $this->addSql('CREATE TABLE article_category (
            article_id INT NOT NULL,
            category_id INT NOT NULL,
            INDEX IDX_53A4EDAA7294869C (article_id),
            INDEX IDX_53A4EDAA12469DE2 (category_id),
            PRIMARY KEY(article_id, category_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Article Image junction table
        $this->addSql('CREATE TABLE article_image (
            article_id INT NOT NULL,
            media_id INT NOT NULL,
            INDEX IDX_B28A764E7294869C (article_id),
            INDEX IDX_B28A764EEA9FDD75 (media_id),
            PRIMARY KEY(article_id, media_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Article Translation table
        $this->addSql('CREATE TABLE article_translation (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            locale VARCHAR(10) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            short_description LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_2EEA2F087294869C (article_id),
            UNIQUE INDEX article_locale_unique (article_id, locale),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Article Variant table
        $this->addSql('CREATE TABLE article_variant (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT NOT NULL,
            image_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            sku VARCHAR(100) DEFAULT NULL,
            price NUMERIC(10, 2) NOT NULL,
            compare_at_price NUMERIC(10, 2) DEFAULT NULL,
            cost NUMERIC(10, 2) DEFAULT NULL,
            stock INT DEFAULT 0 NOT NULL,
            weight NUMERIC(10, 3) DEFAULT NULL,
            attributes JSON DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_9A98022F7294869C (article_id),
            INDEX IDX_9A98022F3DA5256D (image_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Post table
        $this->addSql('CREATE TABLE post (
            id INT AUTO_INCREMENT NOT NULL,
            author_id INT DEFAULT NULL,
            featured_image_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            excerpt LONGTEXT DEFAULT NULL,
            is_published TINYINT(1) DEFAULT 0 NOT NULL,
            published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            views INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug),
            INDEX IDX_5A8A6C8DF675F31B (author_id),
            INDEX IDX_5A8A6C8D3569D950 (featured_image_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Page table
        $this->addSql('CREATE TABLE page (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            template VARCHAR(100) DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_140AB620989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Comment table
        $this->addSql('CREATE TABLE comment (
            id INT AUTO_INCREMENT NOT NULL,
            article_id INT DEFAULT NULL,
            post_id INT DEFAULT NULL,
            user_id INT DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            author_name VARCHAR(100) DEFAULT NULL,
            author_email VARCHAR(255) DEFAULT NULL,
            content LONGTEXT NOT NULL,
            is_approved TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_9474526C7294869C (article_id),
            INDEX IDX_9474526C4B89032C (post_id),
            INDEX IDX_9474526CA76ED395 (user_id),
            INDEX IDX_9474526C727ACA70 (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Language table
        $this->addSql('CREATE TABLE language (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(10) NOT NULL,
            name VARCHAR(100) NOT NULL,
            native_name VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_default TINYINT(1) DEFAULT 0 NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_D4DB71B577153098 (code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Translation table
        $this->addSql('CREATE TABLE translation (
            id INT AUTO_INCREMENT NOT NULL,
            locale VARCHAR(10) NOT NULL,
            `key` VARCHAR(255) NOT NULL,
            value LONGTEXT NOT NULL,
            domain VARCHAR(100) DEFAULT \'messages\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX translation_unique (locale, `key`, domain),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Payment Method table
        $this->addSql('CREATE TABLE payment_method (
            id INT AUTO_INCREMENT NOT NULL,
            logo_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            fee NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            allowed_countries JSON DEFAULT NULL,
            min_price NUMERIC(10, 2) DEFAULT NULL,
            max_price NUMERIC(10, 2) DEFAULT NULL,
            allowed_categories JSON DEFAULT NULL,
            config JSON DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_7B61A1F6F98F144A (logo_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Shipping Method table
        $this->addSql('CREATE TABLE shipping_method (
            id INT AUTO_INCREMENT NOT NULL,
            logo_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            price NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            delivery_days INT DEFAULT NULL,
            delivery_time VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            requires_store_selection TINYINT(1) DEFAULT 0 NOT NULL,
            allowed_countries JSON DEFAULT NULL,
            allowed_postcodes JSON DEFAULT NULL,
            min_price NUMERIC(10, 2) DEFAULT NULL,
            max_price NUMERIC(10, 2) DEFAULT NULL,
            allowed_categories JSON DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_25A85E08F98F144A (logo_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Store table
        $this->addSql('CREATE TABLE store (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(20) DEFAULT NULL,
            country VARCHAR(2) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            opening_hours LONGTEXT DEFAULT NULL,
            latitude NUMERIC(10, 8) DEFAULT NULL,
            longitude NUMERIC(11, 8) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            sort_order INT DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            manager_name VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Order table
        $this->addSql('CREATE TABLE `order` (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            payment_method_id INT DEFAULT NULL,
            shipping_method_id INT DEFAULT NULL,
            pickup_store_id INT DEFAULT NULL,
            order_number VARCHAR(100) NOT NULL,
            status VARCHAR(50) DEFAULT \'pending\' NOT NULL,
            title VARCHAR(20) DEFAULT NULL,
            first_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) DEFAULT NULL,
            customer_name VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            billing_address VARCHAR(255) DEFAULT NULL,
            billing_address_line2 VARCHAR(255) DEFAULT NULL,
            billing_city VARCHAR(100) DEFAULT NULL,
            billing_postcode VARCHAR(20) DEFAULT NULL,
            billing_country VARCHAR(100) DEFAULT NULL,
            shipping_address VARCHAR(255) DEFAULT NULL,
            shipping_address_line2 VARCHAR(255) DEFAULT NULL,
            shipping_city VARCHAR(100) DEFAULT NULL,
            shipping_postcode VARCHAR(20) DEFAULT NULL,
            shipping_country VARCHAR(100) DEFAULT NULL,
            subtotal NUMERIC(10, 2) NOT NULL,
            shipping_cost NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            payment_fee NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            tax_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            discount_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            total_amount NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            admin_notes LONGTEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            shipped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_F5299398551F0F81 (order_number),
            INDEX IDX_F5299398A76ED395 (user_id),
            INDEX IDX_F52993985AA1164F (payment_method_id),
            INDEX IDX_F52993985F7D6850 (shipping_method_id),
            INDEX IDX_F5299398E7ADE67F (pickup_store_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Order Item table
        $this->addSql('CREATE TABLE order_item (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            article_id INT DEFAULT NULL,
            variant_id INT DEFAULT NULL,
            product_name VARCHAR(255) NOT NULL,
            variant_name VARCHAR(255) DEFAULT NULL,
            sku VARCHAR(100) DEFAULT NULL,
            quantity INT NOT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            total_price NUMERIC(10, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            discount_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_52EA1F098D9F6D38 (order_id),
            INDEX IDX_52EA1F097294869C (article_id),
            INDEX IDX_52EA1F093B69A9AF (variant_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Cart table
        $this->addSql('CREATE TABLE cart (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            session_id VARCHAR(255) DEFAULT NULL,
            total NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_BA388B7A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Cart Item table
        $this->addSql('CREATE TABLE cart_item (
            id INT AUTO_INCREMENT NOT NULL,
            cart_id INT NOT NULL,
            article_id INT NOT NULL,
            variant_id INT DEFAULT NULL,
            quantity INT NOT NULL,
            added_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_F0FE25271AD5CDBF (cart_id),
            INDEX IDX_F0FE25277294869C (article_id),
            INDEX IDX_F0FE25273B69A9AF (variant_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Voucher Code table
        $this->addSql('CREATE TABLE voucher_code (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(50) NOT NULL,
            discount_type VARCHAR(20) NOT NULL,
            discount_value NUMERIC(10, 2) NOT NULL,
            min_order_value NUMERIC(10, 2) DEFAULT NULL,
            max_discount NUMERIC(10, 2) DEFAULT NULL,
            usage_limit INT DEFAULT NULL,
            usage_count INT DEFAULT 0 NOT NULL,
            usage_per_user INT DEFAULT NULL,
            starts_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            allowed_products JSON DEFAULT NULL,
            allowed_categories JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_B7DDAA0E77153098 (code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Seller table
        $this->addSql('CREATE TABLE seller (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            store_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            logo VARCHAR(255) DEFAULT NULL,
            banner VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            address LONGTEXT DEFAULT NULL,
            status VARCHAR(50) DEFAULT \'pending\' NOT NULL,
            commission_rate NUMERIC(5, 2) DEFAULT \'10.00\' NOT NULL,
            rating NUMERIC(3, 2) DEFAULT NULL,
            total_sales INT DEFAULT 0 NOT NULL,
            total_revenue NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL,
            bank_name VARCHAR(255) DEFAULT NULL,
            bank_account_number VARCHAR(255) DEFAULT NULL,
            bank_routing_number VARCHAR(255) DEFAULT NULL,
            tax_id VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_FB1AD3FC989D9B62 (slug),
            INDEX IDX_FB1AD3FCA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Tax Rate table
        $this->addSql('CREATE TABLE tax_rate (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            rate NUMERIC(5, 2) NOT NULL,
            country VARCHAR(2) DEFAULT NULL,
            region VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_default TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Address table
        $this->addSql('CREATE TABLE address (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            title VARCHAR(20) DEFAULT NULL,
            first_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) DEFAULT NULL,
            company VARCHAR(255) DEFAULT NULL,
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(20) NOT NULL,
            country VARCHAR(2) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            type VARCHAR(50) DEFAULT \'billing\' NOT NULL,
            is_default TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_D4E6F81A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Menu table
        $this->addSql('CREATE TABLE menu (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_7D053A93989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Menu Item table
        $this->addSql('CREATE TABLE menu_item (
            id INT AUTO_INCREMENT NOT NULL,
            menu_id INT NOT NULL,
            parent_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            url VARCHAR(255) DEFAULT NULL,
            route VARCHAR(255) DEFAULT NULL,
            route_params JSON DEFAULT NULL,
            icon VARCHAR(100) DEFAULT NULL,
            target VARCHAR(20) DEFAULT \'_self\' NOT NULL,
            css_class VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_D754D550CCD7E912 (menu_id),
            INDEX IDX_D754D550727ACA70 (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Site Settings table
        $this->addSql('CREATE TABLE site_settings (
            id INT AUTO_INCREMENT NOT NULL,
            site_name VARCHAR(255) NOT NULL,
            site_tagline VARCHAR(255) DEFAULT NULL,
            site_description LONGTEXT DEFAULT NULL,
            contact_email VARCHAR(255) DEFAULT NULL,
            contact_phone VARCHAR(50) DEFAULT NULL,
            contact_address LONGTEXT DEFAULT NULL,
            logo VARCHAR(255) DEFAULT NULL,
            favicon VARCHAR(255) DEFAULT NULL,
            default_locale VARCHAR(10) DEFAULT \'en\' NOT NULL,
            default_currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL,
            timezone VARCHAR(100) DEFAULT \'UTC\' NOT NULL,
            date_format VARCHAR(50) DEFAULT \'Y-m-d\' NOT NULL,
            time_format VARCHAR(50) DEFAULT \'H:i\' NOT NULL,
            footer_text LONGTEXT DEFAULT NULL,
            copyright_text VARCHAR(255) DEFAULT NULL,
            social_links JSON DEFAULT NULL,
            google_analytics_id VARCHAR(100) DEFAULT NULL,
            facebook_pixel_id VARCHAR(100) DEFAULT NULL,
            maintenance_mode TINYINT(1) DEFAULT 0 NOT NULL,
            maintenance_message LONGTEXT DEFAULT NULL,
            max_media_per_user INT DEFAULT 50 NOT NULL,
            max_media_size_kb INT DEFAULT 5120 NOT NULL,
            custom_head_scripts LONGTEXT DEFAULT NULL,
            custom_body_scripts LONGTEXT DEFAULT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            meta_keywords LONGTEXT DEFAULT NULL,
            og_image VARCHAR(255) DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Theme table
        $this->addSql('CREATE TABLE theme (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            display_name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            author VARCHAR(255) DEFAULT NULL,
            version VARCHAR(20) DEFAULT \'1.0.0\' NOT NULL,
            category VARCHAR(100) DEFAULT \'general\' NOT NULL,
            thumbnail_path VARCHAR(500) DEFAULT NULL,
            config JSON DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 0 NOT NULL,
            is_default TINYINT(1) DEFAULT 0 NOT NULL,
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
            custom_js LONGTEXT DEFAULT NULL,
            files JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_9775E7085E237E06 (name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Email Template table
        $this->addSql('CREATE TABLE email_template (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            body_html LONGTEXT NOT NULL,
            body_text LONGTEXT DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            available_variables JSON DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            from_email VARCHAR(255) DEFAULT NULL,
            from_name VARCHAR(255) DEFAULT NULL,
            reply_to VARCHAR(255) DEFAULT NULL,
            bcc_emails VARCHAR(500) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_9C0600CA989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Newsletter table
        $this->addSql('CREATE TABLE newsletter (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) DEFAULT NULL,
            token VARCHAR(64) NOT NULL,
            is_active TINYINT(1) DEFAULT 0 NOT NULL,
            is_confirmed TINYINT(1) DEFAULT 0 NOT NULL,
            locale VARCHAR(10) DEFAULT \'en\' NOT NULL,
            subscribed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            confirmed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            unsubscribed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ip_address VARCHAR(45) DEFAULT NULL,
            UNIQUE INDEX UNIQ_7E8585C8E7927C74 (email),
            UNIQUE INDEX UNIQ_7E8585C85F37A13B (token),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Newsletter Campaign table
        $this->addSql('CREATE TABLE newsletter_campaign (
            id INT AUTO_INCREMENT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            plain_text_content LONGTEXT DEFAULT NULL,
            from_name VARCHAR(255) DEFAULT NULL,
            from_email VARCHAR(255) DEFAULT NULL,
            total_recipients INT DEFAULT 0 NOT NULL,
            sent_count INT DEFAULT 0 NOT NULL,
            failed_count INT DEFAULT 0 NOT NULL,
            opened_count INT DEFAULT 0 NOT NULL,
            clicked_count INT DEFAULT 0 NOT NULL,
            status VARCHAR(20) DEFAULT \'draft\' NOT NULL,
            scheduled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Contact Form table
        $this->addSql('CREATE TABLE contact_form (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            recipient_email VARCHAR(255) DEFAULT NULL,
            submit_button_text VARCHAR(255) DEFAULT \'Submit\' NOT NULL,
            success_message LONGTEXT DEFAULT NULL,
            send_email TINYINT(1) DEFAULT 1 NOT NULL,
            send_auto_reply TINYINT(1) DEFAULT 0 NOT NULL,
            auto_reply_subject VARCHAR(255) DEFAULT NULL,
            auto_reply_message LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_B052A1AE772E836A (identifier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Contact Form Field table
        $this->addSql('CREATE TABLE contact_form_field (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            label VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) DEFAULT \'text\' NOT NULL,
            placeholder VARCHAR(255) DEFAULT NULL,
            options JSON DEFAULT NULL,
            validation_rules JSON DEFAULT NULL,
            is_required TINYINT(1) DEFAULT 0 NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            css_class VARCHAR(255) DEFAULT NULL,
            INDEX IDX_C79FFDC45FF69B7D (form_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Contact Form Submission table
        $this->addSql('CREATE TABLE contact_form_submission (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            data JSON NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent LONGTEXT DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0 NOT NULL,
            is_spam TINYINT(1) DEFAULT 0 NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            submitted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_59E08C505FF69B7D (form_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Activity Log table
        $this->addSql('CREATE TABLE activity_log (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            action_type VARCHAR(50) NOT NULL,
            description VARCHAR(255) NOT NULL,
            entity_type VARCHAR(100) DEFAULT NULL,
            entity_id INT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_FDD4B855A76ED395 (user_id),
            INDEX idx_activity_created (created_at),
            INDEX idx_activity_type (action_type),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Email Log table
        $this->addSql('CREATE TABLE email_log (
            id INT AUTO_INCREMENT NOT NULL,
            template_id INT DEFAULT NULL,
            recipient VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255) DEFAULT NULL,
            subject VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            plain_text_body LONGTEXT DEFAULT NULL,
            status VARCHAR(50) DEFAULT \'pending\' NOT NULL,
            template_code VARCHAR(255) DEFAULT NULL,
            from_email VARCHAR(255) DEFAULT NULL,
            from_name VARCHAR(255) DEFAULT NULL,
            attachments JSON DEFAULT NULL,
            headers JSON DEFAULT NULL,
            error_message LONGTEXT DEFAULT NULL,
            retry_count INT DEFAULT 0 NOT NULL,
            sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            opened_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            clicked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            related_entity VARCHAR(100) DEFAULT NULL,
            related_entity_id INT DEFAULT NULL,
            INDEX IDX_6FB48835DA0FB8 (template_id),
            INDEX idx_email_log_recipient (recipient),
            INDEX idx_email_log_status (status),
            INDEX idx_email_log_sent_at (sent_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Login Attempt table
        $this->addSql('CREATE TABLE login_attempt (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL,
            was_successful TINYINT(1) DEFAULT 0 NOT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            failure_reason VARCHAR(100) DEFAULT NULL,
            INDEX idx_username_attempted (username, attempted_at),
            INDEX idx_ip_attempted (ip_address, attempted_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Friend table
        $this->addSql('CREATE TABLE friend (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            status VARCHAR(20) DEFAULT \'pending\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_55EEAC61A76ED395 (user_id),
            INDEX IDX_55EEAC616A5458E8 (friend_id),
            UNIQUE INDEX unique_friendship (user_id, friend_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Message table
        $this->addSql('CREATE TABLE message (
            id INT AUTO_INCREMENT NOT NULL,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            content LONGTEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0 NOT NULL,
            sender_deleted TINYINT(1) DEFAULT 0 NOT NULL,
            receiver_deleted TINYINT(1) DEFAULT 0 NOT NULL,
            sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_B6BD307FF624B39D (sender_id),
            INDEX IDX_B6BD307FCD53EDB6 (receiver_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Customer Review table
        $this->addSql('CREATE TABLE customer_review (
            id INT AUTO_INCREMENT NOT NULL,
            product_id INT DEFAULT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_title VARCHAR(255) DEFAULT NULL,
            review_text LONGTEXT NOT NULL,
            rating INT NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_featured TINYINT(1) DEFAULT 0 NOT NULL,
            customer_image VARCHAR(255) DEFAULT NULL,
            customer_location VARCHAR(100) DEFAULT NULL,
            is_verified TINYINT(1) DEFAULT 0 NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_E9A6CCAB4584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Booking table
        $this->addSql('CREATE TABLE booking (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            booking_type VARCHAR(50) NOT NULL,
            status VARCHAR(50) DEFAULT \'pending\' NOT NULL,
            start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            total_price NUMERIC(10, 2) NOT NULL,
            deposit NUMERIC(10, 2) DEFAULT NULL,
            currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL,
            quantity INT DEFAULT 1 NOT NULL,
            details JSON DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            customer_name VARCHAR(255) DEFAULT NULL,
            customer_email VARCHAR(255) DEFAULT NULL,
            customer_phone VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_E00CEDDEA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Page Visit table
        $this->addSql('CREATE TABLE page_visit (
            id INT AUTO_INCREMENT NOT NULL,
            url VARCHAR(500) NOT NULL,
            path VARCHAR(255) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent LONGTEXT DEFAULT NULL,
            referer VARCHAR(500) DEFAULT NULL,
            country VARCHAR(2) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            device_type VARCHAR(50) DEFAULT NULL,
            browser VARCHAR(100) DEFAULT NULL,
            os VARCHAR(100) DEFAULT NULL,
            visited_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_visited_at (visited_at),
            INDEX idx_page_visit_path (path),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User Media table
        $this->addSql('CREATE TABLE user_media (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            type VARCHAR(50) DEFAULT \'image\' NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_public TINYINT(1) DEFAULT 1 NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            views INT DEFAULT 0 NOT NULL,
            uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_E9AA9DDCA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User Block table
        $this->addSql('CREATE TABLE user_block (
            id INT AUTO_INCREMENT NOT NULL,
            blocker_id INT NOT NULL,
            blocked_id INT NOT NULL,
            reason LONGTEXT DEFAULT NULL,
            blocked_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_61D96C7A548D5975 (blocker_id),
            INDEX IDX_61D96C7A1EA50573 (blocked_id),
            UNIQUE INDEX unique_block (blocker_id, blocked_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // SEO URL table
        $this->addSql('CREATE TABLE seo_url (
            id INT AUTO_INCREMENT NOT NULL,
            url VARCHAR(255) NOT NULL,
            route VARCHAR(255) NOT NULL,
            parameters JSON DEFAULT NULL,
            locale VARCHAR(10) DEFAULT \'en\' NOT NULL,
            priority INT DEFAULT 0 NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            canonical_url VARCHAR(255) DEFAULT NULL,
            meta_tags JSON DEFAULT NULL,
            status_code VARCHAR(10) DEFAULT \'200\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX seo_url_unique (url, locale),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // API Key table
        $this->addSql('CREATE TABLE api_key (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            api_key VARCHAR(64) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            rate_limit INT DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            request_count INT DEFAULT 0 NOT NULL,
            ip_whitelist JSON DEFAULT NULL,
            permissions JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_C912ED9DC912ED9D (api_key),
            INDEX IDX_C912ED9DA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Notification table
        $this->addSql('CREATE TABLE notification (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            related_user_id INT DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            message LONGTEXT NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            related_entity_type VARCHAR(100) DEFAULT NULL,
            related_entity_id INT DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_BF5476CAA76ED395 (user_id),
            INDEX IDX_BF5476CA98771930 (related_user_id),
            INDEX idx_notification_user_read (user_id, is_read),
            INDEX idx_notification_created (created_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // AI Workflow table
        $this->addSql('CREATE TABLE ai_workflow (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            ai_provider VARCHAR(50) DEFAULT \'openai\' NOT NULL,
            model VARCHAR(100) DEFAULT \'gpt-4\' NOT NULL,
            trigger_event VARCHAR(255) DEFAULT NULL,
            trigger_conditions JSON DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            execution_count INT DEFAULT 0 NOT NULL,
            last_executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // AI Workflow Step table
        $this->addSql('CREATE TABLE ai_workflow_step (
            id INT AUTO_INCREMENT NOT NULL,
            workflow_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            prompt LONGTEXT NOT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            action VARCHAR(50) DEFAULT \'generate_text\' NOT NULL,
            parameters JSON DEFAULT NULL,
            output_variable VARCHAR(100) DEFAULT NULL,
            condition_expression LONGTEXT DEFAULT NULL,
            INDEX IDX_E8F42B62C33923F1 (workflow_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Cookie Banner table
        $this->addSql('CREATE TABLE cookie_banner (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) DEFAULT \'We use cookies\' NOT NULL,
            message LONGTEXT NOT NULL,
            accept_button_text VARCHAR(255) DEFAULT \'Accept All\' NOT NULL,
            decline_button_text VARCHAR(255) DEFAULT \'Decline\' NOT NULL,
            settings_button_text VARCHAR(255) DEFAULT \'Settings\' NOT NULL,
            save_preferences_text VARCHAR(255) DEFAULT \'Save Preferences\' NOT NULL,
            privacy_policy_url VARCHAR(255) DEFAULT NULL,
            cookie_policy_url VARCHAR(255) DEFAULT NULL,
            imprint_url VARCHAR(255) DEFAULT NULL,
            position VARCHAR(20) DEFAULT \'bottom\' NOT NULL,
            theme VARCHAR(20) DEFAULT \'light\' NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            cookie_categories JSON DEFAULT NULL,
            cookie_lifetime INT DEFAULT 365 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Frontend Template table
        $this->addSql('CREATE TABLE frontend_template (
            id INT AUTO_INCREMENT NOT NULL,
            template_key VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            content LONGTEXT NOT NULL,
            available_variables JSON DEFAULT NULL,
            category VARCHAR(50) DEFAULT \'general\' NOT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            is_editable TINYINT(1) DEFAULT 1 NOT NULL,
            meta_title VARCHAR(255) DEFAULT NULL,
            meta_description LONGTEXT DEFAULT NULL,
            meta_keywords JSON DEFAULT NULL,
            og_title VARCHAR(255) DEFAULT NULL,
            og_description LONGTEXT DEFAULT NULL,
            og_image VARCHAR(500) DEFAULT NULL,
            og_type VARCHAR(50) DEFAULT \'website\' NOT NULL,
            twitter_card VARCHAR(50) DEFAULT \'summary\' NOT NULL,
            twitter_title VARCHAR(255) DEFAULT NULL,
            twitter_description LONGTEXT DEFAULT NULL,
            twitter_image VARCHAR(500) DEFAULT NULL,
            structured_data JSON DEFAULT NULL,
            schema_type VARCHAR(100) DEFAULT \'WebPage\' NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_B17F0F1F2FDCA5B2 (template_key),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Template table
        $this->addSql('CREATE TABLE template (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            position VARCHAR(50) DEFAULT \'content\' NOT NULL,
            content LONGTEXT NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_97601F83772E836A (identifier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Intelligent Agent table
        $this->addSql('CREATE TABLE intelligent_agent (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            type VARCHAR(50) DEFAULT \'custom\' NOT NULL,
            system_prompt LONGTEXT NOT NULL,
            configuration JSON DEFAULT NULL,
            tools JSON DEFAULT NULL,
            workflow JSON DEFAULT NULL,
            model VARCHAR(100) DEFAULT \'gpt-4\' NOT NULL,
            temperature DOUBLE PRECISION DEFAULT \'0.7\',
            max_tokens INT DEFAULT 2000,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            priority INT DEFAULT 0 NOT NULL,
            trigger_event VARCHAR(255) DEFAULT NULL,
            trigger_conditions JSON DEFAULT NULL,
            api_endpoint VARCHAR(255) DEFAULT NULL,
            execution_count INT DEFAULT 0 NOT NULL,
            success_count INT DEFAULT 0 NOT NULL,
            failure_count INT DEFAULT 0 NOT NULL,
            last_executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_B8B85D83989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Social Connection table
        $this->addSql('CREATE TABLE social_connection (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            provider VARCHAR(50) NOT NULL,
            provider_id VARCHAR(255) NOT NULL,
            access_token LONGTEXT DEFAULT NULL,
            refresh_token LONGTEXT DEFAULT NULL,
            token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            profile_data JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_49BBDC5AA76ED395 (user_id),
            UNIQUE INDEX social_provider_unique (provider, provider_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Profile Report table
        $this->addSql('CREATE TABLE profile_report (
            id INT AUTO_INCREMENT NOT NULL,
            reporter_id INT NOT NULL,
            reported_user_id INT NOT NULL,
            reason VARCHAR(50) NOT NULL,
            details LONGTEXT DEFAULT NULL,
            status VARCHAR(20) DEFAULT \'pending\' NOT NULL,
            admin_notes LONGTEXT DEFAULT NULL,
            resolved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_D6B2CF9BE1CFE6F5 (reporter_id),
            INDEX IDX_D6B2CF9BE7566E49 (reported_user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Messenger Messages table (for Symfony Messenger)
        $this->addSql('CREATE TABLE messenger_messages (
            id BIGINT AUTO_INCREMENT NOT NULL,
            body LONGTEXT NOT NULL,
            headers LONGTEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_75EA56E0FB7336F0 (queue_name),
            INDEX IDX_75EA56E0E3BD61CE (available_at),
            INDEX IDX_75EA56E016BA31DB (delivered_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // ============================================
        // FOREIGN KEY CONSTRAINTS
        // ============================================

        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C13DA5256D FOREIGN KEY (image_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E663569D950 FOREIGN KEY (featured_image_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E668DE820D9 FOREIGN KEY (seller_id) REFERENCES seller (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764EEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F087294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_variant ADD CONSTRAINT FK_9A98022F7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_variant ADD CONSTRAINT FK_9A98022F3DA5256D FOREIGN KEY (image_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D3569D950 FOREIGN KEY (featured_image_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C727ACA70 FOREIGN KEY (parent_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment_method ADD CONSTRAINT FK_7B61A1F6F98F144A FOREIGN KEY (logo_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE shipping_method ADD CONSTRAINT FK_25A85E08F98F144A FOREIGN KEY (logo_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES shipping_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E7ADE67F FOREIGN KEY (pickup_store_id) REFERENCES store (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F097294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F093B69A9AF FOREIGN KEY (variant_id) REFERENCES article_variant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25273B69A9AF FOREIGN KEY (variant_id) REFERENCES article_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT FK_FB1AD3FCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_form_field ADD CONSTRAINT FK_C79FFDC45FF69B7D FOREIGN KEY (form_id) REFERENCES contact_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_form_submission ADD CONSTRAINT FK_59E08C505FF69B7D FOREIGN KEY (form_id) REFERENCES contact_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FDD4B855A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE email_log ADD CONSTRAINT FK_6FB48835DA0FB8 FOREIGN KEY (template_id) REFERENCES email_template (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_review ADD CONSTRAINT FK_E9A6CCAB4584665A FOREIGN KEY (product_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_media ADD CONSTRAINT FK_E9AA9DDCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_block ADD CONSTRAINT FK_61D96C7A548D5975 FOREIGN KEY (blocker_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_block ADD CONSTRAINT FK_61D96C7A1EA50573 FOREIGN KEY (blocked_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE api_key ADD CONSTRAINT FK_C912ED9DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA98771930 FOREIGN KEY (related_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_workflow_step ADD CONSTRAINT FK_E8F42B62C33923F1 FOREIGN KEY (workflow_id) REFERENCES ai_workflow (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE social_connection ADD CONSTRAINT FK_49BBDC5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_report ADD CONSTRAINT FK_D6B2CF9BE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_report ADD CONSTRAINT FK_D6B2CF9BE7566E49 FOREIGN KEY (reported_user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C13DA5256D');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E663569D950');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E668DE820D9');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA7294869C');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA12469DE2');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764E7294869C');
        $this->addSql('ALTER TABLE article_image DROP FOREIGN KEY FK_B28A764EEA9FDD75');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F087294869C');
        $this->addSql('ALTER TABLE article_variant DROP FOREIGN KEY FK_9A98022F7294869C');
        $this->addSql('ALTER TABLE article_variant DROP FOREIGN KEY FK_9A98022F3DA5256D');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D3569D950');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C727ACA70');
        $this->addSql('ALTER TABLE payment_method DROP FOREIGN KEY FK_7B61A1F6F98F144A');
        $this->addSql('ALTER TABLE shipping_method DROP FOREIGN KEY FK_25A85E08F98F144A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993985AA1164F');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993985F7D6850');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398E7ADE67F');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F097294869C');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F093B69A9AF');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25277294869C');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25273B69A9AF');
        $this->addSql('ALTER TABLE seller DROP FOREIGN KEY FK_FB1AD3FCA76ED395');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395');
        $this->addSql('ALTER TABLE menu_item DROP FOREIGN KEY FK_D754D550CCD7E912');
        $this->addSql('ALTER TABLE menu_item DROP FOREIGN KEY FK_D754D550727ACA70');
        $this->addSql('ALTER TABLE contact_form_field DROP FOREIGN KEY FK_C79FFDC45FF69B7D');
        $this->addSql('ALTER TABLE contact_form_submission DROP FOREIGN KEY FK_59E08C505FF69B7D');
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FDD4B855A76ED395');
        $this->addSql('ALTER TABLE email_log DROP FOREIGN KEY FK_6FB48835DA0FB8');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC61A76ED395');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC616A5458E8');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE customer_review DROP FOREIGN KEY FK_E9A6CCAB4584665A');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE user_media DROP FOREIGN KEY FK_E9AA9DDCA76ED395');
        $this->addSql('ALTER TABLE user_block DROP FOREIGN KEY FK_61D96C7A548D5975');
        $this->addSql('ALTER TABLE user_block DROP FOREIGN KEY FK_61D96C7A1EA50573');
        $this->addSql('ALTER TABLE api_key DROP FOREIGN KEY FK_C912ED9DA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA98771930');
        $this->addSql('ALTER TABLE ai_workflow_step DROP FOREIGN KEY FK_E8F42B62C33923F1');
        $this->addSql('ALTER TABLE social_connection DROP FOREIGN KEY FK_49BBDC5AA76ED395');
        $this->addSql('ALTER TABLE profile_report DROP FOREIGN KEY FK_D6B2CF9BE1CFE6F5');
        $this->addSql('ALTER TABLE profile_report DROP FOREIGN KEY FK_D6B2CF9BE7566E49');

        // Drop tables in reverse order of creation
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE profile_report');
        $this->addSql('DROP TABLE social_connection');
        $this->addSql('DROP TABLE intelligent_agent');
        $this->addSql('DROP TABLE template');
        $this->addSql('DROP TABLE frontend_template');
        $this->addSql('DROP TABLE cookie_banner');
        $this->addSql('DROP TABLE ai_workflow_step');
        $this->addSql('DROP TABLE ai_workflow');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE api_key');
        $this->addSql('DROP TABLE seo_url');
        $this->addSql('DROP TABLE user_block');
        $this->addSql('DROP TABLE user_media');
        $this->addSql('DROP TABLE page_visit');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE customer_review');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE friend');
        $this->addSql('DROP TABLE login_attempt');
        $this->addSql('DROP TABLE email_log');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('DROP TABLE contact_form_submission');
        $this->addSql('DROP TABLE contact_form_field');
        $this->addSql('DROP TABLE contact_form');
        $this->addSql('DROP TABLE newsletter_campaign');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE email_template');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE site_settings');
        $this->addSql('DROP TABLE menu_item');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE tax_rate');
        $this->addSql('DROP TABLE seller');
        $this->addSql('DROP TABLE voucher_code');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE store');
        $this->addSql('DROP TABLE shipping_method');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE article_variant');
        $this->addSql('DROP TABLE article_translation');
        $this->addSql('DROP TABLE article_image');
        $this->addSql('DROP TABLE article_category');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE user');
    }
}
