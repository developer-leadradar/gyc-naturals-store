<?php
// GYC Naturals — Core Functions

function getDB() {
    return Database::getInstance();
}

// ═══════════════════════════════════════════════════════
// AUTHENTICATION — HMAC-signed cookie (stateless, works
// across Vercel serverless containers; file sessions are
// per-container and lost on the very next request)
// ═══════════════════════════════════════════════════════

function _authSecret(): string {
    // Use the DB proxy secret as the signing key; it's already in env
    $s = defined('DB_PROXY_SECRET') ? DB_PROXY_SECRET : '';
    return $s ?: 'gyc_hmac_fallback_2025';
}

function _setAuthCookie(array $user, int $ttl = 86400): void {
    $payload = base64_encode(json_encode([
        'uid'   => (int)$user['id'],
        'role'  => $user['role'],
        'email' => $user['email'],
        'name'  => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
        'exp'   => time() + $ttl,
    ]));
    $token  = $payload . '.' . hash_hmac('sha256', $payload, _authSecret());
    $secure = !empty(getenv('VERCEL')) || !empty(getenv('VERCEL_ENV'));
    setcookie('gyc_auth', $token, [
        'expires'  => time() + $ttl,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['gyc_auth'] = $token;  // available for the rest of this request
}

function _getAuthPayload(): ?array {
    static $cache = false;
    if ($cache !== false) return $cache;
    $token = $_COOKIE['gyc_auth'] ?? '';
    if (!$token) { $cache = null; return null; }
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) { $cache = null; return null; }
    [$payload, $sig] = $parts;
    if (!hash_equals(hash_hmac('sha256', $payload, _authSecret()), $sig)) {
        $cache = null; return null;
    }
    $data = json_decode(base64_decode($payload), true);
    if (!$data || ($data['exp'] ?? 0) < time()) { $cache = null; return null; }
    $cache = $data;
    return $data;
}

function isLoggedIn() {
    if (_getAuthPayload() !== null) return true;
    return isset($_SESSION['user_id']);          // local-dev fallback
}

function isAdmin() {
    $p = _getAuthPayload();
    if ($p) return in_array($p['role'], ['admin', 'super_admin'], true);
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    if (isAdmin()) {
        redirect(SITE_URL . '/admin/index.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function getCurrentUser() {
    $p   = _getAuthPayload();
    $uid = $p['uid'] ?? ($_SESSION['user_id'] ?? null);
    if (!$uid) return null;
    return getDB()->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$uid]);
}

function login($email, $password) {
    $db   = getDB();
    $user = $db->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
    if (!$user || !password_verify($password, $user['password'])) return false;
    _setAuthCookie($user);
    // Keep session vars for cart merge and local-dev compatibility
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
    $_SESSION['user_role']  = $user['role'];
    return true;
}

function logout() {
    $secure = !empty(getenv('VERCEL')) || !empty(getenv('VERCEL_ENV'));
    setcookie('gyc_auth', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    unset($_COOKIE['gyc_auth']);
    session_destroy();
    redirect(SITE_URL . '/');
}

function register($data) {
    $db = getDB();
    $exists = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($exists) return ['success' => false, 'message' => 'Email already registered.'];
    $data['password'] = password_hash($data['password'], PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
    $userId = $db->insert('users', $data);
    return $userId ? ['success' => true, 'user_id' => $userId] : ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

// ═══════════════════════════════════════════════════════
// SITE SETTINGS
// ═══════════════════════════════════════════════════════

function getSetting($key) {
    static $cache = null;
    if ($cache === null) {
        $db    = getDB();
        $rows  = $db->fetchAll("SELECT setting_key, setting_val FROM site_settings");
        $cache = [];
        foreach ($rows as $row) {
            $cache[$row['setting_key']] = $row['setting_val'];
        }
    }
    return $cache[$key] ?? null;
}

function updateSetting($key, $value) {
    $db = getDB();
    $exists = $db->fetchOne("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
    if ($exists) {
        return $db->update('site_settings', ['setting_val' => $value], 'setting_key = ?', [$key]);
    }
    return $db->insert('site_settings', ['setting_key' => $key, 'setting_val' => $value]);
}

function getAllSettings() {
    $db   = getDB();
    $rows = $db->fetchAll("SELECT setting_key, setting_val FROM site_settings");
    $out  = [];
    foreach ($rows as $row) {
        $out[$row['setting_key']] = $row['setting_val'];
    }
    return $out;
}

function getProverbOfTheDay() {
    $json = getSetting('proverbs');
    if (!$json) return ['text' => 'Irun jẹ ẹwa', 'translation' => 'Hair is beauty', 'language' => 'Yoruba'];
    $proverbs = json_decode($json, true);
    if (empty($proverbs)) return null;
    $index = date('z') % count($proverbs);
    return $proverbs[$index];
}

// ═══════════════════════════════════════════════════════
// GALLERY FUNCTIONS
// ═══════════════════════════════════════════════════════

function getGalleryImages($filters = [], $limit = null, $offset = 0) {
    $db     = getDB();
    $sql    = "SELECT gi.*, gc.name as category_name, gc.slug as category_slug
               FROM gallery_images gi
               LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
               WHERE gi.is_active = 1";
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= " AND gi.category_id = ?";
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['category_slug'])) {
        $sql .= " AND gc.slug = ?";
        $params[] = $filters['category_slug'];
    }
    if (!empty($filters['style_type'])) {
        $sql .= " AND gi.style_type = ?";
        $params[] = $filters['style_type'];
    }
    if (!empty($filters['featured'])) {
        $sql .= " AND gi.is_featured = 1";
    }
    if (!empty($filters['has_before'])) {
        $sql .= " AND gi.before_image_url IS NOT NULL";
    }

    $sql .= " ORDER BY gi.is_featured DESC, gi.display_order ASC, gi.created_at DESC";

    if ($limit) {
        $sql     .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
    }
    return $db->fetchAll($sql, $params);
}

function getGalleryImageBySlug($slug) {
    $db = getDB();
    return $db->fetchOne(
        "SELECT gi.*, gc.name as category_name, gc.slug as category_slug
         FROM gallery_images gi
         LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
         WHERE gi.slug = ? AND gi.is_active = 1",
        [$slug]
    );
}

function getFeaturedGalleryImages($limit = 6) {
    return getGalleryImages(['featured' => true], $limit);
}

function getAllGalleryCategories($activeOnly = true) {
    $db  = getDB();
    $sql = "SELECT * FROM gallery_categories" . ($activeOnly ? " WHERE is_active = 1" : "");
    $sql .= " ORDER BY display_order ASC";
    return $db->fetchAll($sql);
}

function countGalleryImages($filters = []) {
    $db     = getDB();
    $sql    = "SELECT COUNT(*) as total FROM gallery_images gi
               LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
               WHERE gi.is_active = 1";
    $params = [];
    if (!empty($filters['category_slug'])) {
        $sql .= " AND gc.slug = ?";
        $params[] = $filters['category_slug'];
    }
    if (!empty($filters['style_type'])) {
        $sql .= " AND gi.style_type = ?";
        $params[] = $filters['style_type'];
    }
    $result = $db->fetchOne($sql, $params);
    return $result ? (int)$result['total'] : 0;
}

// ═══════════════════════════════════════════════════════
// APPOINTMENT FUNCTIONS
// ═══════════════════════════════════════════════════════

function createAppointment($data) {
    $db = getDB();

    // Validate future date
    if (strtotime($data['requested_date']) < strtotime('today')) {
        return ['success' => false, 'message' => 'Please select a future date.'];
    }

    // Check slot availability if slot_id provided
    if (!empty($data['slot_id'])) {
        $slot = $db->fetchOne("SELECT * FROM booking_slots WHERE id = ? AND is_available = 1", [$data['slot_id']]);
        if (!$slot) {
            return ['success' => false, 'message' => 'This time slot is no longer available.'];
        }
        // Count existing bookings for this slot
        $count = $db->fetchOne(
            "SELECT COUNT(*) as c FROM appointments WHERE slot_id = ? AND status != 'cancelled'",
            [$data['slot_id']]
        );
        if ($count && $count['c'] >= $slot['max_bookings']) {
            return ['success' => false, 'message' => 'This slot is fully booked.'];
        }
    }

    // Generate appointment number
    $year   = date('Y');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $aptNum = 'APT-' . $year . '-' . $random;

    // Ensure uniqueness
    while ($db->fetchOne("SELECT id FROM appointments WHERE appointment_number = ?", [$aptNum])) {
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $aptNum = 'APT-' . $year . '-' . $random;
    }

    $insertData = [
        'appointment_number' => $aptNum,
        'customer_name'      => sanitize($data['customer_name']),
        'customer_phone'     => sanitize($data['customer_phone']),
        'customer_email'     => sanitize($data['customer_email'] ?? ''),
        'user_id'            => $data['user_id'] ?? null,
        'gallery_image_id'   => $data['gallery_image_id'] ?? null,
        'slot_id'            => $data['slot_id'] ?? null,
        'requested_date'     => $data['requested_date'],
        'requested_time'     => $data['requested_time'],
        'duration_estimate'  => $data['duration_estimate'] ?? null,
        'customer_notes'     => sanitize($data['customer_notes'] ?? ''),
        'status'             => 'pending',
    ];

    $id = $db->insert('appointments', $insertData);
    if (!$id) return ['success' => false, 'message' => 'Failed to create appointment.'];

    // Note: confirmation email is sent by the calling API endpoint (create-booking.php)
    // using the branded emailBookingConfirmation() template.
    return ['success' => true, 'appointment_number' => $aptNum, 'appointment_id' => $id];
}

function getAppointments($filters = [], $limit = 20, $offset = 0) {
    $db     = getDB();
    $sql    = "SELECT a.*, gi.title as style_name, gi.image_url as style_image
               FROM appointments a
               LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
               WHERE 1=1";
    $params = [];
    if (!empty($filters['status'])) {
        $sql .= " AND a.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['date'])) {
        $sql .= " AND a.requested_date = ?";
        $params[] = $filters['date'];
    }
    $sql     .= " ORDER BY a.requested_date ASC, a.requested_time ASC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    return $db->fetchAll($sql, $params);
}

function getAppointmentById($id) {
    $db = getDB();
    return $db->fetchOne(
        "SELECT a.*, gi.title as style_name, gi.image_url as style_image, gi.price_from, gi.price_to
         FROM appointments a
         LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
         WHERE a.id = ?",
        [$id]
    );
}

function getAppointmentByNumber($aptNum) {
    $db = getDB();
    return $db->fetchOne(
        "SELECT a.*, gi.title as style_name, gi.image_url as style_image
         FROM appointments a
         LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
         WHERE a.appointment_number = ?",
        [$aptNum]
    );
}

function updateAppointmentStatus($id, $status, $notes = '') {
    $db   = getDB();
    $data = ['status' => $status];
    if ($notes) $data['admin_notes'] = $notes;
    if ($status === 'confirmed') $data['confirmed_at'] = date('Y-m-d H:i:s');
    return $db->update('appointments', $data, 'id = ?', [$id]);
}

function getAvailableSlots($date) {
    $db = getDB();
    return $db->fetchAll(
        "SELECT bs.*,
                (SELECT COUNT(*) FROM appointments a
                 WHERE a.slot_id = bs.id AND a.status != 'cancelled') as booked_count
         FROM booking_slots bs
         WHERE bs.slot_date = ? AND bs.is_available = 1
         HAVING booked_count < bs.max_bookings
         ORDER BY bs.start_time ASC",
        [$date]
    );
}

// ═══════════════════════════════════════════════════════
// WAITLIST
// ═══════════════════════════════════════════════════════

function joinWaitlist($data) {
    $db = getDB();
    $id = $db->insert('waitlist', [
        'customer_name'    => sanitize($data['customer_name']),
        'customer_phone'   => sanitize($data['customer_phone']),
        'customer_email'   => sanitize($data['customer_email'] ?? ''),
        'preferred_date'   => $data['preferred_date'],
        'gallery_image_id' => $data['gallery_image_id'] ?? null,
    ]);
    return $id ? ['success' => true] : ['success' => false, 'message' => 'Failed to join waitlist.'];
}

function getWaitlist($date = null) {
    $db     = getDB();
    $sql    = "SELECT w.*, gi.title as style_name FROM waitlist w
               LEFT JOIN gallery_images gi ON w.gallery_image_id = gi.id
               WHERE 1=1";
    $params = [];
    if ($date) { $sql .= " AND w.preferred_date = ?"; $params[] = $date; }
    $sql .= " ORDER BY w.created_at DESC";
    return $db->fetchAll($sql, $params);
}

function notifyWaitlist($date) {
    $db = getDB();
    return $db->query("UPDATE waitlist SET notified = 1 WHERE preferred_date = ? AND notified = 0", [$date]);
}

// ═══════════════════════════════════════════════════════
// TESTIMONIALS
// ═══════════════════════════════════════════════════════

function getAllTestimonials($featuredOnly = false) {
    $db     = getDB();
    $params = [];
    if ($featuredOnly) {
        $sql = "SELECT * FROM testimonials WHERE is_approved = 1 AND is_featured = 1";
    } else {
        $sql = "SELECT * FROM testimonials WHERE is_approved = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    return $db->fetchAll($sql, $params);
}

// ═══════════════════════════════════════════════════════
// BUNDLES
// ═══════════════════════════════════════════════════════

function getAllBundles($activeOnly = true) {
    $db  = getDB();
    $sql = "SELECT * FROM bundles" . ($activeOnly ? " WHERE is_active = 1" : "");
    $sql .= " ORDER BY display_order ASC, is_featured DESC";
    return $db->fetchAll($sql);
}

function getBundleById($id) {
    $db = getDB();
    return $db->fetchOne("SELECT * FROM bundles WHERE id = ?", [$id]);
}

function getBundleBySlug($slug) {
    $db = getDB();
    return $db->fetchOne("SELECT * FROM bundles WHERE slug = ? AND is_active = 1", [$slug]);
}

function getBundleItems($bundleId) {
    $db = getDB();
    return $db->fetchAll(
        "SELECT bi.*, p.name, p.price, p.image, p.slug, p.key_ingredient, p.volume_ml
         FROM bundle_items bi
         JOIN products p ON bi.product_id = p.id
         WHERE bi.bundle_id = ?",
        [$bundleId]
    );
}

function getBundlePrice($bundleId) {
    $db      = getDB();
    $bundle  = getBundleById($bundleId);
    $items   = getBundleItems($bundleId);
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $discount = $bundle ? ($subtotal * $bundle['discount_percentage'] / 100) : 0;
    return [
        'subtotal'        => $subtotal,
        'discount'        => $discount,
        'total'           => $subtotal - $discount,
        'discount_pct'    => $bundle ? $bundle['discount_percentage'] : 0,
    ];
}

// ═══════════════════════════════════════════════════════
// BLOG
// ═══════════════════════════════════════════════════════

function getBlogPosts($limit = 6, $offset = 0, $category = null) {
    $db     = getDB();
    $sql    = "SELECT * FROM blog_posts WHERE status = 'published'";
    $params = [];
    if ($category) { $sql .= " AND category = ?"; $params[] = $category; }
    $sql     .= " ORDER BY published_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    return $db->fetchAll($sql, $params);
}

function getBlogPostBySlug($slug) {
    $db = getDB();
    return $db->fetchOne("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'", [$slug]);
}

function getBlogCategories() {
    $db = getDB();
    return $db->fetchAll("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category");
}

// ═══════════════════════════════════════════════════════
// PRODUCTS (adapted from Phelyz, hair-product fields)
// ═══════════════════════════════════════════════════════

function getAllCategories($activeOnly = true) {
    $db  = getDB();
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
    if ($activeOnly) $sql .= " AND is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $db->fetchAll($sql);
}

function getCategoryById($id) {
    return getDB()->fetchOne("SELECT * FROM categories WHERE id = ?", [$id]);
}

function getCategoryBySlug($slug) {
    return getDB()->fetchOne("SELECT * FROM categories WHERE slug = ?", [$slug]);
}

function getAllProducts($filters = [], $limit = null, $offset = 0) {
    $db     = getDB();
    $sql    = "SELECT p.*, c.name as category_name FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE p.is_active = 1";
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = ?"; $params[] = $filters['category_id'];
    }
    if (!empty($filters['hair_type'])) {
        $sql .= " AND p.hair_type LIKE ?"; $params[] = '%' . $filters['hair_type'] . '%';
    }
    if (!empty($filters['product_type'])) {
        $sql .= " AND p.product_type = ?"; $params[] = $filters['product_type'];
    }
    if (!empty($filters['concern'])) {
        $sql .= " AND p.concern LIKE ?"; $params[] = '%' . $filters['concern'] . '%';
    }
    if (!empty($filters['scent'])) {
        $sql .= " AND p.scent = ?"; $params[] = $filters['scent'];
    }
    if (!empty($filters['min_price'])) {
        $sql .= " AND p.price >= ?"; $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.price <= ?"; $params[] = $filters['max_price'];
    }
    if (!empty($filters['featured'])) {
        $sql .= " AND p.is_featured = 1";
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $t = '%' . $filters['search'] . '%';
        $params[] = $t; $params[] = $t;
    }
    if (isset($filters['clothing'])) {
        $sql .= $filters['clothing'] ? " AND p.clothing_size IS NOT NULL" : " AND p.clothing_size IS NULL";
    }

    $orderBy = "p.is_featured DESC, p.display_order ASC, p.created_at DESC";
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc':   $orderBy = "p.price ASC";  break;
            case 'price_desc':  $orderBy = "p.price DESC"; break;
            case 'rating':      $orderBy = "p.rating DESC, p.review_count DESC"; break;
            case 'newest':      $orderBy = "p.created_at DESC"; break;
        }
    }
    $sql .= " ORDER BY $orderBy";

    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
    }
    return $db->fetchAll($sql, $params);
}

