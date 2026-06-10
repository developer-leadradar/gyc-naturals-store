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
| African images set | ✅ | Pexels CDN URLs |
| Google Maps embed | ✅ | Big Qua Mall, Calabar iframe |
| DB content update | ⏳ | Run /update-content.php?key=GYCupdate2024 after deploy |

---

## Test Cases

### Page 1: Homepage (/)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200, styled correctly | ⏳ | |
| CSS/JS loaded | No broken styles | ⏳ | SITE_URL fix must be deployed |
| Hero section | Shows "Big Qua Mall, Calabar · Est. 2024" | ⏳ | |
| Gallery images | Shows African braids (Pexels photos) | ⏳ | Requires DB update |
| Services section | 6 service cards with prices | ⏳ | |
| Products grid | 4 featured products visible | ⏳ | Requires DB update |
| Bundles section | Visible if bundles in DB | ⏳ | |
| Testimonials | Visible if approved in DB | ⏳ | |
| About strip | Owner photo (Pexels), Calabar text | ⏳ | |
| Blog preview | 3 posts with African images | ⏳ | Requires DB update |
| Footer | Calabar address, correct phone | ⏳ | |
| WhatsApp float | Visible, links to +2347037256585 | ⏳ | |
| Add to Cart (AJAX) | Works without page reload | ⏳ | |
| Mobile responsive | No overflow on 375px | ⏳ | |

---

### Page 2: Gallery (/gallery.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ⏳ | |
| Masonry grid | Images display correctly | ⏳ | |
| Category filter | Filters without reload | ⏳ | |
| Before/After slider | Drag reveals after image | ⏳ | |
| Bookmark button | Adds to moodboard | ⏳ | |
| Book This Style | Links to booking page | ⏳ | |

---

### Page 3: Shop (/shop.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ⏳ | |
| Products listed | 12 products visible | ⏳ | |
| AJAX filter | Works without reload | ⏳ | |
| Add to cart | Cart count increments | ⏳ | |
| Wishlist toggle | Heart fills/unfills | ⏳ | |
| Product images | Relevant African/natural hair product images | ⏳ | |

---

### Page 4: Product Detail (/product.php?slug=...)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Page loads | HTTP 200 | ⏳ | |
| Product image | Shows correctly | ⏳ | |
| Add to cart | Works | ⏳ | |
| Reviews section | Visible | ⏳ | |
| Related products | Shows at bottom | ⏳ | |

---

### Page 5: Cart (/cart.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Cart items persist | Session-based persistence | ⏳ | |
| Qty update | Updates total | ⏳ | |
| Remove item | Removes from cart | ⏳ | |
| Proceed to checkout | Links to checkout | ⏳ | |

---

### Page 6: Checkout (/checkout.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Form renders | All shipping fields | ⏳ | |
| Paystack button | Appears with correct amount | ⏳ | |
| CSRF token | Hidden field present | ⏳ | |

---

### Page 7: Book Appointment (/book-appointment.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Multi-step form | Steps 1–4 visible | ⏳ | |
| Style selector | Shows gallery styles | ⏳ | |
| Date picker | Available slots shown | ⏳ | |
| Booking confirmation | Redirects on success | ⏳ | |

---

### Page 8: Hair Quiz (/quiz.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| 6 quiz steps | Each question renders | ⏳ | |
| Progress bar | Updates per step | ⏳ | |
| Results page | Shows recommended styles | ⏳ | |

---

### Page 9: About (/about.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Founder: Juliet Arah | Correct name shown | ⏳ | |
| Location: Calabar | No "Lagos" or "Victoria Island" | ⏳ | |
| Founded: 2024 | Milestones correct | ⏳ | |
| Single owner | No "Meet Our Team" of 3 | ⏳ | |
| Founder image | Pexels African woman photo | ⏳ | |

---

