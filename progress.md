# GYC Naturals — Build Progress

## Status: ✅ ALL 35 PHASES COMPLETE
## Last Updated: 2026-06-09
## PHP Syntax: 0 errors across 87 files

---

## Phase Completion

- [x] Phase 0:  Directory & progress setup
- [x] Phase 1:  Foundation — config.php, install.php, db.php (PDO singleton), session setup
- [x] Phase 2:  Design system — CSS tokens, SVG logo, Kente stripe, Adinkra motifs
- [x] Phase 3:  Core includes — header.php, footer.php, functions.php, nav with cart badge
- [x] Phase 4:  Database seeding — install.php creates all tables; seed.php populates demo data
- [x] Phase 5:  African design elements — Ankara patterns, terracotta, gold palette, Playfair Display
- [x] Phase 6:  Homepage — hero, stats, gallery strip, services, bundles, testimonials, blog, CTA
- [x] Phase 7:  Gallery system — masonry grid, category filter, before/after slider, style-detail.php
- [x] Phase 8:  Hair consultation quiz — 6-step quiz, quiz-result.php, recommendation engine
- [x] Phase 9:  Appointment booking — book-appointment.php, style selector, slot picker, booking-confirmation.php
- [x] Phase 10: Shop system — shop.php with AJAX filter, product.php with reviews
- [x] Phase 11: Product bundles — bundle.php, bundle detail, savings calculator
- [x] Phase 12: Cart & checkout — cart.php, checkout.php, Paystack inline JS, api/paystack-verify.php
- [x] Phase 13: Style moodboard — moodboard.php, localStorage persistence, api/moodboard-items.php
- [x] Phase 14: Auth & customer portal — register, login, logout, forgot/reset password, customer-dashboard.php, my-orders, my-appointments, my-wishlist, my-profile
- [x] Phase 15: Testimonials — testimonials.php, star picker, duplicate guard, approval queue
- [x] Phase 16: Blog — blog.php, blog-post.php, categories, related posts, view counter
- [x] Phase 17: Legal pages — privacy.php, terms.php, refund.php, faq.php, about.php, services.php, contact.php, offline.php
- [x] Phase 18: Admin base — admin/index.php (dashboard + Canvas charts), login, logout, shared header/footer with badge system
- [x] Phase 19: Admin gallery — admin/gallery.php, add-gallery.php, gallery-categories.php
- [x] Phase 20: Admin appointments — admin/appointments.php, booking-calendar.php, waitlist.php
- [x] Phase 21: Admin bundles — admin/bundles.php (list + form, dynamic items, discount calc)
- [x] Phase 22: Admin testimonials + blog + messages — admin/testimonials.php, blog.php, messages.php (inbox with reply + WhatsApp)
- [x] Phase 23: PWA — manifest.json (shortcuts, screenshots, 6 icon sizes), service-worker.js (3 caches, stale-while-revalidate, offline fallback)
- [x] Phase 24: SEO — sitemap.php (dynamic XML), robots.txt, JSON-LD schemas (BeautySalon, Product, Article), OpenGraph meta
- [x] Phase 25: WhatsApp automation — api/whatsapp-redirect.php (7 contexts, JSON mode), whatsappMessage() helper, float button
- [x] Phase 26: .htaccess & 404 — security headers, cache control, compression, 404.php, mod_expires
- [x] Phase 27: Database seeder — seed.php (6 categories, 12 products, 9 gallery images, 4 blog posts, 7 testimonials, 3 bundles, 23 site settings) with INSERT IGNORE
- [x] Phase 28: Branded HTML email templates — includes/email-templates.php (order confirm, booking confirm, welcome, password reset, order status update, appointment update); wired into paystack-verify.php, create-booking.php, register.php, forgot-password.php
- [x] Phase 29: Clothing & fashion page — clothing.php (hero, category strip, brand story, product grid, lookbook, size guide, custom order CTA); added to nav + sitemap
- [x] Phase 30: Admin email dispatch — admin/orders.php & appointments.php now send branded status-update emails with optional admin note; admin/settings.php bug fixed (wrong table + column names)
- [x] Phase 31: CSS design tokens & responsive — style.css :root fully expanded (--gyc-gold, --gyc-green-600, --gyc-radius-xl etc.); admin.css mirrored; responsive.css additions (clothing breakpoints, lazy-image fade-in, focus-visible, prefers-reduced-motion, content-visibility utilities)
- [x] Phase 32: Performance — IntersectionObserver lazy-image fade-in in footer.php (rootMargin 200px); cookie banner instant-hide from localStorage
- [x] Phase 33: Security hardening — ADMIN_EMAIL constant added to config.php; contact.php rate-limiting (3/hr/IP via ip_hash); .htaccess blocks install/seed/deploy scripts from non-localhost; install.php schema migration block for new columns
- [x] Phase 34: QA cross-check — orders.billing_name computed in SQL; customer_email added to orders schema; emailOrderConfirmation accepts both shipping/shipping_cost keys; duplicate createAppointment() email removed; all 87 PHP files pass php -l
- [x] Phase 35: Deployment — deploy-checklist.php (40-point auto-check + manual checklist + .env template); .env.example fully documented; .htaccess blocks checklist from public access