function getProductById($id) {
    return getDB()->fetchOne(
        "SELECT p.*, c.name as category_name FROM products p
         LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?",
        [$id]
    );
}

function getProductBySlug($slug) {
    return getDB()->fetchOne(
        "SELECT p.*, c.name as category_name FROM products p
         LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?",
        [$slug]
    );
}

function getFeaturedProducts($limit = 4) {
    return getAllProducts(['featured' => true], $limit);
}

function getRelatedProducts($productId, $categoryId, $limit = 4) {
    $db = getDB();
    return $db->fetchAll(
        "SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1
         ORDER BY RAND() LIMIT ?",
        [$categoryId, $productId, $limit]
    );
}

function countProducts($filters = []) {
    $db = getDB();
    $sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1";
    $params = [];
    if (!empty($filters['category_id'])) { $sql .= " AND p.category_id = ?"; $params[] = $filters['category_id']; }
    if (!empty($filters['concern'])) { $sql .= " AND p.concern LIKE ?"; $params[] = '%' . $filters['concern'] . '%'; }
    $result = $db->fetchOne($sql, $params);
    return $result ? (int)$result['total'] : 0;
}

function getProductBundles($productId) {
    $db = getDB();
    return $db->fetchAll(
        "SELECT b.* FROM bundles b
         JOIN bundle_items bi ON b.id = bi.bundle_id
         WHERE bi.product_id = ? AND b.is_active = 1",
        [$productId]
    );
}

