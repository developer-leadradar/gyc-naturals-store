<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET NAMES utf8mb4");
    echo "✓ Database ready.\n";

    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS `users` (
          `id`             INT AUTO_INCREMENT PRIMARY KEY,
          `first_name`     VARCHAR(80) NOT NULL,
          `last_name`      VARCHAR(80) NOT NULL,
          `email`          VARCHAR(150) UNIQUE NOT NULL,
          `password`       VARCHAR(255) NOT NULL,
          `phone`          VARCHAR(30),
          `role`           ENUM('customer','admin') DEFAULT 'customer',
          `is_active`      TINYINT(1) DEFAULT 1,
          `email_verified` TINYINT(1) DEFAULT 0,
          `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX idx_email (`email`),
          INDEX idx_role  (`role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'categories' => "CREATE TABLE IF NOT EXISTS `categories` (
          `id`            INT AUTO_INCREMENT PRIMARY KEY,
          `name`          VARCHAR(100) NOT NULL,
          `slug`          VARCHAR(120) UNIQUE NOT NULL,
          `description`   TEXT,
          `image`         VARCHAR(500),
          `parent_id`     INT NULL,
          `display_order` INT DEFAULT 0,
          `is_active`     TINYINT(1) DEFAULT 1,
          `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'products' => "CREATE TABLE IF NOT EXISTS `products` (
          `id`             INT AUTO_INCREMENT PRIMARY KEY,
          `name`           VARCHAR(200) NOT NULL,
          `slug`           VARCHAR(220) UNIQUE NOT NULL,
          `description`    TEXT,
          `short_desc`     VARCHAR(500),
          `sku`            VARCHAR(100),
          `price`          DECIMAL(10,2) NOT NULL DEFAULT 0,
          `compare_price`  DECIMAL(10,2) NULL,
          `cost_price`     DECIMAL(10,2) NULL,
          `stock_quantity` INT DEFAULT 0,
          `category_id`    INT NULL,
          `brand`          VARCHAR(100),
          `image`          VARCHAR(500),
          `images`         JSON,
          `is_active`      TINYINT(1) DEFAULT 1,
          `is_featured`    TINYINT(1) DEFAULT 0,
          `rating`         DECIMAL(3,2) DEFAULT 0,
          `review_count`   INT DEFAULT 0,
          `display_order`  INT DEFAULT 0,
          `hair_type`      VARCHAR(100),
          `product_type`   VARCHAR(100),
          `key_ingredient` VARCHAR(150),
          `volume_ml`      DECIMAL(8,2),
          `suitable_for`   VARCHAR(100),
          `scent`          VARCHAR(80),
          `concern`        VARCHAR(150),
          `clothing_size`  VARCHAR(50) NULL,
          `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
          INDEX idx_category (`category_id`),
          INDEX idx_featured (`is_featured`),
          FULLTEXT idx_search (`name`, `description`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'orders' => "CREATE TABLE IF NOT EXISTS `orders` (
          `id`                   INT AUTO_INCREMENT PRIMARY KEY,
          `order_number`         VARCHAR(30) UNIQUE NOT NULL,
          `user_id`              INT NULL,
          `status`               ENUM('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
          `payment_status`       ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
          `payment_method`       VARCHAR(50) DEFAULT 'paystack',
          `paystack_ref`         VARCHAR(100) NULL,
          `subtotal`             DECIMAL(10,2) DEFAULT 0,
          `tax`                  DECIMAL(10,2) DEFAULT 0,
          `shipping`             DECIMAL(10,2) DEFAULT 0,
          `discount`             DECIMAL(10,2) DEFAULT 0,
          `total`                DECIMAL(10,2) DEFAULT 0,
          `customer_email`        VARCHAR(180) DEFAULT NULL COMMENT 'Captured at checkout (guest or logged-in)',
          `shipping_first_name`  VARCHAR(80),
          `shipping_last_name`   VARCHAR(80),
          `shipping_address`     VARCHAR(300),
          `shipping_city`        VARCHAR(100),
          `shipping_state`       VARCHAR(100),
          `shipping_zip`         VARCHAR(20),
          `shipping_country`     VARCHAR(100) DEFAULT 'Nigeria',
          `shipping_phone`       VARCHAR(30),
          `billing_first_name`   VARCHAR(80),
          `billing_last_name`    VARCHAR(80),
          `billing_address`      VARCHAR(300),
          `billing_city`         VARCHAR(100),
          `billing_state`        VARCHAR(100),
          `billing_zip`          VARCHAR(20),
          `billing_country`      VARCHAR(100) DEFAULT 'Nigeria',
          `billing_phone`        VARCHAR(30),
          `notes`                TEXT,
          `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
          INDEX idx_status (`status`),
          INDEX idx_payment (`payment_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'order_items' => "CREATE TABLE IF NOT EXISTS `order_items` (
          `id`                INT AUTO_INCREMENT PRIMARY KEY,
          `order_id`          INT NOT NULL,
          `product_id`        INT NULL,
          `product_name`      VARCHAR(200) NOT NULL,
          `quantity`          INT DEFAULT 1,
          `price_at_purchase` DECIMAL(10,2) NOT NULL,
          `subtotal`          DECIMAL(10,2) NOT NULL,
          `bundle_id`         INT NULL,
          FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cart' => "CREATE TABLE IF NOT EXISTS `cart` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `user_id`    INT NULL,
          `session_id` VARCHAR(100) NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'cart_items' => "CREATE TABLE IF NOT EXISTS `cart_items` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `cart_id`    INT NOT NULL,
          `product_id` INT NOT NULL,
          `quantity`   INT DEFAULT 1,
          `bundle_id`  INT NULL,
          FOREIGN KEY (`cart_id`) REFERENCES `cart`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'wishlist' => "CREATE TABLE IF NOT EXISTS `wishlist` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `user_id`    INT NOT NULL,
          `product_id` INT NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY unique_wish (`user_id`, `product_id`),
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'addresses' => "CREATE TABLE IF NOT EXISTS `addresses` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `user_id`    INT NOT NULL,
          `label`      VARCHAR(50) DEFAULT 'Home',
          `first_name` VARCHAR(80),
          `last_name`  VARCHAR(80),
          `address`    VARCHAR(300),
          `city`       VARCHAR(100),
          `state`      VARCHAR(100),
          `zip`        VARCHAR(20),
          `country`    VARCHAR(100) DEFAULT 'Nigeria',
          `phone`      VARCHAR(30),
          `is_default` TINYINT(1) DEFAULT 0,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'reviews' => "CREATE TABLE IF NOT EXISTS `reviews` (
          `id`          INT AUTO_INCREMENT PRIMARY KEY,
          `product_id`  INT NOT NULL,
          `user_id`     INT NOT NULL,
          `rating`      TINYINT(1) DEFAULT 5,
          `title`       VARCHAR(200),
          `body`        TEXT,
          `is_approved` TINYINT(1) DEFAULT 1,
          `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY unique_review (`product_id`, `user_id`),
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'email_verifications' => "CREATE TABLE IF NOT EXISTS `email_verifications` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `user_id`    INT NOT NULL,
          `token`      VARCHAR(100) NOT NULL,
          `expires_at` DATETIME NOT NULL,
          `used`       TINYINT(1) DEFAULT 0,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'password_resets' => "CREATE TABLE IF NOT EXISTS `password_resets` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `email`      VARCHAR(150) NOT NULL,
          `token`      VARCHAR(100) NOT NULL,
          `expires_at` DATETIME NOT NULL,
          `used`       TINYINT(1) DEFAULT 0,
          INDEX idx_email (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'site_settings' => "CREATE TABLE IF NOT EXISTS `site_settings` (
          `id`          INT AUTO_INCREMENT PRIMARY KEY,
          `setting_key` VARCHAR(100) UNIQUE NOT NULL,
          `setting_val` TEXT,
          `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'gallery_categories' => "CREATE TABLE IF NOT EXISTS `gallery_categories` (
          `id`            INT AUTO_INCREMENT PRIMARY KEY,
          `name`          VARCHAR(100) NOT NULL,
          `slug`          VARCHAR(120) UNIQUE NOT NULL,
          `description`   TEXT,
          `image`         VARCHAR(500),
          `display_order` INT DEFAULT 0,
          `is_active`     TINYINT(1) DEFAULT 1,
          `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'gallery_images' => "CREATE TABLE IF NOT EXISTS `gallery_images` (
          `id`               INT AUTO_INCREMENT PRIMARY KEY,
          `title`            VARCHAR(200) NOT NULL,
          `slug`             VARCHAR(220) UNIQUE NOT NULL,
          `description`      TEXT,
          `category_id`      INT NULL,
          `style_type`       ENUM('box_braids','cornrows','knotless','twists','locs','weave','natural','updo','other') DEFAULT 'other',
          `duration_hours`   DECIMAL(4,1),
          `price_from`       DECIMAL(10,2),
          `price_to`         DECIMAL(10,2),
          `image_url`        VARCHAR(500) NOT NULL,
          `before_image_url` VARCHAR(500) NULL,
          `video_url`        VARCHAR(500) NULL,
          `images`           JSON,
          `blur_hash`        TEXT NULL,
          `is_featured`      TINYINT(1) DEFAULT 0,
          `display_order`    INT DEFAULT 0,
          `is_active`        TINYINT(1) DEFAULT 1,
          `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE SET NULL,
          INDEX idx_category (`category_id`),
          INDEX idx_featured (`is_featured`),
          FULLTEXT idx_search (`title`, `description`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'booking_slots' => "CREATE TABLE IF NOT EXISTS `booking_slots` (
          `id`           INT AUTO_INCREMENT PRIMARY KEY,
          `slot_date`    DATE NOT NULL,
          `start_time`   TIME NOT NULL,
          `end_time`     TIME NOT NULL,
          `is_available` TINYINT(1) DEFAULT 1,
          `max_bookings` INT DEFAULT 1,
          `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY unique_slot (`slot_date`, `start_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'appointments' => "CREATE TABLE IF NOT EXISTS `appointments` (
          `id`                 INT AUTO_INCREMENT PRIMARY KEY,
          `appointment_number` VARCHAR(30) UNIQUE NOT NULL,
          `customer_name`      VARCHAR(100) NOT NULL,
          `customer_email`     VARCHAR(150),
          `customer_phone`     VARCHAR(30) NOT NULL,
          `user_id`            INT NULL,
          `gallery_image_id`   INT NULL,
          `slot_id`            INT NULL,
          `requested_date`     DATE NOT NULL,
          `requested_time`     TIME NOT NULL,
          `duration_estimate`  DECIMAL(4,1),
          `status`             ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
          `deposit_paid`       TINYINT(1) DEFAULT 0,
          `deposit_amount`     DECIMAL(10,2) NULL,
          `paystack_ref`       VARCHAR(100) NULL,
          `admin_notes`        TEXT,
          `customer_notes`     TEXT,
          `confirmed_at`       TIMESTAMP NULL,
          `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
          FOREIGN KEY (`gallery_image_id`) REFERENCES `gallery_images`(`id`) ON DELETE SET NULL,
          INDEX idx_status (`status`),
          INDEX idx_date   (`requested_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'waitlist' => "CREATE TABLE IF NOT EXISTS `waitlist` (
          `id`               INT AUTO_INCREMENT PRIMARY KEY,
          `customer_name`    VARCHAR(100) NOT NULL,
          `customer_phone`   VARCHAR(30) NOT NULL,
          `customer_email`   VARCHAR(150),
          `preferred_date`   DATE NOT NULL,
          `gallery_image_id` INT NULL,
          `notified`         TINYINT(1) DEFAULT 0,
          `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`gallery_image_id`) REFERENCES `gallery_images`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'testimonials' => "CREATE TABLE IF NOT EXISTS `testimonials` (
          `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `user_id`         INT UNSIGNED,
          `author_name`     VARCHAR(120) NOT NULL,
          `author_location` VARCHAR(120),
          `photo_url`       VARCHAR(500),
          `service`         VARCHAR(150),
          `content`         TEXT NOT NULL,
          `rating`          TINYINT(1) NOT NULL DEFAULT 5,
          `is_approved`     TINYINT(1) NOT NULL DEFAULT 0,
          `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
          `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          INDEX idx_approved  (is_approved),
          INDEX idx_featured  (is_featured),
          INDEX idx_user_id   (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'bundles' => "CREATE TABLE IF NOT EXISTS `bundles` (
          `id`                  INT AUTO_INCREMENT PRIMARY KEY,
          `name`                VARCHAR(200) NOT NULL,
          `slug`                VARCHAR(220) UNIQUE NOT NULL,
          `description`         TEXT,
          `image`               VARCHAR(500),
          `discount_percentage` DECIMAL(5,2) DEFAULT 0,
          `is_active`           TINYINT(1) DEFAULT 1,
          `is_featured`         TINYINT(1) DEFAULT 0,
          `display_order`       INT DEFAULT 0,
          `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'bundle_items' => "CREATE TABLE IF NOT EXISTS `bundle_items` (
          `id`         INT AUTO_INCREMENT PRIMARY KEY,
          `bundle_id`  INT NOT NULL,
          `product_id` INT NOT NULL,
          `quantity`   INT DEFAULT 1,
          FOREIGN KEY (`bundle_id`) REFERENCES `bundles`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'blog_posts' => "CREATE TABLE IF NOT EXISTS `blog_posts` (
          `id`               INT AUTO_INCREMENT PRIMARY KEY,
          `title`            VARCHAR(250) NOT NULL,
          `slug`             VARCHAR(270) UNIQUE NOT NULL,
          `excerpt`          TEXT,
          `body`             LONGTEXT,
          `featured_image`   VARCHAR(500),
          `author`           VARCHAR(120) DEFAULT 'GYC Naturals Team',
          `category`         VARCHAR(80),
          `tags`             VARCHAR(500),
          `status`           ENUM('draft','published') NOT NULL DEFAULT 'draft',
          `is_featured`      TINYINT(1) NOT NULL DEFAULT 0,
          `read_time`        TINYINT UNSIGNED NOT NULL DEFAULT 3,
          `view_count`       INT UNSIGNED NOT NULL DEFAULT 0,
          `meta_title`       VARCHAR(250),
          `meta_description` VARCHAR(320),
          `published_at`     DATETIME,
          `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX idx_status   (status),
          INDEX idx_category (category),
          FULLTEXT idx_search (`title`, `excerpt`, `body`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'quiz_results' => "CREATE TABLE IF NOT EXISTS `quiz_results` (
          `id`                   INT AUTO_INCREMENT PRIMARY KEY,
          `session_token`        VARCHAR(64),
          `hair_type`            VARCHAR(50),
          `hair_length`          VARCHAR(50),
          `hair_goal`            VARCHAR(100),
          `lifestyle`            VARCHAR(50),
          `recommended_styles`   JSON,
          `recommended_products` JSON,
          `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'contact_messages' => "CREATE TABLE IF NOT EXISTS `contact_messages` (
          `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `name`       VARCHAR(120) NOT NULL,
          `email`      VARCHAR(180) NOT NULL,
          `subject`    VARCHAR(200) NOT NULL,
          `message`    TEXT NOT NULL,
          `ip_hash`    VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 of submitter IP (rate limiting)',
          `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          INDEX idx_is_read (is_read),
          INDEX idx_created_at (created_at),
          INDEX idx_ip_hash (ip_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'product_categories' => "CREATE TABLE IF NOT EXISTS `product_categories` (
          `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `name`        VARCHAR(120) NOT NULL,
          `slug`        VARCHAR(140) NOT NULL UNIQUE,
          `description` TEXT,
          `image`       VARCHAR(400),
          `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
          `display_order` INT NOT NULL DEFAULT 99,
          `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Table `$name` ready.\n";
        } catch (PDOException $e) {
            echo "❌ Table `$name` failed: " . $e->getMessage() . "\n";
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ── Schema migrations for existing installations ──────────
    // These are safe ALTER TABLE ... ADD COLUMN IF NOT EXISTS statements
    // that fix schema gaps added after initial install.
    $migrations = [
        "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_email` VARCHAR(180) DEFAULT NULL COMMENT 'Captured at checkout' AFTER `user_id`",
        "ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `ip_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 of submitter IP' AFTER `message`",
    ];
    foreach ($migrations as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Migration applied.\n";
        } catch (PDOException $e) {
            // Older MySQL may not support IF NOT EXISTS — safe to ignore duplicate column errors
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "⚠ Migration: " . $e->getMessage() . "\n";
            } else {
                echo "✓ Column already exists — skipped.\n";
            }
        }
    }

    // Create admin user
    $adminEmail    = 'admin@gycnaturals.com';
    $adminPassword = password_hash('Admin@2025', PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, is_active, email_verified) VALUES (?,?,?,?,?,?,?)")
            ->execute(['GYC', 'Admin', $adminEmail, $adminPassword, 'admin', 1, 1]);
        echo "✓ Admin user created: admin@gycnaturals.com / Admin@2025\n";
    } else {
        echo "✓ Admin user already exists.\n";
    }

    echo "\n✅ Installation complete!\n";
    echo "Next: visit http://localhost/gyc-store/seed.php to populate data.\n";
    echo "⚠ DELETE install.php after installation.\n";

} catch (PDOException $e) {
    echo "❌ Fatal: " . $e->getMessage() . "\n";
}