### Page 10: Contact (/contact.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Google Maps | Embedded map shows Calabar | ⏳ | |
| Address: Calabar | Correct Big Qua Mall address | ⏳ | |
| Phone: +2347037256585 | Correct phone shown | ⏳ | |
| Form submission | Success message appears | ⏳ | |
| Rate limiting | 4th submission blocked | ⏳ | |

---

### Page 11: Blog (/blog.php & /blog-post.php)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Blog listing | 4+ posts shown | ⏳ | |
| Category filter | Filters posts | ⏳ | |
| Post images | African hair related images | ⏳ | |
| Single post | Renders correctly | ⏳ | |
| View counter | Increments on visit | ⏳ | |

---

### Page 12: Auth Pages

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Register (/register.php) | Form works | ⏳ | |
| Login (/login.php) | Correct credentials login | ⏳ | |
| Logout | Redirects to login | ⏳ | |
| Forgot password | Email sent (if SMTP configured) | ⏳ | |
| Customer dashboard | Shows orders/appointments | ⏳ | |

---

### Page 13: Admin Panel (/admin/)

| Test | Expected | Result | Notes |
|------|----------|--------|-------|
| Admin login | admin@gycnaturals.com / Admin@2025 | ⏳ | |
| Dashboard | Charts + recent activity | ⏳ | |
| Products | List, add, edit | ⏳ | |
| Gallery | List, add, manage | ⏳ | |
| Appointments | Calendar + list | ⏳ | |
| Settings | Site settings 6 tabs | ⏳ | |
| Settings — Address | Shows Calabar address | ⏳ | DB update required |

---

### Page 14: Other Pages

| Page | Expected | Result | Notes |
|------|----------|--------|-------|
| /services.php | Full service menu | ⏳ | |
| /testimonials.php | Reviews grid, submit form | ⏳ | |
| /moodboard.php | Saved looks board | ⏳ | |
| /faq.php | Accordion FAQ | ⏳ | |
| /privacy.php | Legal text | ⏳ | |
| /terms.php | Cross River jurisdiction | ⏳ | |
| /404.php | Custom 404 | ⏳ | |
| /sitemap.php | Valid XML sitemap | ⏳ | |

---

## Issues Found & Fixed

| Issue | Root Cause | Fix Applied | Status |
|-------|-----------|------------|--------|
| Site not loading (morning) | SITE_URL env var missing in Vercel | Added `SITE_URL=https://gyc-naturals.vercel.app` via CLI | ✅ Fixed |
| DB cold start 22–30s | 1 TLS handshake per SQL query + SELECT 1 probe | Persistent curl handle + removed probe | ✅ Fixed (7.5s cold, 3.2s warm) |
| Wrong business info | Code had placeholder "Grace Yakubu", "Victoria Island" | Updated all PHP files (about, contact, footer, index, etc.) | ✅ Fixed |
| No African images | Images used picsum.photos / localhost placeholders | Replaced with 24 Pexels African hair photo IDs | ✅ Fixed (fallbacks) |
| No Google Maps | Contact page had placeholder div | Added Google Maps iframe embed for Big Qua Mall, Calabar | ✅ Fixed |
| DB images still old | gallery_images, products, blog_posts have placeholder URLs | update-content.php script created — run once post-deploy | ⏳ Pending |

---

## Post-Deploy Action Items

1. Visit https://gyc-naturals.vercel.app/update-content.php?key=GYCupdate2024
2. Verify all items in log show ✅
3. DELETE /update-content.php (or add to .htaccess block)
4. Re-run full UAT test suite above
5. Update all ⏳ to ✅ or ❌ with findings

---

## Sign-off

- [ ] All critical paths PASS
- [ ] No broken images (no localhost URLs)
- [ ] All "Lagos" / "Victoria Island" / "Grace Yakubu" references removed from visible pages
- [ ] Google Maps shows Big Qua Mall, Calabar
- [ ] Owner name shows "Juliet Arah"
- [ ] Phone/WhatsApp shows +2347037256585