// ═══════════════════════════════════════════════════════
// CART FUNCTIONS
// ═══════════════════════════════════════════════════════

function getOrCreateCart() {
    $db = getDB();
    if (isLoggedIn()) {
        $cart = $db->fetchOne("SELECT * FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
        if (!$cart) {
            $id   = $db->insert('cart', ['user_id' => $_SESSION['user_id']]);
            $cart = ['id' => $id];
        }
    } else {
        $sid  = session_id();
        $cart = $db->fetchOne("SELECT * FROM cart WHERE session_id = ?", [$sid]);
        if (!$cart) {
            $id   = $db->insert('cart', ['session_id' => $sid]);
            $cart = ['id' => $id];
        }
    }
    return $cart;
}

function addToCart($productId, $quantity = 1, $bundleId = null) {
    $db      = getDB();
    $cart    = getOrCreateCart();
    $product = getProductById($productId);
    if (!$product) return false;
    if ($product['stock_quantity'] < $quantity) return false;

    $existing = $db->fetchOne(
        "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?",
        [$cart['id'], $productId]
    );
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) return false;
        return $db->update('cart_items', ['quantity' => $newQty], 'id = ?', [$existing['id']]);
    }
    return $db->insert('cart_items', [
        'cart_id'    => $cart['id'],
        'product_id' => $productId,
        'quantity'   => $quantity,
        'bundle_id'  => $bundleId,
    ]);
}

