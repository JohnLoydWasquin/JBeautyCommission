<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';

// ── Validate ?id= param ─────────────────────────────────────────
$rawId   = $_GET['id'] ?? '';
$orderId = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$orderId) {
    header('Location: shop.php');
    exit;
}

// ── Fetch order details ─────────────────────────────────────────
$order      = null;
$orderItems = [];
$dbError    = null;

try {
    $pdo = getDB();

    // Fetch order header
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, shipping_address,
                total_amount, payment_method, status, created_at
           FROM orders
          WHERE id = ?'
    );
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: shop.php');
        exit;
    }

    // Fetch line items joined with product names
    $stmt = $pdo->prepare(
        'SELECT oi.quantity, oi.price,
                p.name, p.image_url
           FROM order_items oi
           JOIN products p ON p.id = oi.product_id
          WHERE oi.order_id = ?'
    );
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();

} catch (\RuntimeException $e) {
    $dbError = $e->getMessage();
}

// Helper: format payment method label
$paymentLabels = [
    'credit_card'      => 'Credit / Debit Card',
    'cash_on_delivery' => 'Cash on Delivery',
];
$paymentLabel = $paymentLabels[$order['payment_method'] ?? ''] ?? ucwords($order['payment_method'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JBeauty — Your order has been placed successfully.">
    <title>Order Confirmed - JBeauty</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
    <link rel="stylesheet" href="assets/css/order_succes.css">
</head>
<body>

<canvas id="confettiCanvas" aria-hidden="true"></canvas>
<a href="#main-content" class="skip-link">Skip to main content</a>

<header id="header" role="banner" aria-label="Main navigation">
    <div class="container">
        <nav class="nav-inner" aria-label="Primary">
            <a href="index.php" class="nav-logo" aria-label="JBeauty — go to homepage">
                <div class="nav-logo-mark" aria-hidden="true">J</div>
                <span class="nav-logo-text">JBeauty</span>
            </a>
            <ul class="nav-links" role="list">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="index.php#about">About Us</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <div class="nav-actions">
                <a href="#" class="nav-icon-btn" aria-label="View your profile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </a>
                <button class="nav-hamburger" id="hamburgerBtn" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobileMenu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </nav>
    </div>
    <nav id="mobileMenu" class="mobile-menu" aria-label="Mobile navigation" aria-hidden="true">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="index.php#about">About Us</a>
        <a href="index.php#contact">Contact</a>
    </nav>
</header>

<main class="success-section" id="main-content">

    <?php if ($dbError): ?>
    <!-- Fallback if DB is unavailable -->
    <div style="text-align:center;padding:4rem 1rem;">
        <p style="color:#C0392B;">Unable to load order details. Your order may still have been placed. Please contact support.</p>
        <a href="shop.php" class="btn-primary" style="margin-top:2rem;">← Back to Shop</a>
    </div>
    <?php else: ?>

    <!-- ── Animated checkmark ── -->
    <div class="success-icon-wrap" aria-hidden="true">
        <div class="success-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
    </div>

    <!-- ── Heading ── -->
    <p class="success-eyebrow">Order Confirmed</p>
    <h1 class="success-heading">Thank You, <em><?= htmlspecialchars(explode(' ', $order['full_name'])[0]) ?>!</em></h1>
    <p class="success-subtext">
        Your order has been placed and is now being prepared with care.
        A confirmation has been sent to <strong><?= htmlspecialchars($order['email']) ?></strong>.
    </p>

    <!-- ── Order ID ── -->
    <div class="order-id-badge" aria-label="Order number <?= (int)$order['id'] ?>">
        <span class="label">Order&nbsp;#</span>
        <span><?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></span>
        <span class="status-pill"><?= htmlspecialchars($order['status']) ?></span>
    </div>

    <!-- ── What happens next ── -->
    <div class="next-steps" role="region" aria-label="What happens next">
        <h3>What happens next?</h3>
        <ul class="steps-list">
            <li>
                <div class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span><strong style="color:var(--clr-ink);">Confirmation email</strong> — A receipt has been sent to your email address.</span>
            </li>
            <li>
                <div class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span><strong style="color:var(--clr-ink);">Order processing</strong> — We'll start preparing your items within 1–2 business days.</span>
            </li>
            <li>
                <div class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <span><strong style="color:var(--clr-ink);">Delivery</strong> — Estimated 3–7 business days depending on your location.</span>
            </li>
        </ul>
    </div>

    <!-- ── Detail cards ── -->
    <div class="detail-grid">

        <div class="detail-card" role="region" aria-label="Shipping details">
            <p class="detail-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Shipping To
            </p>
            <p>
                <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
            </p>
        </div>

        <div class="detail-card" role="region" aria-label="Payment details">
            <p class="detail-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Payment
            </p>
            <p>
                <strong><?= htmlspecialchars($paymentLabel) ?></strong><br>
                <span style="font-size:.82rem;color:var(--clr-ink-light);">
                    Placed <?= date('F j, Y · g:i A', strtotime($order['created_at'])) ?>
                </span>
            </p>
        </div>

    </div>

    <!-- ── Order items ── -->
    <?php if (!empty($orderItems)): ?>
    <div class="items-card" role="region" aria-label="Items ordered">
        <p class="items-card-title">Items in Your Order</p>

        <?php foreach ($orderItems as $item): ?>
        <div class="order-item-row">
            <img class="order-item-img"
                 src="<?= htmlspecialchars($item['image_url']) ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>"
                 loading="lazy"
                 onerror="this.src='https://placehold.co/56x56/F2EAE0/C4956A?text=✦'">
            <div>
                <p class="order-item-name"><?= htmlspecialchars($item['name']) ?></p>
                <p class="order-item-qty">Qty: <?= (int)$item['quantity'] ?></p>
            </div>
            <p class="order-item-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
        </div>
        <?php endforeach; ?>

        <div class="total-row">
            <span>Order Total</span>
            <span class="total-amount">₱<?= number_format((float)$order['total_amount'], 2) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Actions ── -->
    <div class="success-actions">
        <a href="shop.php" class="btn-primary" aria-label="Continue shopping at JBeauty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
            </svg>
            Continue Shopping
        </a>
        <a href="index.php" class="btn-secondary">
            ← Back to Home
        </a>
    </div>

    <?php endif; ?>

</main>

<footer id="contact" role="contentinfo" aria-label="Site footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="nav-logo-mark" aria-hidden="true">J</div>
                <p class="nav-logo-text">JBeauty</p>
                <p class="footer-brand-desc">Premium, inclusive cosmetics crafted for every skin tone. Beauty without compromise, luxury without exclusion.</p>
            </div>
            <div class="footer-col">
                <h4>Navigate</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Help</h4>
                <ul>
                    <li><a href="#">Track My Order</a></li>
                    <li><a href="#">Returns &amp; Exchanges</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <div class="footer-newsletter">
                    <h4>Stay in the loop</h4>
                    <p>Be the first to hear about new launches and exclusive offers.</p>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-copy">&copy; <?= date('Y') ?> JBeauty. All rights reserved.</p>
            <nav class="footer-legal" aria-label="Legal links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </nav>
        </div>
    </div>
</footer>

<script src="assets/js/home.js" defer></script>

<script>
/* ── Confetti burst on load ──────────────────────────────── */
(function () {
    const canvas = document.getElementById('confettiCanvas');
    if (!canvas) return;
    const ctx   = canvas.getContext('2d');
    const W     = canvas.width  = window.innerWidth;
    const H     = canvas.height = window.innerHeight;
    const COLORS = ['#B07D55','#F7D5A8','#E2C6A8','#8B6035','#D4A574','#F0E6D3','#2C1B0E'];

    const pieces = Array.from({ length: 110 }, () => ({
        x:  Math.random() * W,
        y:  Math.random() * -H,
        w:  6 + Math.random() * 6,
        h:  10 + Math.random() * 8,
        r:  Math.random() * Math.PI * 2,
        dr: (Math.random() - .5) * .15,
        dy: 2.5 + Math.random() * 3,
        dx: (Math.random() - .5) * 1.5,
        color: COLORS[Math.floor(Math.random() * COLORS.length)],
        opacity: .85 + Math.random() * .15,
    }));

    let frame = 0;
    function draw() {
        ctx.clearRect(0, 0, W, H);
        pieces.forEach(p => {
            ctx.save();
            ctx.globalAlpha = p.opacity;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.r);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            ctx.restore();
            p.y += p.dy;
            p.x += p.dx;
            p.r += p.dr;
            p.opacity -= .003;
        });
        frame++;
        if (frame < 220) requestAnimationFrame(draw);
        else ctx.clearRect(0, 0, W, H);
    }
    draw();
})();
</script>

</body>
</html>