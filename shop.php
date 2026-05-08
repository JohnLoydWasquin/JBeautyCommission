<?php

declare(strict_types=1);

// Start session early (cart lives in $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';

// ── Fetch all products ──────────────────────────────────────────
$products   = [];
$dbError    = null;
$categories = ['All', 'Face', 'Eyes', 'Lips', 'Skincare'];

// Read ?category= URL param so JS can auto-filter on page load
$activeCategory = in_array($_GET['category'] ?? '', $categories, true)
    ? $_GET['category']
    : 'All';

try {
    $pdo  = getDB();
    $stmt = $pdo->query(
        'SELECT id, name, description, price, image_url, category
           FROM products
          ORDER BY category, name'
    );
    $products = $stmt->fetchAll();
} catch (RuntimeException $e) {
    $dbError = $e->getMessage();
}

// ── Cart helpers ────────────────────────────────────────────────
$cart      = $_SESSION['cart'] ?? [];
$cartCount = array_sum(array_column($cart, 'qty'));

// Build enriched cart rows (join session data with DB records)
$cartItems = [];
if (!empty($cart) && empty($dbError)) {
    try {
        $pdo  = getDB();
        $ids  = array_keys($cart);
        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT id, name, price, image_url FROM products WHERE id IN ($ph)"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $row) {
            $row['qty']      = $cart[$row['id']]['qty'] ?? 1;
            $row['subtotal'] = $row['price'] * $row['qty'];
            $cartItems[]     = $row;
        }
    } catch (RuntimeException $e) {
        // Cart rendering falls back gracefully
    }
}