function getCartItems() {
    $db   = getDB();
    $cart = getOrCreateCart();
    return $db->fetchAll(
        "SELECT ci.*, p.name, p.price, p.image, p.stock_quantity, p.slug,
                b.name as bundle_name, b.slug as bundle_slug
         FROM cart_items ci
         JOIN products p ON ci.product_id = p.id
         LEFT JOIN bundles b ON ci.bundle_id = b.id
         WHERE ci.cart_id = ?",
        [$cart['id']]
    );
}

function getCartTotal() {
    $total = 0;
    foreach (getCartItems() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function getCartCount() {
    $count = 0;
    foreach (getCartItems() as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function getCartSummary() {
    $items    = getCartItems();
    $subtotal = 0;
    $itemCount = 0;
    foreach ($items as $item) {
        $subtotal  += $item['price'] * $item['quantity'];
        $itemCount += $item['quantity'];
    }
    $shipping = $subtotal >= 50000 ? 0 : 2500;
    $total    = $subtotal + $shipping;
    return compact('items', 'itemCount', 'subtotal', 'shipping', 'total');
}

function updateCartQuantity($cartItemId, $quantity) {
    if ($quantity <= 0) return removeFromCart($cartItemId);
    return getDB()->update('cart_items', ['quantity' => $quantity], 'id = ?', [$cartItemId]);
}

function removeFromCart($cartItemId) {
    return getDB()->delete('cart_items', 'id = ?', [$cartItemId]);
}

function clearCart() {
    $db   = getDB();
    $cart = getOrCreateCart();
    return $db->delete('cart_items', 'cart_id = ?', [$cart['id']]);
}

function mergeGuestCart($userId) {
    $db       = getDB();
    $sid      = session_id();
    $guestCart = $db->fetchOne("SELECT * FROM cart WHERE session_id = ?", [$sid]);
    if (!$guestCart) return;
    $userCart = $db->fetchOne("SELECT * FROM cart WHERE user_id = ?", [$userId]);
    if (!$userCart) {
        $db->update('cart', ['user_id' => $userId, 'session_id' => null], 'id = ?', [$guestCart['id']]);
    } else {
        $guestItems = $db->fetchAll("SELECT * FROM cart_items WHERE cart_id = ?", [$guestCart['id']]);
        foreach ($guestItems as $item) {
            $existing = $db->fetchOne(
                "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?",
                [$userCart['id'], $item['product_id']]
            );
            if ($existing) {
                $db->update('cart_items', ['quantity' => $existing['quantity'] + $item['quantity']], 'id = ?', [$existing['id']]);
            } else {
                $db->insert('cart_items', ['cart_id' => $userCart['id'], 'product_id' => $item['product_id'], 'quantity' => $item['quantity']]);
            }
        }
        $db->delete('cart_items', 'cart_id = ?', [$guestCart['id']]);
        $db->delete('cart', 'id = ?', [$guestCart['id']]);
    }
}

// ═══════════════════════════════════════════════════════
// ORDERS
// ═══════════════════════════════════════════════════════

function createOrder($orderData) {
    $db          = getDB();
    $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    while ($db->fetchOne("SELECT id FROM orders WHERE order_number = ?", [$orderNumber])) {
        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    $orderData['order_number'] = $orderNumber;
    $orderId = $db->insert('orders', $orderData);
    return $orderId ? ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber]
                    : ['success' => false];
}

function addOrderItems($orderId, $items) {
    $db = getDB();
    foreach ($items as $item) {
        $db->insert('order_items', [
            'order_id'          => $orderId,
            'product_id'        => $item['product_id'],
            'product_name'      => $item['name'],
            'quantity'          => $item['quantity'],
            'price_at_purchase' => $item['price'],
            'subtotal'          => $item['price'] * $item['quantity'],
        ]);
        $db->query(
            "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
            [$item['quantity'], $item['product_id']]
        );
    }
}

function getOrdersByUser($userId) {
    return getDB()->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
}

function getOrderById($id) {
    return getDB()->fetchOne("SELECT * FROM orders WHERE id = ?", [$id]);
}

function getOrderByNumber($orderNumber) {
    return getDB()->fetchOne("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
}

function getOrderItems($orderId) {
    return getDB()->fetchAll(
        "SELECT oi.*, p.image, p.slug FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?",
        [$orderId]
    );
}

function updateOrderStatus($orderId, $status) {
    return getDB()->update('orders', ['status' => $status], 'id = ?', [$orderId]);
}

// ═══════════════════════════════════════════════════════
// WISHLIST
// ═══════════════════════════════════════════════════════

function addToWishlist($productId) {
    if (!isLoggedIn()) return false;
    $db     = getDB();
    $exists = $db->fetchOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
    if ($exists) return true;
    return $db->insert('wishlist', ['user_id' => $_SESSION['user_id'], 'product_id' => $productId]);
}

function removeFromWishlist($productId) {
    if (!isLoggedIn()) return false;
    return getDB()->delete('wishlist', 'user_id = ? AND product_id = ?', [$_SESSION['user_id'], $productId]);
}

function getWishlistItems() {
    if (!isLoggedIn()) return [];
    return getDB()->fetchAll(
        "SELECT w.*, p.* FROM wishlist w JOIN products p ON w.product_id = p.id
         WHERE w.user_id = ? ORDER BY w.created_at DESC",
        [$_SESSION['user_id']]
    );
}

function isInWishlist($productId) {
    if (!isLoggedIn()) return false;
    return (bool)getDB()->fetchOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
}

// ═══════════════════════════════════════════════════════
// REVIEWS
// ═══════════════════════════════════════════════════════

function getProductReviews($productId, $limit = null) {
    $db  = getDB();
    $sql = "SELECT r.*, u.first_name, u.last_name FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC";
    if ($limit) $sql .= " LIMIT $limit";
    return $db->fetchAll($sql, [$productId]);
}

function getReviewStats($productId) {
    $db    = getDB();
    $stats = $db->fetchOne("SELECT COALESCE(AVG(rating),0) as average, COUNT(*) as total FROM reviews WHERE product_id = ? AND is_approved = 1", [$productId]);
    $breakdown = [];
    for ($i = 1; $i <= 5; $i++) {
        $c = $db->fetchOne("SELECT COUNT(*) as count FROM reviews WHERE product_id = ? AND rating = ? AND is_approved = 1", [$productId, $i]);
        $breakdown[$i] = $c ? (int)$c['count'] : 0;
    }
    return ['average' => (float)$stats['average'], 'total' => (int)$stats['total'], 'breakdown' => $breakdown];
}

function updateProductRating($productId) {
    $db    = getDB();
    $stats = $db->fetchOne("SELECT COALESCE(AVG(rating),0) as average, COUNT(*) as count FROM reviews WHERE product_id = ?", [$productId]);
    $db->update('products', ['rating' => round($stats['average'], 2), 'review_count' => $stats['count']], 'id = ?', [$productId]);
}

// ═══════════════════════════════════════════════════════
// PAYSTACK
// ═══════════════════════════════════════════════════════

function generatePaystackLink($email, $amountNgn, $reference, $callbackUrl = null) {
    $callbackUrl = $callbackUrl ?: PAYSTACK_CALLBACK_URL;
    $payload     = [
        'email'        => $email,
        'amount'       => (int)($amountNgn * 100),
        'reference'    => $reference,
        'callback_url' => $callbackUrl,
        'currency'     => 'NGN',
    ];
    $ch = curl_init('https://api.paystack.co/transaction/initialize');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return ($response && $response['status']) ? $response['data']['authorization_url'] : false;
}

function verifyPaystackTransaction($reference) {
    $ch = curl_init('https://api.paystack.co/transaction/verify/' . urlencode($reference));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return ($response && $response['status'] && $response['data']['status'] === 'success') ? $response['data'] : false;
}

// ═══════════════════════════════════════════════════════
// WHATSAPP
// ═══════════════════════════════════════════════════════

function whatsappMessage($phone, $message) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
}

function getWhatsAppFloat() {
    $phone   = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
    $message = "Hello GYC Naturals! I'd like to enquire about your hair braiding services.";
    return whatsappMessage($phone, $message);
}

// ═══════════════════════════════════════════════════════
// IMAGE / UPLOAD
// ═══════════════════════════════════════════════════════

function uploadGalleryImage($file, $subdir = 'gallery') {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed) || $file['error'] !== UPLOAD_ERR_OK) return false;

    $filename = $subdir . '/' . uniqid() . '.' . $ext;

    if (!empty(SUPABASE_URL) && !empty(SUPABASE_SERVICE_KEY)) {
        $content = file_get_contents($file['tmp_name']);
        $mimes   = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $mime    = $mimes[$ext] ?? 'application/octet-stream';
        $url     = SUPABASE_URL . '/storage/v1/object/' . SUPABASE_BUCKET . '/' . $filename;
        $ch      = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
                'apikey: ' . SUPABASE_SERVICE_KEY,
                'Content-Type: ' . $mime,
                'x-upsert: true',
            ],
            CURLOPT_POSTFIELDS     => $content,
        ]);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 || $code === 201) {
            return SUPABASE_URL . '/storage/v1/object/public/' . SUPABASE_BUCKET . '/' . $filename;
        }
    }

    // Local fallback with optional WebP conversion
    $uploadDir = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (function_exists('imagewebp') && $ext !== 'webp') {
        $webpName = $subdir . '/' . uniqid() . '.webp';
        $webpPath = UPLOAD_PATH . $webpName;
        $img = null;
        if ($ext === 'jpg' || $ext === 'jpeg') $img = @imagecreatefromjpeg($file['tmp_name']);
        elseif ($ext === 'png')                $img = @imagecreatefrompng($file['tmp_name']);
        if ($img) {
            imagewebp($img, $webpPath, 85);
            imagedestroy($img);
            return 'uploads/' . $webpName;
        }
    }

    $destPath = UPLOAD_PATH . $filename;
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return 'uploads/' . $filename;
    }
    return false;
}

