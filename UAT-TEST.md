# GYC Naturals — UAT Test Plan & Results

**Test URL:** https://gyc-naturals.vercel.app/  
**Tested By:** Developer / Claude Code  
**Date:** 2026-06-10  
**Environment:** Vercel Production (vercel-php@0.9.0, Neon PostgreSQL via Node.js proxy)

---

## Pre-Test Checklist

| Item | Status | Notes |
|------|--------|-------|
| SITE_URL env var set | ✅ | `https://gyc-naturals.vercel.app` — added 2026-06-10 |
| DB_PROXY_SECRET set | ✅ | Neon proxy authenticated |
| SITE_PHONE / SITE_WHATSAPP | ✅ | +2347037256585 / 2347037256585 |
| Business info updated | ✅ | Juliet Arah, Calabar, Est. 2024 |
| African images set | ✅ | Pexels CDN URLs in code + DB updated |
| Google Maps embed | ✅ | Big Qua Mall, Calabar iframe on contact.php |
| DB content update | ✅ | Ran update-content.php 2026-06-10, script deleted |
| fix-names.php deleted | ✅ | Applied via db-proxy directly, file removed |

---

## Test Results

### Page 1: Homepage (/)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200, styled | ✅ | Title: "...Calabar" |
| Hero eyebrow | "Big Qua Mall, Calabar · Est. 2024" | ✅ | |
| Gallery images | 6 African braids photos | ✅ | Pexels African hair images |
| Style cards | All 6 show African braids | ✅ | Colorful Goddess Braids, etc. |
| Testimonials | No Grace/Lagos references | ✅ | Juliet, Calabar references |
| Blog preview | 3 posts, Juliet Arah author | ✅ | Nigerian Heat, Calabar salons |
| Owner section | Juliet Arah image + Calabar text | ✅ | |
| Footer address | Big Qua Mall, Calabar | ✅ | Full address correct |
| Footer phone | +2347037256585 | ✅ | |
| WhatsApp float | Links to +2347037256585 | ✅ | wa.me/2347037256585 |
| "Made with love" | "Made with love in Calabar 🇳🇬" | ✅ | |
| No stale refs | No "Victoria Island" / "Grace Yakubu" | ✅ | |

---

### Page 2: Gallery (/gallery.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | Title: "...GYC Naturals Calabar" |
| 9 styles shown | African hair images | ✅ | Colorful Goddess Braids, Tribal Braids, etc. |
| Category filter tabs | Present | ✅ | 7 categories |
| Book link per card | Links to booking page | ✅ | |
| Moodboard button | Present per card | ✅ | |

---

### Page 3: Shop (/shop.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | Title: "...GYC Naturals Calabar" |
| 12 products shown | Product images visible | ✅ | Hair care, styling, accessories, clothing |
| Filter sidebar | Category + hair type + price | ✅ | |
| 3 bundles shown | Bundle images visible | ✅ | Starter Kit, Loc Lovers, Scalp Rescue |
| Add to Bag | Button present | ✅ | |
| No stale refs | No Lagos/Grace refs | ✅ | |

---

### Page 4: Book Appointment (/book-appointment.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | Title: "...GYC Naturals Calabar" |
| 3-step form | Style → Date → Details | ✅ | |
| 9 style options | African hair images shown | ✅ | |
| Category filter buttons | 7 categories | ✅ | |
| Date picker | Present | ✅ | |
| Contact fields | Name, phone, email, notes | ✅ | |
| 30% deposit note | Visible | ✅ | |

---

### Page 5: Cart (/cart.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | Title: "Your Bag — GYC Naturals Calabar" |
| No stale refs | Clean | ✅ | |
| Checkout redirect | Redirects to cart when empty | ✅ | Expected behaviour |

---

### Page 6: About (/about.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| Founder: Juliet Arah | Correct name | ✅ | "Founded in 2024 by Juliet Arah" |
| Location: Calabar | No "Lagos" / "Victoria Island" | ✅ | "heart of Calabar" |
| Founded: 2024 | Stats + milestones | ✅ | "Est. in Calabar" stat |
| Single founder | No 3-person team section | ✅ | "Meet the Founder" single section |
| Founder image | African woman photo (Pexels) | ✅ | |
| 5 milestones | All reference 2024 / Calabar | ✅ | |
| CTA | "Big Qua Mall, Ediba Road, Calabar" | ✅ | |

---

### Page 7: Contact (/contact.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| Google Maps | Big Qua Mall, Calabar map | ✅ | iframe present |
| "Open in Maps" link | Google Maps URL with Calabar | ✅ | |
| Phone | +2347037256585 | ✅ | |
| WhatsApp | +2347037256585 | ✅ | |
| Address | Big Qua Mall, Ediba Road, Calabar | ✅ | |
| Contact form | Name, email, subject, message | ✅ | |
| Hours | Mon–Sat 9am–7pm | ✅ | |