$cartTotal = array_sum(array_column($cartItems, 'subtotal'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JBeauty Shop — Browse our curated collection of premium, inclusive cosmetics.">
    <title>Shop · JBeauty</title>

    <!-- Same Google Fonts as index.php -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Global design system (colors, reset, nav, footer, buttons) -->
    <link rel="stylesheet" href="assets/css/home.css">

    <!-- Shop-specific styles (grid, cards, filter bar, cart panel) -->
    <link rel="stylesheet" href="assets/css/shop.css">

    <link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">

    <style>
    /* ── Add to Cart button ──────────────────────────────────── */
    .btn-add-cart,
    .btn-add-cart:link,
    .btn-add-cart:visited {
        display:          flex                          !important;
        align-items:      center                       !important;
        gap:              0.5rem                       !important;
        background-color: #2C1B0E                     !important;
        color:            #FFFFFF                      !important;
        opacity:          1                            !important;
        visibility:       visible                      !important;
        border:           none                         !important;
        border-radius:    50px                         !important;
        padding:          0.65rem 1.3rem               !important;
        min-height:       44px                         !important;
        font-family:      'DM Sans', system-ui, sans-serif !important;
        font-size:        0.78rem                      !important;
        letter-spacing:   0.06em                       !important;
        text-transform:   uppercase                    !important;
        text-decoration:  none                         !important;
        cursor:           pointer                      !important;
        white-space:      nowrap                       !important;
        transition:       background-color 0.25s ease,
                          transform        0.25s ease,
                          box-shadow       0.25s ease  !important;
    }

    .btn-add-cart:hover,
    .btn-add-cart:focus {
        background-color: #9D6B42                              !important;
        color:            #FFFFFF                              !important;
        opacity:          1                                    !important;
        visibility:       visible                              !important;
        transform:        translateY(-2px)                     !important;
        box-shadow:       0 6px 20px rgba(157, 107, 66, 0.40) !important;
        text-decoration:  none                                 !important;
    }

    .btn-add-cart:active {
        background-color: #7A5230         !important;
        color:            #FFFFFF         !important;
        transform:        translateY(0)   !important;
        box-shadow:       none            !important;
    }

    .btn-add-cart svg,
    .btn-add-cart:hover svg {
        stroke:  #FFFFFF !important;
        color:   #FFFFFF !important;
        fill:    none    !important;
        opacity: 1       !important;
    }

    .btn-add-cart.loading {
        opacity:        0.5  !important;
        pointer-events: none !important;
        cursor:         wait !important;
    }

    /* ── Checkout button ─────────────────────────────────────── */
    .btn-checkout,
    .btn-checkout:link,
    .btn-checkout:visited {
        display:          block                       !important;
        width:            100%                        !important;
        background-color: #B07D55                     !important; /* --clr-accent */
        color:            #FFFFFF                     !important;
        opacity:          1                           !important;
        visibility:       visible                     !important;
        text-decoration:  none                        !important;
        text-align:       center                      !important;
        text-transform:   uppercase                   !important;
        letter-spacing:   0.1em                       !important;
        font-family:      'DM Sans', system-ui, sans-serif !important;
        font-size:        0.88rem                     !important;
        border:           none                        !important;
        border-radius:    var(--radius-md)            !important;
        min-height:       52px                        !important;
        line-height:      52px                        !important;
        padding:          0 1.5rem                    !important;
        cursor:           pointer                     !important;
        transition:       background-color 0.25s ease,
                          transform        0.25s ease !important;
    }

    .btn-checkout:hover,
    .btn-checkout:focus {
        background-color: #8B6035                              !important; /* --clr-accent-dk */
        color:            #FFFFFF                              !important;
        opacity:          1                                    !important;
        visibility:       visible                              !important;
        transform:        translateY(-1px)                     !important;
        text-decoration:  none                                 !important;
        box-shadow:       0 6px 20px rgba(139, 96, 53, 0.35)  !important;
    }

    .btn-checkout:active {
        background-color: #7A5230       !important;
        color:            #FFFFFF       !important;
        transform:        translateY(0) !important;
        box-shadow:       none          !important;
    }

    /* Disabled state when cart is empty */
    .btn-checkout[aria-disabled="true"] {
        background-color: #D9C8B4       !important; /* --clr-border */
        color:            #7A6050       !important; /* --clr-text-light */
        pointer-events:   none          !important;
        transform:        none          !important;
        box-shadow:       none          !important;
        cursor:           default       !important;
    }
</style>
</head>
<body>

<a href="#main-content" class="skip-link">Skip to main content</a>

<header id="header" role="banner" aria-label="Main navigation">
    <div class="container">
        <nav class="nav-inner" aria-label="Primary">

            <!-- Logo -->
            <a href="index.php" class="nav-logo" aria-label="JBeauty — go to homepage">
                <div class="nav-logo-mark" aria-hidden="true">J</div>
                <span class="nav-logo-text">JBeauty</span>
            </a>

            <!-- Desktop nav links -->
            <ul class="nav-links" role="list">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php" aria-current="page" class="nav-link--active">Shop</a></li>
                <li><a href="index.php#about">About Us</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>

            <!-- Nav actions: profile · cart trigger · hamburger -->
            <div class="nav-actions">

                <!-- Profile icon (same as index.php) -->
                <?php $profileRoute = isset($_SESSION['user_id']) ? 'profile.php' : 'auth/login.php'; ?>
                    <a href="<?= $profileRoute ?>" class="nav-icon-btn" aria-label="View your profile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </a>

                <button
                    class="nav-icon-btn"
                    id="cartTrigger"
                    aria-controls="cartPanel"
                    aria-expanded="false"
                    aria-label="Open shopping cart, <?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?>">

                    <!-- Badge (hidden via CSS when count = 0) -->
                    <span
                        class="cart-badge"
                        id="cartBadge"
                        aria-live="polite"
                        data-count="<?= $cartCount ?>">
                        <?= $cartCount ?: '' ?>
                    </span>

                    <!-- Shopping bag icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                </button>

                <!-- Hamburger (same markup as index.php) -->
                <button
                    class="nav-hamburger"
                    id="hamburgerBtn"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                    aria-controls="mobileMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

            </div><!-- /nav-actions -->
        </nav>
    </div>

    <!-- Mobile menu (same structure as index.php) -->
    <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile navigation" aria-hidden="true">
    <a href="index.php">Home</a>
    <a href="shop.php" aria-current="page">Shop</a>
    <a href="index.php#about">About Us</a>
    <a href="index.php#contact">Contact</a>

    <!-- Divider -->
    <hr style="border:none; border-top:1px solid rgba(44,27,14,.12); margin:.5rem 1.2rem;">

    <!-- Dynamic account links -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php">My Profile</a>
        <a href="auth/logout.php" style="color:#C0392B;">Log Out</a>
    <?php else: ?>
        <a href="auth/login.php">Log In / Register</a>
    <?php endif; ?>
    </nav>
</header>

<section class="shop-hero" aria-labelledby="shopHeading">
    <p class="eyebrow">Curated Collection</p>
    <h1 id="shopHeading">Discover Your <em>Ritual</em></h1>
    <p>Premium cosmetics crafted with intention — for every skin tone, every story.</p>
</section>

<main class="catalogue" id="main-content">

    <!-- Database Error State -->
    <?php if ($dbError): ?>
    <div class="db-error" role="alert">
        <strong>Unable to load products.</strong>
        <p>Please check your database connection and try again.</p>
    </div>

    <?php else: ?>

    <!-- ── Filter Bar ─────────────────────────────────────────── -->
    <div class="filter-bar" role="navigation" aria-label="Filter products by category">
        <span class="filter-label">Filter:</span>

        <?php foreach ($categories as $cat): ?>
        <button
            class="filter-btn <?= $cat === $activeCategory ? 'active' : '' ?>"
            data-filter="<?= htmlspecialchars($cat) ?>"
            aria-pressed="<?= $cat === $activeCategory ? 'true' : 'false' ?>"
            aria-label="Show <?= htmlspecialchars($cat) ?> products">
            <?= htmlspecialchars($cat) ?>
        </button>
        <?php endforeach; ?>

        <span class="result-count" id="resultCount" aria-live="polite">
            <?= count($products) ?> products
        </span>
    </div>

    <!-- ── Product Grid ───────────────────────────────────────── -->
    <div
        class="product-grid"
        id="productGrid"
        role="list"
        data-active-category="<?= htmlspecialchars($activeCategory) ?>">

        <?php if (empty($products)): ?>
        <div class="grid-empty">
            <span aria-hidden="true">✦</span>
            <p>No products found. Add some via the database to get started.</p>
        </div>

        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <article
                class="product-card"
                data-category="<?= htmlspecialchars($product['category']) ?>"
                role="listitem">

                <!-- Product image -->
                <div class="card-image-wrap">
                    <img
                        src="<?= htmlspecialchars($product['image_url']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/800x600/F2EAE0/C4956A?text=%E2%9C%A6'">
                    <span class="card-category-badge" aria-label="Category: <?= htmlspecialchars($product['category']) ?>">
                        <?= htmlspecialchars($product['category']) ?>
                    </span>
                </div>

                <!-- Product info -->
                <div class="card-body">
                    <h2 class="card-name"><?= htmlspecialchars($product['name']) ?></h2>
                    <p class="card-description"><?= htmlspecialchars($product['description']) ?></p>

                    <div class="card-footer">
                        <p class="card-price" aria-label="Price: $<?= number_format((float)$product['price'], 2) ?>">
                            <sup>₱</sup><?= number_format((float)$product['price'], 2) ?>
                        </p>

                        <button
                            class="btn-add-cart"
                            data-product-id="<?= (int)$product['id'] ?>"
                            data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                            aria-label="Add <?= htmlspecialchars($product['name'], ENT_QUOTES) ?> to cart">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>

            <!-- Empty filtered state (shown by JS when no cards match) -->
            <div class="grid-empty" id="noResults" hidden>
                <span aria-hidden="true">✦</span>
                <p>No products in this category yet.</p>
            </div>

        <?php endif; ?>
    </div><!-- /product-grid -->

    <?php endif; ?>
</main>

<!-- Overlay -->
<div class="cart-overlay" id="cartOverlay" aria-hidden="true"></div>

<!-- Panel -->
<aside
    class="cart-panel"
    id="cartPanel"
    role="dialog"
    aria-modal="true"
    aria-label="Shopping cart"
    aria-hidden="true">

    <!-- Panel Header -->
    <div class="cart-header">
        <h2>
            Your Cart
            <span class="item-count" id="cartItemCountLabel">
                <?php
                    $total = array_sum(array_column($cartItems, 'qty'));
                    echo $total > 0 ? "({$total} item" . ($total > 1 ? 's' : '') . ')' : '';
                ?>
            </span>
        </h2>
        <button class="cart-close" id="cartClose" aria-label="Close shopping cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <!-- Cart Items -->
    <div class="cart-items" id="cartItemsList" aria-live="polite" aria-label="Cart items">
        <?php if (empty($cartItems)): ?>
        <div class="cart-empty" id="cartEmptyState">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            <p>Your cart is empty.<br>Start adding products above.</p>
        </div>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
            <div class="cart-item" data-id="<?= (int)$item['id'] ?>">
                <img
                    class="cart-item-img"
                    src="<?= htmlspecialchars($item['image_url']) ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                    onerror="this.src='https://placehold.co/72x72/F7F0E8/C4956A?text=✦'">
                <div class="cart-item-info">
                    <p class="cart-item-name"><?= htmlspecialchars($item['name']) ?></p>
                    <div class="qty-controls">
                        <button class="qty-btn" data-action="decrease" data-id="<?= (int)$item['id'] ?>" aria-label="Decrease quantity">−</button>
                        <span class="qty-value" aria-label="Quantity"><?= (int)$item['qty'] ?></span>
                        <button class="qty-btn" data-action="increase" data-id="<?= (int)$item['id'] ?>" aria-label="Increase quantity">+</button>
                    </div>
                </div>
                <p class="cart-item-price">₱<?= number_format($item['subtotal'], 2) ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Cart Footer -->
    <div class="cart-footer">
        <div class="cart-subtotal">
            <span class="label">Subtotal</span>
            <span class="amount" id="cartTotal">₱<?= number_format($cartTotal, 2) ?></span>
        </div>
        <p class="cart-note">Taxes &amp; shipping calculated at checkout</p>
        <a
            href="checkout.php"
            class="btn-checkout"
            id="checkoutBtn"
            <?= empty($cartItems) ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
            Proceed to Checkout →
        </a>
        <button class="continue-shopping" id="continueShopping">
            ← Continue Shopping
        </button>
    </div>
</aside>

<!-- Toast notifications -->
<div class="toast-container" id="toastContainer" aria-live="assertive" aria-atomic="true"></div>

<footer id="contact" role="contentinfo" aria-label="Site footer">
    <div class="container">

        <div class="footer-top">

            <!-- Brand column -->
            <div class="footer-brand">
                <div class="nav-logo-mark" aria-hidden="true">J</div>
                <p class="nav-logo-text">JBeauty</p>
                <p class="footer-brand-desc">
                    Premium, inclusive cosmetics crafted for every skin tone.
                    Beauty without compromise, luxury without exclusion.
                </p>
                <nav aria-label="Social media links">
                    <div class="footer-social">
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <circle cx="12" cy="12" r="4"/>
                                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
                            </svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on TikTok">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>
                            </svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on Pinterest">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.03-2.83.19-.77 1.27-5.38 1.27-5.38s-.32-.65-.32-1.61c0-1.51.88-2.64 1.97-2.64.93 0 1.38.7 1.38 1.53 0 .93-.6 2.34-.9 3.63-.26 1.09.53 1.97 1.6 1.97 1.91 0 3.19-2.46 3.19-5.37 0-2.21-1.48-3.87-4.16-3.87-3.03 0-4.92 2.26-4.92 4.78 0 .87.26 1.47.66 1.94.18.22.21.3.14.55-.05.18-.16.61-.2.78-.06.25-.26.34-.47.25-1.32-.54-1.94-2-1.94-3.63 0-2.69 2.27-5.92 6.77-5.92 3.62 0 5.99 2.63 5.99 5.46 0 3.74-2.07 6.54-5.12 6.54-1.02 0-1.98-.55-2.31-1.18l-.64 2.49c-.23.88-.85 1.97-1.27 2.64.96.29 1.97.45 3.01.45 5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                            </svg>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Navigate column -->
            <div class="footer-col">
                <h4>Navigate</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>

            <!-- Help column -->
            <div class="footer-col">
                <h4>Help</h4>
                <ul>
                    <li><a href="#">Shade Finder</a></li>
                    <li><a href="#">Track My Order</a></li>
                    <li><a href="#">Returns &amp; Exchanges</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <!-- Newsletter column -->
            <div class="footer-col">
                <div class="footer-newsletter">
                    <h4>Stay in the loop</h4>
                    <p>Be the first to hear about new launches, exclusive offers &amp; beauty tips.</p>
                    <form class="newsletter-form" onsubmit="handleNewsletter(event)" aria-label="Email newsletter signup">
                        <label for="newsletterEmail" class="sr-only">Email address</label>
                        <input
                            type="email"
                            id="newsletterEmail"
                            class="newsletter-input"
                            placeholder="your@email.com"
                            required
                            aria-required="true"
                            autocomplete="email">
                        <button type="submit" class="newsletter-btn" aria-label="Subscribe to newsletter">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /footer-top -->

        <div class="footer-bottom">
            <p class="footer-copy">
                &copy; <span id="year"><?= date('Y') ?></span> JBeauty. All rights reserved. Made with love &amp; purpose.
            </p>
            <nav class="footer-legal" aria-label="Legal links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </nav>
        </div>

    </div><!-- /container -->
</footer>

<!-- home.js handles: hamburger menu, reveal animations, scroll header -->
<script src="assets/js/home.js" defer></script>

<!-- cart.js handles: filter buttons, add-to-cart, slide panel, toasts -->
<script src="assets/js/cart.js" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const activeCategory = <?= json_encode($activeCategory) ?>;
        if (activeCategory && activeCategory !== 'All') {
            const btn = document.querySelector(
                `.filter-btn[data-filter="${CSS.escape(activeCategory)}"]`
            );
            btn?.click();
        }
    });
</script>

</body>
</html>