function generateBlurHash($imagePath) {
    if (!function_exists('imagecreatefromjpeg')) return null;
    $fullPath = (strpos($imagePath, 'http') === 0) ? $imagePath : (UPLOAD_PATH . str_replace('uploads/', '', $imagePath));
    $ext      = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $img      = null;
    if ($ext === 'jpg' || $ext === 'jpeg') $img = @imagecreatefromjpeg($fullPath);
    elseif ($ext === 'png')               $img = @imagecreatefrompng($fullPath);
    elseif ($ext === 'webp')              $img = @imagecreatefromwebp($fullPath);
    if (!$img) return null;
    $thumb = imagescale($img, 20, 0);
    imagedestroy($img);
    ob_start();
    imagejpeg($thumb, null, 10);
    $data = ob_get_clean();
    imagedestroy($thumb);
    return 'data:image/jpeg;base64,' . base64_encode($data);
}

// ═══════════════════════════════════════════════════════
// EMAIL
// ═══════════════════════════════════════════════════════

function sendEmail($to, $subject, $html) {
    if (!empty(RESEND_API_KEY)) {
        $payload = [
            'from'    => SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
            'text'    => strip_tags($html),
        ];
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . RESEND_API_KEY, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 || $code === 201) return true;
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? 'ssl' : 'tls';
            $mail->Port       = SMTP_PORT;
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer: ' . $mail->ErrorInfo);
        }
    }

    $headers = "From: " . SMTP_FROM_EMAIL . "\r\nContent-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $html, $headers);
}

