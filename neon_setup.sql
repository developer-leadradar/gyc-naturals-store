-- =============================================================
-- GYC Naturals — PostgreSQL Schema for Neon
-- Run this in Neon's SQL Editor once to set up all tables
-- =============================================================

-- Drop existing types & tables cleanly (CASCADE handles dependencies)
DROP TABLE IF EXISTS quiz_results CASCADE;
DROP TABLE IF EXISTS contact_messages CASCADE;
DROP TABLE IF EXISTS product_categories CASCADE;
DROP TABLE IF EXISTS blog_posts CASCADE;
DROP TABLE IF EXISTS bundle_items CASCADE;
DROP TABLE IF EXISTS bundles CASCADE;
DROP TABLE IF EXISTS testimonials CASCADE;
DROP TABLE IF EXISTS waitlist CASCADE;
DROP TABLE IF EXISTS appointments CASCADE;
DROP TABLE IF EXISTS booking_slots CASCADE;
DROP TABLE IF EXISTS gallery_images CASCADE;
DROP TABLE IF EXISTS gallery_categories CASCADE;
DROP TABLE IF EXISTS site_settings CASCADE;
DROP TABLE IF EXISTS password_resets CASCADE;
DROP TABLE IF EXISTS email_verifications CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS addresses CASCADE;
DROP TABLE IF EXISTS wishlist CASCADE;
DROP TABLE IF EXISTS cart_items CASCADE;
DROP TABLE IF EXISTS cart CASCADE;
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

DROP TYPE IF EXISTS user_role CASCADE;
DROP TYPE IF EXISTS order_status_t CASCADE;
DROP TYPE IF EXISTS payment_status_t CASCADE;
DROP TYPE IF EXISTS appointment_status_t CASCADE;
DROP TYPE IF EXISTS blog_status_t CASCADE;
DROP TYPE IF EXISTS style_type_t CASCADE;

-- ENUM types
CREATE TYPE user_role         AS ENUM ('customer','admin');
CREATE TYPE order_status_t    AS ENUM ('pending','processing','shipped','delivered','cancelled','refunded');
CREATE TYPE payment_status_t  AS ENUM ('pending','paid','failed','refunded');
CREATE TYPE appointment_status_t AS ENUM ('pending','confirmed','cancelled','completed');
CREATE TYPE blog_status_t     AS ENUM ('draft','published');
CREATE TYPE style_type_t      AS ENUM ('box_braids','cornrows','knotless','twists','locs','weave','natural','updo','other');