---

## File Inventory (87 PHP files, 0 syntax errors)

### Public Pages
| File | Description |
|------|-------------|
| index.php | Homepage with all sections |
| gallery.php | Masonry gallery with filter |
| style-detail.php | Single style page with booking CTA |
| shop.php | Product shop with AJAX filter |
| product.php | Product detail with reviews |
| bundle.php | Bundle detail page |
| clothing.php | Fashion/clothing sub-page |
| book-appointment.php | Multi-step booking flow |
| booking-confirmation.php | Booking success page |
| quiz.php | 6-step hair consultation quiz |
| quiz-result.php | Quiz results + recommendations |
| blog.php | Blog listing with categories |
| blog-post.php | Single blog post |
| testimonials.php | Reviews grid + submit form |
| moodboard.php | Saved looks board |
| cart.php | Shopping cart |
| checkout.php | Checkout with Paystack |
| order-details.php | Order confirmation + tracking |
| about.php | About page |
| services.php | Services listing |
| contact.php | Contact form with rate-limiting |
| faq.php | FAQ accordion |
| privacy.php | Privacy policy |
| terms.php | Terms & conditions |
| refund.php | Refund policy |
| offline.php | PWA offline fallback |
| 404.php | Custom 404 page |

### Auth Pages
| File | Description |
|------|-------------|
| login.php | Customer/admin login |
| register.php | New customer registration |
| logout.php | Session destroy + redirect |
| forgot-password.php | Password reset request |
| reset-password.php | Token-validated password reset |
| customer-dashboard.php | Account overview |
| my-orders.php | Order history |
| my-appointments.php | Appointment history |
| my-wishlist.php | Saved products |
| my-profile.php | Edit profile |

### API Endpoints
| File | Description |
|------|-------------|
| api/paystack-verify.php | Payment verify + order create |
| api/paystack-webhook.php | Paystack server-side webhook |
| api/create-booking.php | Appointment create + email |
| api/add-to-cart.php | Add product to cart |
| api/update-cart.php | Update cart qty/remove |
| api/add-to-wishlist.php | Toggle wishlist item |
| api/filter-products.php | AJAX product filter |
| api/filter-gallery.php | AJAX gallery filter |
| api/search-autocomplete.php | Live search suggestions |
| api/submit-review.php | Product review submit |
| api/check-availability.php | Booking slot check |
| api/join-waitlist.php | Join booking waitlist |
| api/moodboard-items.php | Moodboard CRUD |
| api/whatsapp-redirect.php | Context-aware WA links |
| api/toggle-status.php | Admin status toggle |

### Admin Panel
| File | Description |
|------|-------------|
| admin/index.php | Dashboard + Canvas charts |
| admin/orders.php | Order management + email dispatch |
| admin/products.php | Product listing |
| admin/add-product.php | Add/edit product |
| admin/categories.php | Product categories |
| admin/gallery.php | Gallery management |
| admin/add-gallery.php | Add/edit gallery image |
| admin/gallery-categories.php | Gallery category CRUD |
| admin/appointments.php | Appointment management + email |
| admin/booking-calendar.php | Calendar view |
| admin/waitlist.php | Waitlist management |
| admin/blog.php | Blog editor (contenteditable) |
| admin/testimonials.php | Review moderation queue |
| admin/bundles.php | Bundle builder |
| admin/messages.php | Contact form inbox |
| admin/customers.php | Customer list |
| admin/reports.php | Analytics + charts |
| admin/settings.php | Site settings (6 tabs) |
| admin/login.php | Admin login |
| admin/logout.php | Admin logout |

### Includes
| File | Description |
|------|-------------|
| includes/config.php | → root config.php |
| includes/db.php | PDO singleton Database class |
| includes/functions.php | ~50 helper functions |
| includes/email-templates.php | 6 branded HTML email builders |
| includes/header.php | Nav, meta, CSS links |
| includes/footer.php | Footer, scripts, SW registration |
| includes/dash-sidebar.php | Customer dashboard nav |
| admin/includes/header.php | Admin nav + badge counts |
| admin/includes/footer.php | Admin footer + Lucide init |