---

### Page 8: Blog (/blog.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| 4 posts shown | African hair images | ✅ | |
| Post 1 author | "Juliet Arah, GYC Naturals" | ✅ | |
| Post 3 title | "...Nigerian Heat" (not Lagos) | ✅ | |
| Post 4 title | "Starting Locs in Calabar" | ✅ | |
| Excerpt 1 | "Calabar salons" (not Lagos) | ✅ | |
| Newsletter form | Present | ✅ | |

---

### Page 9: Testimonials (/testimonials.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| Stats | "Trusted by 100+ Calabar clients" | ✅ | |
| "Est. in Calabar" stat | Visible | ✅ | |
| 7 testimonials | With Pexels African portrait photos | ✅ | |
| No "Grace" refs | "Juliet and her team" | ✅ | Fixed via db-proxy |
| No "Lagos" refs | "Cross River State" | ✅ | Fixed via db-proxy |
| No "Chinwe" refs | "Juliet did an..." | ✅ | Fixed via db-proxy |
| Submit form | Links to login | ✅ | |

---

### Page 10: Clothing (/clothing.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | Title: "...GYC Naturals Calabar" |
| "Made in Calabar" stat | Visible | ✅ | |
| "Same-day in Calabar" delivery | Visible | ✅ | |
| Lookbook | "pure Calabar energy" text | ✅ | |
| Product filter — clothing only | ⏳ | ❌→✅ DEPLOY PENDING | Bug fixed: was querying `product_categories` not `categories` table |

---

### Page 11: Services (/services.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| No stale refs | Clean | ✅ | |
| Calabar content | Present | ✅ | |

---

### Page 12: Auth Pages

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Login (/login.php) | Form present, no stale refs | ✅ | |
| Admin Login (/admin/login.php) | Form present, clean | ✅ | |

---

### Page 13: Quiz (/quiz.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ✅ | |
| Form present | Multi-step quiz | ✅ | |
| No stale refs | Clean | ✅ | |

---

### Page 14: Other Pages

| Page | Result | Notes |
|------|--------|-------|
| /faq.php | ✅ | Calabar present, no stale refs |
| /cart.php | ✅ | "Your Bag — GYC Naturals Calabar" |
| /checkout.php | ✅ | Redirects to cart when empty (expected) |
| /sitemap.php | ✅ DEPLOY PENDING | Fixed to query `categories` not `product_categories` |

---

## Issues Found & Fixed

| # | Issue | Root Cause | Fix Applied | Status |
|---|-------|-----------|------------|--------|
| 1 | Site not loading (morning) | SITE_URL env var missing in Vercel | Added `SITE_URL=https://gyc-naturals.vercel.app` | ✅ |
| 2 | Wrong business info in code | Placeholder "Grace Yakubu", "Victoria Island" | Updated all 20+ PHP files | ✅ |
| 3 | Old images (not African) | Unsplash/placeholder URLs | Replaced with Pexels African hair IDs | ✅ |
| 4 | No Google Maps | Contact page had placeholder div | Added iframe embed for Big Qua Mall, Calabar | ✅ |
| 5 | Grace Yakubu in blog authors | DB had old author names | Updated via db-proxy: all 4 posts → Juliet Arah | ✅ |
| 6 | "Grace and her team" in testimonial | DB testimonial text | Updated via db-proxy | ✅ |
| 7 | "Chinwe" in testimonial | DB testimonial text | Updated via db-proxy | ✅ |
| 8 | "Lagos" in Adaora testimonial | DB testimonial text | Updated via db-proxy → "Cross River State" | ✅ |
| 9 | Blog titles referencing "Lagos Heat" | DB blog_posts titles | Updated via db-proxy → "Nigerian Heat" / "Calabar" | ✅ |
| 10 | clothing.php showed all 12 products | Wrong table: `product_categories` (empty) vs `categories` | Fixed query in clothing.php | ✅ DEPLOY PENDING |
| 11 | sitemap.php using wrong table | Same wrong table name | Fixed query in sitemap.php | ✅ DEPLOY PENDING |

---

## Sign-off

- [x] All critical paths PASS
- [x] No broken images (no localhost URLs, all Pexels CDN)
- [x] All "Lagos" / "Victoria Island" / "Grace Yakubu" / "Chinwe" references removed
- [x] Google Maps shows Big Qua Mall, Calabar
- [x] Owner name: "Juliet Arah" everywhere
- [x] Phone/WhatsApp: +2347037256585 everywhere
- [x] Footer: Big Qua Mall address, "Made with love in Calabar 🇳🇬"
- [ ] Clothing page filter: pending deployment of `product_categories` → `categories` fix