-- ── Users ──────────────────────────────────────────────────
CREATE TABLE users (
  id             SERIAL PRIMARY KEY,
  first_name     VARCHAR(80)  NOT NULL,
  last_name      VARCHAR(80)  NOT NULL,
  email          VARCHAR(150) UNIQUE NOT NULL,
  password       VARCHAR(255) NOT NULL,
  phone          VARCHAR(30),
  role           user_role DEFAULT 'customer',
  is_active      SMALLINT DEFAULT 1,
  email_verified SMALLINT DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_role  ON users (role);

-- ── Categories ────────────────────────────────────────────
CREATE TABLE categories (
  id            SERIAL PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  slug          VARCHAR(120) UNIQUE NOT NULL,
  description   TEXT,
  image         VARCHAR(500),
  parent_id     INTEGER NULL REFERENCES categories(id) ON DELETE SET NULL,
  display_order INTEGER DEFAULT 0,
  is_active     SMALLINT DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Products ──────────────────────────────────────────────
CREATE TABLE products (
  id             SERIAL PRIMARY KEY,
  name           VARCHAR(200) NOT NULL,
  slug           VARCHAR(220) UNIQUE NOT NULL,
  description    TEXT,
  short_desc     VARCHAR(500),
  sku            VARCHAR(100),
  price          DECIMAL(10,2) NOT NULL DEFAULT 0,
  compare_price  DECIMAL(10,2) NULL,
  cost_price     DECIMAL(10,2) NULL,
  stock_quantity INTEGER DEFAULT 0,
  category_id    INTEGER NULL REFERENCES categories(id) ON DELETE SET NULL,
  brand          VARCHAR(100),
  image          VARCHAR(500),
  images         JSONB,
  is_active      SMALLINT DEFAULT 1,
  is_featured    SMALLINT DEFAULT 0,
  rating         DECIMAL(3,2) DEFAULT 0,
  review_count   INTEGER DEFAULT 0,
  display_order  INTEGER DEFAULT 0,
  hair_type      VARCHAR(100),
  product_type   VARCHAR(100),
  key_ingredient VARCHAR(150),
  volume_ml      DECIMAL(8,2),
  suitable_for   VARCHAR(100),
  scent          VARCHAR(80),
  concern        VARCHAR(150),
  clothing_size  VARCHAR(50) NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_products_category ON products (category_id);
CREATE INDEX idx_products_featured ON products (is_featured);

-- ── Orders ────────────────────────────────────────────────
CREATE TABLE orders (
  id                   SERIAL PRIMARY KEY,
  order_number         VARCHAR(30)  UNIQUE NOT NULL,
  user_id              INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
  customer_email       VARCHAR(180) DEFAULT NULL,
  status               order_status_t   DEFAULT 'pending',
  payment_status       payment_status_t DEFAULT 'pending',
  payment_method       VARCHAR(50)  DEFAULT 'paystack',
  paystack_ref         VARCHAR(100) NULL,
  subtotal             DECIMAL(10,2) DEFAULT 0,
  tax                  DECIMAL(10,2) DEFAULT 0,
  shipping             DECIMAL(10,2) DEFAULT 0,
  discount             DECIMAL(10,2) DEFAULT 0,
  total                DECIMAL(10,2) DEFAULT 0,
  shipping_first_name  VARCHAR(80),
  shipping_last_name   VARCHAR(80),
  shipping_address     VARCHAR(300),
  shipping_city        VARCHAR(100),
  shipping_state       VARCHAR(100),
  shipping_zip         VARCHAR(20),
  shipping_country     VARCHAR(100) DEFAULT 'Nigeria',
  shipping_phone       VARCHAR(30),
  billing_first_name   VARCHAR(80),
  billing_last_name    VARCHAR(80),
  billing_address      VARCHAR(300),
  billing_city         VARCHAR(100),
  billing_state        VARCHAR(100),
  billing_zip          VARCHAR(20),
  billing_country      VARCHAR(100) DEFAULT 'Nigeria',
  billing_phone        VARCHAR(30),
  notes                TEXT,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_orders_status  ON orders (status);
CREATE INDEX idx_orders_payment ON orders (payment_status);

-- ── Order Items ───────────────────────────────────────────
CREATE TABLE order_items (
  id                SERIAL PRIMARY KEY,
  order_id          INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_id        INTEGER NULL     REFERENCES products(id) ON DELETE SET NULL,
  product_name      VARCHAR(200) NOT NULL,
  quantity          INTEGER DEFAULT 1,
  price_at_purchase DECIMAL(10,2) NOT NULL,
  subtotal          DECIMAL(10,2) NOT NULL,
  bundle_id         INTEGER NULL
);

-- ── Cart ──────────────────────────────────────────────────
CREATE TABLE cart (
  id         SERIAL PRIMARY KEY,
  user_id    INTEGER NULL REFERENCES users(id) ON DELETE CASCADE,
  session_id VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart_items (
  id         SERIAL PRIMARY KEY,
  cart_id    INTEGER NOT NULL REFERENCES cart(id) ON DELETE CASCADE,
  product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  quantity   INTEGER DEFAULT 1,
  bundle_id  INTEGER NULL
);

-- ── Wishlist ──────────────────────────────────────────────
CREATE TABLE wishlist (
  id         SERIAL PRIMARY KEY,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, product_id)
);

-- ── Addresses ─────────────────────────────────────────────
CREATE TABLE addresses (
  id         SERIAL PRIMARY KEY,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  label      VARCHAR(50) DEFAULT 'Home',
  first_name VARCHAR(80),
  last_name  VARCHAR(80),
  address    VARCHAR(300),
  city       VARCHAR(100),
  state      VARCHAR(100),
  zip        VARCHAR(20),
  country    VARCHAR(100) DEFAULT 'Nigeria',
  phone      VARCHAR(30),
  is_default SMALLINT DEFAULT 0
);

-- ── Reviews ───────────────────────────────────────────────
CREATE TABLE reviews (
  id          SERIAL PRIMARY KEY,
  product_id  INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  rating      SMALLINT DEFAULT 5,
  title       VARCHAR(200),
  body        TEXT,
  is_approved SMALLINT DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(product_id, user_id)
);

-- ── Email Verifications ───────────────────────────────────
CREATE TABLE email_verifications (
  id         SERIAL PRIMARY KEY,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  token      VARCHAR(100) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  used       SMALLINT DEFAULT 0
);

-- ── Password Resets ───────────────────────────────────────
CREATE TABLE password_resets (
  id         SERIAL PRIMARY KEY,
  email      VARCHAR(150) NOT NULL,
  token      VARCHAR(100) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  used       SMALLINT DEFAULT 0
);
CREATE INDEX idx_pwreset_email ON password_resets (email);

-- ── Site Settings ─────────────────────────────────────────
CREATE TABLE site_settings (
  id          SERIAL PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_val TEXT,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Gallery Categories ────────────────────────────────────
CREATE TABLE gallery_categories (
  id            SERIAL PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  slug          VARCHAR(120) UNIQUE NOT NULL,
  description   TEXT,
  image         VARCHAR(500),
  display_order INTEGER DEFAULT 0,
  is_active     SMALLINT DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Gallery Images ────────────────────────────────────────
CREATE TABLE gallery_images (
  id               SERIAL PRIMARY KEY,
  title            VARCHAR(200) NOT NULL,
  slug             VARCHAR(220) UNIQUE NOT NULL,
  description      TEXT,
  category_id      INTEGER NULL REFERENCES gallery_categories(id) ON DELETE SET NULL,
  style_type       style_type_t DEFAULT 'other',
  duration_hours   DECIMAL(4,1),
  price_from       DECIMAL(10,2),
  price_to         DECIMAL(10,2),
  image_url        VARCHAR(500) NOT NULL,
  before_image_url VARCHAR(500) NULL,
  video_url        VARCHAR(500) NULL,
  images           JSONB,
  blur_hash        TEXT NULL,
  is_featured      SMALLINT DEFAULT 0,
  display_order    INTEGER DEFAULT 0,
  is_active        SMALLINT DEFAULT 1,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_gallery_category ON gallery_images (category_id);
CREATE INDEX idx_gallery_featured ON gallery_images (is_featured);

-- ── Booking Slots ─────────────────────────────────────────
CREATE TABLE booking_slots (
  id           SERIAL PRIMARY KEY,
  slot_date    DATE NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  is_available SMALLINT DEFAULT 1,
  max_bookings INTEGER DEFAULT 1,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(slot_date, start_time)
);

-- ── Appointments ──────────────────────────────────────────
CREATE TABLE appointments (
  id                 SERIAL PRIMARY KEY,
  appointment_number VARCHAR(30) UNIQUE NOT NULL,
  customer_name      VARCHAR(100) NOT NULL,
  customer_email     VARCHAR(150),
  customer_phone     VARCHAR(30) NOT NULL,
  user_id            INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
  gallery_image_id   INTEGER NULL REFERENCES gallery_images(id) ON DELETE SET NULL,
  slot_id            INTEGER NULL,
  requested_date     DATE NOT NULL,
  requested_time     TIME NOT NULL,
  duration_estimate  DECIMAL(4,1),
  status             appointment_status_t DEFAULT 'pending',
  deposit_paid       SMALLINT DEFAULT 0,
  deposit_amount     DECIMAL(10,2) NULL,
  paystack_ref       VARCHAR(100) NULL,
  admin_notes        TEXT,
  customer_notes     TEXT,
  confirmed_at       TIMESTAMP NULL,
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_appt_status ON appointments (status);
CREATE INDEX idx_appt_date   ON appointments (requested_date);

-- ── Waitlist ──────────────────────────────────────────────
CREATE TABLE waitlist (
  id               SERIAL PRIMARY KEY,
  customer_name    VARCHAR(100) NOT NULL,
  customer_phone   VARCHAR(30)  NOT NULL,
  customer_email   VARCHAR(150),
  preferred_date   DATE NOT NULL,
  gallery_image_id INTEGER NULL REFERENCES gallery_images(id) ON DELETE SET NULL,
  notified         SMALLINT DEFAULT 0,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Testimonials ──────────────────────────────────────────
CREATE TABLE testimonials (
  id              SERIAL PRIMARY KEY,
  user_id         INTEGER,
  author_name     VARCHAR(120) NOT NULL,
  author_location VARCHAR(120),
  photo_url       VARCHAR(500),
  service         VARCHAR(150),
  content         TEXT NOT NULL,
  rating          SMALLINT NOT NULL DEFAULT 5,
  is_approved     SMALLINT NOT NULL DEFAULT 0,
  is_featured     SMALLINT NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_test_approved ON testimonials (is_approved);
CREATE INDEX idx_test_featured ON testimonials (is_featured);
CREATE INDEX idx_test_user     ON testimonials (user_id);

-- ── Bundles ───────────────────────────────────────────────
CREATE TABLE bundles (
  id                  SERIAL PRIMARY KEY,
  name                VARCHAR(200) NOT NULL,
  slug                VARCHAR(220) UNIQUE NOT NULL,
  description         TEXT,
  image               VARCHAR(500),
  discount_percentage DECIMAL(5,2) DEFAULT 0,
  is_active           SMALLINT DEFAULT 1,
  is_featured         SMALLINT DEFAULT 0,
  display_order       INTEGER DEFAULT 0,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bundle_items (
  id         SERIAL PRIMARY KEY,
  bundle_id  INTEGER NOT NULL REFERENCES bundles(id)  ON DELETE CASCADE,
  product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
  quantity   INTEGER DEFAULT 1
);

-- ── Blog Posts ────────────────────────────────────────────
CREATE TABLE blog_posts (
  id               SERIAL PRIMARY KEY,
  title            VARCHAR(250) NOT NULL,
  slug             VARCHAR(270) UNIQUE NOT NULL,
  excerpt          TEXT,
  body             TEXT,
  featured_image   VARCHAR(500),
  author           VARCHAR(120) DEFAULT 'GYC Naturals Team',
  category         VARCHAR(80),
  tags             VARCHAR(500),
  status           blog_status_t NOT NULL DEFAULT 'draft',
  is_featured      SMALLINT NOT NULL DEFAULT 0,
  read_time        SMALLINT NOT NULL DEFAULT 3,
  view_count       INTEGER NOT NULL DEFAULT 0,
  meta_title       VARCHAR(250),
  meta_description VARCHAR(320),
  published_at     TIMESTAMP,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_blog_status   ON blog_posts (status);
CREATE INDEX idx_blog_category ON blog_posts (category);

-- ── Quiz Results ──────────────────────────────────────────
CREATE TABLE quiz_results (
  id                   SERIAL PRIMARY KEY,
  session_token        VARCHAR(64),
  hair_type            VARCHAR(50),
  hair_length          VARCHAR(50),
  hair_goal            VARCHAR(100),
  lifestyle            VARCHAR(50),
  recommended_styles   JSONB,
  recommended_products JSONB,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Contact Messages ──────────────────────────────────────
CREATE TABLE contact_messages (
  id         SERIAL PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(180) NOT NULL,
  subject    VARCHAR(200) NOT NULL,
  message    TEXT NOT NULL,
  ip_hash    VARCHAR(64) DEFAULT NULL,
  is_read    SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_contact_read    ON contact_messages (is_read);
CREATE INDEX idx_contact_created ON contact_messages (created_at);
CREATE INDEX idx_contact_ip      ON contact_messages (ip_hash);

-- ── Product Categories ────────────────────────────────────
CREATE TABLE product_categories (
  id            SERIAL PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  slug          VARCHAR(140) NOT NULL UNIQUE,
  description   TEXT,
  image         VARCHAR(400),
  is_active     SMALLINT NOT NULL DEFAULT 1,
  display_order INTEGER NOT NULL DEFAULT 99,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ── Admin User ────────────────────────────────────────────
-- password hash for: Admin@2025
INSERT INTO users (first_name, last_name, email, password, role, is_active, email_verified)
VALUES ('GYC', 'Admin', 'admin@gycnaturals.com',
        '$2y$12$LakhUj2rvV8DttoiZQUCa.p761dOgyPChnX/MUM/zdcc2BPWRz73q',
        'admin', 1, 1)
ON CONFLICT (email) DO NOTHING;

-- ============================================================
-- Schema setup complete! 26 tables created.
-- Next: run seed data or visit /install.php to seed the DB.
-- ============================================================