// ═══════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════

function redirect($url) {
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    if (ob_get_level()) ob_end_clean();
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return '₦' . number_format((float)$price, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($dt) {
    return date('M d, Y \a\t g:ia', strtotime($dt));
}

function generateSlug($text) {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function readTime($body) {
    $words   = str_word_count(strip_tags($body));
    $minutes = max(1, round($words / 200));
    return $minutes . ' min read';
}

function getStatusBadge($status) {
    $map = [
        'pending'    => ['warning', 'Pending'],
        'processing' => ['info',    'Processing'],
        'shipped'    => ['primary', 'Shipped'],
        'delivered'  => ['success', 'Delivered'],
        'cancelled'  => ['danger',  'Cancelled'],
        'refunded'   => ['secondary','Refunded'],
        'confirmed'  => ['success', 'Confirmed'],
        'completed'  => ['success', 'Completed'],
        'paid'       => ['success', 'Paid'],
        'failed'     => ['danger',  'Failed'],
    ];
    [$cls, $label] = $map[$status] ?? ['secondary', ucfirst($status)];
    return "<span class=\"badge badge-$cls\">$label</span>";
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function pagination($total, $perPage, $currentPage, $url) {
    $totalPages = max(1, ceil($total / $perPage));
    if ($totalPages <= 1) return '';
    $html = '<ul class="pagination">';
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $url . '&page=' . ($currentPage - 1) . '">← Prev</a></li>';
    }
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $active = $i == $currentPage ? ' class="active"' : '';
        $html .= "<li$active><a href=\"$url&page=$i\">$i</a></li>";
    }
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $url . '&page=' . ($currentPage + 1) . '">Next →</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

function isJsonRequest() {
    return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

// ── CSRF — Double Submit Cookie pattern (stateless, no session required) ──────
// A random token is stored in a non-HttpOnly cookie (gyc_csrf) AND embedded
// in every form as a hidden field.  On submit, both values must match.
// SameSite=Lax on the auth cookie already blocks most CSRF; this adds a
// second layer that works even when SameSite is not honoured.

function csrfToken(): string {
    if (empty($_COOKIE['gyc_csrf'])) {
        $token  = bin2hex(random_bytes(32));
        $secure = !empty(getenv('VERCEL')) || !empty(getenv('VERCEL_ENV'));
        setcookie('gyc_csrf', $token, [
            'expires'  => time() + 7200,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,   // readable by JS for AJAX forms
            'samesite' => 'Lax',
        ]);
        $_COOKIE['gyc_csrf'] = $token;
    }
    return $_COOKIE['gyc_csrf'];
}

function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf($token = null): void {
    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    $cookie    = $_COOKIE['gyc_csrf'] ?? '';
    // Accept if cookie and form field both present and equal
    if ($cookie && $submitted && hash_equals($cookie, $submitted)) return;
    // Fallback: session-based check (local dev / same-container)
    if (!empty($_SESSION['csrf_token']) && $submitted && hash_equals($_SESSION['csrf_token'], $submitted)) return;
    // Failed
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ' . SITE_URL . '/');
    }
    exit;
}

function verifyCsrfSilent(): bool {
    $t      = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $cookie = $_COOKIE['gyc_csrf'] ?? '';
    if ($cookie && $t && hash_equals($cookie, $t)) return true;
    return !empty($_SESSION['csrf_token']) && $t && hash_equals($_SESSION['csrf_token'], $t);
}