### Setup / Utility (localhost only)
| File | Description |
|------|-------------|
| install.php | Create all DB tables + admin user |
| seed.php | Populate demo data |
| generate-icons.php | Create PWA icon sizes from 512px |
| deploy-checklist.php | 40-point pre-launch verification |

---

## Bugs Fixed (this build)

| Bug | Fix |
|-----|-----|
| blog_posts schema mismatch | `is_published`→`status`, `author_name`→`author`, `cover_image`→`featured_image`, `read_time_minutes`→`read_time` |
| testimonials schema mismatch | `client_name`→`author_name`, `client_photo`→`photo_url`, `quote`→`content`, `style_done`→`service` |
| getAllTestimonials() showed unapproved | Added `is_approved = 1` filter |
| sendEmail() called with 4 params | Corrected to 3-param signature |
| sitemap.php wrong require paths | Fixed to `config.php` + `includes/db.php` |
| admin/settings.php wrong table | `settings`→`site_settings`, `setting_value`→`setting_val` |
| orders.billing_name missing | Computed as `CONCAT(shipping_first_name, ' ', shipping_last_name)` in SQL |
| orders.customer_email missing | Added to schema; install.php migration block |
| contact_messages.ip_hash missing | Added to schema; install.php migration block |
| ADMIN_EMAIL undefined | Added constant to config.php |
| --gyc-gold/--gyc-green-600/--gyc-radius-xl unresolved | Added all missing tokens to :root in style.css + admin.css |
| Duplicate booking confirmation email | Removed inline sendEmail() from createAppointment(); email sent by API endpoint only |
| bundles.php form not showing for new | `isset($_GET['edit'])` instead of `if ($editId)` |
| display_order in testimonials insert | Removed; replaced with `$t['id'] % count($accents)` |

---

## Credentials

| Item | Value |
|------|-------|
| Admin email | admin@gycnaturals.com |
| Admin password | Admin@2025 |
| DB name | gyc_store |
| DB user | root |
| DB pass | (empty) |
| Local URL | http://localhost/gyc-store |

---

## To Do Before Going Live

- [ ] Run `http://localhost/gyc-store/install.php` (creates tables + admin user)
- [ ] Run `http://localhost/gyc-store/seed.php` (populates demo data)
- [ ] Run `http://localhost/gyc-store/generate-icons.php` (creates PWA icon sizes)
- [ ] Replace Paystack **test** keys with **live** keys (`pk_live_` / `sk_live_`)
- [ ] Set `RESEND_API_KEY` in `.env` for transactional emails
- [ ] Set `SITE_URL=https://yourdomain.com` in `.env`
- [ ] Replace all Unsplash image URLs with real GYC Naturals photography
- [ ] Force HTTPS — uncomment the RewriteRule block in `.htaccess`
- [ ] Delete `install.php`, `seed.php`, `generate-icons.php`, `deploy-checklist.php` from live server
- [ ] Submit `sitemap.php` to Google Search Console
- [ ] Run deploy-checklist.php and resolve any remaining failures

---

## Step Log

| Date | Phase | Action |
|------|-------|--------|
| 2026-06-06 | 0 | Directory tree, .env, .htaccess, progress.md created |
| 2026-06-06 | 1–10 | Foundation, design system, includes, homepage, gallery, quiz, booking, shop |
| 2026-06-07 | 11–17 | Bundles, cart/checkout/Paystack, moodboard, auth, testimonials, blog, legal pages |
| 2026-06-07 | 18–20 | Admin base, gallery admin, appointments admin |
| 2026-06-08 | 21–27 | Admin bundles, testimonials, blog, messages; PWA; SEO; WhatsApp; .htaccess; seeder |
| 2026-06-08 | —    | Schema consistency sweep (blog_posts + testimonials column renames across 38 files) |
| 2026-06-09 | 28   | Branded HTML email templates (6 functions) wired into all transactional flows |
| 2026-06-09 | 29   | Clothing & fashion page, added to nav + sitemap |
| 2026-06-09 | 30   | Admin order/appointment email dispatch; settings.php table bug fixed |
| 2026-06-09 | 31   | CSS design tokens expanded (--gyc-gold, --gyc-green-600, --gyc-radius-xl etc.) |
| 2026-06-09 | 32   | Performance: lazy-image IntersectionObserver, cookie banner instant-hide |
| 2026-06-09 | 33   | Security: rate-limiting, ADMIN_EMAIL, .htaccess script-blocking, schema migrations |
| 2026-06-09 | 34   | QA: orders schema, email column fixes, customer_email in orders table, 0 syntax errors |
| 2026-06-09 | 35   | deploy-checklist.php (40 checks), .env.example documented, .htaccess protection |
