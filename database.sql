-- GYC Naturals Database Schema
-- Run via install.php

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ──────────────────────────────────────────────────────────
-- PART A: CORE TABLES
-- ──────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `users` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `first_name`      VARCHAR(80) NOT NULL,
  `last_name`       VARCHAR(80) NOT NULL,
  `email`           VARCHAR(150) UNIQUE NOT NULL,
  `password`        VARCHAR(255) NOT NULL,
  `phone`           VARCHAR(30),
  `role`            ENUM('customer','admin') DEFAULT 'customer',
  `is_active`       TINYINT(1) DEFAULT 1,
  `email_verified`  TINYINT(1) DEFAULT 0,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (`email`),
  INDEX idx_role  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
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
  -- Hair product fields
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NULL,
  `session_id` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart_items` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity`   INT DEFAULT 1,
  `bundle_id`  INT NULL,
  FOREIGN KEY (`cart_id`) REFERENCES `cart`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_wish (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `addresses` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id`    INT NOT NULL,
  `rating`     TINYINT(1) DEFAULT 5,
  `title`      VARCHAR(200),
  `body`       TEXT,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_review (`product_id`, `user_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `token`      VARCHAR(100) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used`       TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `email`      VARCHAR(150) NOT NULL,
  `token`      VARCHAR(100) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used`       TINYINT(1) DEFAULT 0,
  INDEX idx_email (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────
-- PART B: GYC-SPECIFIC TABLES
-- ──────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_val` TEXT,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(100) NOT NULL,
  `slug`          VARCHAR(120) UNIQUE NOT NULL,
  `description`   TEXT,
  `image`         VARCHAR(500),
  `display_order` INT DEFAULT 0,
  `is_active`     TINYINT(1) DEFAULT 1,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_images` (
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
  `blur_hash`        VARCHAR(500) NULL,
  `is_featured`      TINYINT(1) DEFAULT 0,
  `display_order`    INT DEFAULT 0,
  `is_active`        TINYINT(1) DEFAULT 1,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE SET NULL,
  INDEX idx_category (`category_id`),
  INDEX idx_featured (`is_featured`),
  FULLTEXT idx_search (`title`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `booking_slots` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `slot_date`    DATE NOT NULL,
  `start_time`   TIME NOT NULL,
  `end_time`     TIME NOT NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `max_bookings` INT DEFAULT 1,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_slot (`slot_date`, `start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `appointments` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `waitlist` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name`    VARCHAR(100) NOT NULL,
  `customer_phone`   VARCHAR(30) NOT NULL,
  `customer_email`   VARCHAR(150),
  `preferred_date`   DATE NOT NULL,
  `gallery_image_id` INT NULL,
  `notified`         TINYINT(1) DEFAULT 0,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`gallery_image_id`) REFERENCES `gallery_images`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `client_name`   VARCHAR(100) NOT NULL,
  `client_photo`  VARCHAR(500),
  `quote`         TEXT NOT NULL,
  `style_done`    VARCHAR(150),
  `rating`        TINYINT(1) DEFAULT 5,
  `is_featured`   TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bundles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bundle_items` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `bundle_id`  INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity`   INT DEFAULT 1,
  FOREIGN KEY (`bundle_id`) REFERENCES `bundles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `title`        VARCHAR(250) NOT NULL,
  `slug`         VARCHAR(270) UNIQUE NOT NULL,
  `excerpt`      TEXT,
  `body`         LONGTEXT,
  `cover_image`  VARCHAR(500),
  `author_name`  VARCHAR(100) DEFAULT 'GYC Naturals',
  `category`     VARCHAR(80),
  `is_published` TINYINT(1) DEFAULT 1,
  `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FULLTEXT idx_search (`title`, `excerpt`, `body`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_results` (
  `id`                   INT AUTO_INCREMENT PRIMARY KEY,
  `session_token`        VARCHAR(64),
  `hair_type`            VARCHAR(50),
  `hair_length`          VARCHAR(50),
  `hair_goal`            VARCHAR(100),
  `lifestyle`            VARCHAR(50),
  `recommended_styles`   JSON,
  `recommended_products` JSON,
  `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
