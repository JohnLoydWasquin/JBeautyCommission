<?php
declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';

// ── Auth guard — redirect to login if not authenticated ──────────
if (empty($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=profile.php');
    exit;
}

$userId  = (int) $_SESSION['user_id'];
$user    = null;
$orders  = [];
$dbError = null;

try {
    $pdo = getDB();
    
    // ── Fetch user record ────────────────────────────────────────
    $stmt = $pdo->prepare(
    'SELECT id, CONCAT(first_name, " ", last_name) AS full_name,
            email, phone, kyc_status, created_at
       FROM users
      WHERE id = ?
      LIMIT 1'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        // Session references a deleted account — destroy and redirect
        session_destroy();
        header('Location: auth/login.php');
        exit;
    }

    // ── Fetch order history with line items ─────────────────────
    $stmt = $pdo->prepare(
    'SELECT o.id, o.total_amount, o.payment_method, o.status, o.created_at,
            oi.quantity, oi.price,
            p.name  AS product_name,
            p.image_url
       FROM orders o
       JOIN order_items oi ON oi.order_id = o.id
       JOIN products    p  ON p.id = oi.product_id
      WHERE o.user_id = ?
      ORDER BY o.created_at DESC, o.id DESC'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    // ── Group line items under their parent order ────────────────
    foreach ($rows as $row) {
        $oid = $row['id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'id'             => $oid,
                'total_amount'   => $row['total_amount'],
                'payment_method' => $row['payment_method'],
                'status'         => $row['status'],
                'created_at'     => $row['created_at'],
                'items'          => [],
            ];
        }
        $orders[$oid]['items'][] = [
            'name'      => $row['product_name'],
            'image_url' => $row['image_url'],
            'quantity'  => $row['quantity'],
            'price'     => $row['price'],
        ];
    }

} catch (\RuntimeException $e) {
    $dbError = $e->getMessage();
}

// ── Helpers ──────────────────────────────────────────────────────
$orderCount = count($orders);

// Safe display name — falls back to 'Member' if full_name is null or empty
$displayName = !empty($user['full_name']) ? $user['full_name'] : 'Member';

// Initials derived from $displayName so it is always a valid string
$parts    = explode(' ', trim($displayName));
$initials = strtoupper(
    substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '')
);

$paymentLabels = [
    'credit_card'      => 'Credit / Debit Card',
    'cash_on_delivery' => 'Cash on Delivery',
];

// KYC — single source variable eliminates double null-coalescing
$kycStatus = $user['kyc_status'] ?? '';
$kycClass  = match ($kycStatus) {
    'Verified' => 'badge-verified',
    'Pending'  => 'badge-pending',
    default    => 'badge-unverified',
};
$kycLabel = $kycStatus !== '' ? $kycStatus : 'Not Submitted';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JBeauty — Your account overview, personal details, and order history.">
    <title>My Profile - JBeauty</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
</head>
<body>

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
                <!-- Profile icon → profile.php when logged in -->
                <a href="profile.php" class="nav-icon-btn" aria-label="View your profile" aria-current="page">
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
        <a href="profile.php" aria-current="page">My Profile</a>
        <a href="auth/logout.php" style="color:#C0392B;">Log Out</a>
    </nav>
</header>

<section class="profile-hero" aria-label="Profile overview">
    <div class="profile-hero-inner">

        <!-- Avatar (initials-based, no external image dependency) -->
        <div class="profile-avatar-wrap" aria-hidden="true">
            <div class="profile-avatar"><?= htmlspecialchars($initials) ?></div>
        </div>

        <!-- Name + email -->
        <div class="profile-hero-text">
            <h1 class="profile-hero-name"><?= htmlspecialchars($displayName) ?></h1>
            <p class="profile-hero-email"><?= htmlspecialchars($user['email']) ?></p>
        </div>

    </div>
</section>

<div class="profile-tabs-bar" role="tablist" aria-label="Profile sections">
    <div class="profile-tabs-inner">

        <button class="tab-btn active"
                id="tab-account"
                role="tab"
                aria-selected="true"
                aria-controls="panel-account">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/>
            </svg>
            Account Details
        </button>

        <button class="tab-btn"
                id="tab-orders"
                role="tab"
                aria-selected="false"
                aria-controls="panel-orders">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/>
            </svg>
            Order History
            <?php if ($orderCount > 0): ?>
            <span class="tab-count" aria-label="<?= $orderCount ?> orders"><?= $orderCount ?></span>
            <?php endif; ?>
        </button>

    </div>
</div>

<main class="profile-body" id="main-content">

    <?php if ($dbError): ?>
    <div style="padding:2rem;background:#FDECEA;border-radius:12px;color:#C0392B;font-size:.9rem;" role="alert">
        <strong>Database error:</strong> <?= htmlspecialchars($dbError) ?>
    </div>
    <?php endif; ?>

    <div id="panel-account" class="tab-panel active" role="tabpanel" aria-labelledby="tab-account">

        <div class="account-grid">

            <!-- Main info column -->
            <div class="account-main">

                <!-- Personal Information card -->
                <div class="profile-card">
                    <h2 class="card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                        Personal Information
                    </h2>
                    <dl class="info-list">
                        <div class="info-row">
                            <dt class="info-label">Full Name</dt>
                            <dd class="info-value"><?= htmlspecialchars($displayName) ?></dd>
                        </div>
                        <div class="info-row">
                            <dt class="info-label">Email Address</dt>
                            <dd class="info-value"><?= htmlspecialchars($user['email']) ?></dd>
                        </div>
                        <div class="info-row">
                            <dt class="info-label">Phone Number</dt>
                            <dd class="info-value <?= empty($user['phone']) ? 'empty' : '' ?>">
                                <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided' ?>
                            </dd>
                        </div>
                        <div class="info-row">
                            <dt class="info-label">KYC Status</dt>
                            <dd class="info-value">
                                <span class="status-badge <?= $kycClass ?>">
                                    <?php if ($kycLabel === 'Verified'): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php elseif ($kycLabel === 'Pending'): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($kycLabel) ?>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div><!-- /profile-card -->

                <!-- Account Security card -->
                <div class="profile-card">
                    <h2 class="card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        Account Security
                    </h2>
                    <dl class="info-list">
                        <div class="info-row">
                            <dt class="info-label">Password</dt>
                            <dd class="info-value">
                                <span style="letter-spacing:.18em;color:var(--clr-ink-light);">••••••••••</span>
                            </dd>
                        </div>
                        <div class="info-row">
                            <dt class="info-label">Member Since</dt>
                            <dd class="info-value">
                                <?= date('F j, Y', strtotime($user['created_at'])) ?>
                            </dd>
                        </div>
                    </dl>
                </div><!-- /profile-card -->

            </div><!-- /account-main -->

            <!-- Sidebar -->
            <div class="quick-actions">

                <!-- Quick actions card -->
                <div class="profile-card">
                    <h2 class="card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Quick Actions
                    </h2>

                    <nav class="quick-action-list" aria-label="Account actions">
                        <a href="shop.php" class="quick-action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                            </svg>
                            Browse the Shop
                        </a>
                        <button class="quick-action-btn"
                                onclick="document.getElementById('tab-orders').click()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/>
                            </svg>
                            View Order History
                        </button>
                        <a href="index.php#contact" class="quick-action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                            Contact Support
                        </a>
                        <a href="auth/logout.php" class="quick-action-btn danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                            </svg>
                            Log Out
                        </a>
                    </nav>
                </div>

                <!-- Member since decorative badge -->
                <div class="profile-card" style="padding:1.5rem;">
                    <div class="member-since">
                        <p class="ms-label">Member Since</p>
                        <p class="ms-date"><?= date('F Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    <div style="margin-top:1rem;text-align:center;">
                        <p style="font-size:.75rem;color:var(--clr-ink-light);line-height:1.5;">
                            Thank you for being a part of the JBeauty family. ✦
                        </p>
                    </div>
                </div>

            </div><!-- /quick-actions -->

        </div><!-- /account-grid -->

    </div><!-- /panel-account -->

    <div id="panel-orders" class="tab-panel" role="tabpanel" aria-labelledby="tab-orders">

        <div class="orders-toolbar">
            <p class="orders-toolbar-left">
                <?= $orderCount > 0
                    ? $orderCount . ' Order' . ($orderCount !== 1 ? 's' : '') . ' Placed'
                    : 'Order History' ?>
            </p>
            <?php if ($orderCount > 0): ?>
            <div class="orders-toolbar-right">
                <a href="shop.php" class="btn-primary-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                    Shop Again
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($orders)): ?>
        <!-- Empty state -->
        <div class="orders-empty" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            <h3>No Orders Yet</h3>
            <p>When you place your first order, it will appear here so you can track it.</p>
            <a href="shop.php" class="btn-primary-sm">Start Shopping →</a>
        </div>

        <?php else: ?>
        <!-- Orders list -->
        <div role="list" aria-label="Your orders">
            <?php foreach ($orders as $order): ?>
            <?php
                $statusClass = 'status-' . preg_replace('/\s+/', '', $order['status']);
                $payLabel    = $paymentLabels[$order['payment_method']] ?? ucwords($order['payment_method']);
                $paddedId    = str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT);
            ?>
            <article class="order-card" role="listitem" aria-label="Order #<?= $paddedId ?>">

                <!-- Order header row -->
                <div class="order-card-head">
                    <div class="order-id-num">
                        <span>Order</span>
                        #<?= $paddedId ?>
                    </div>
                    <div class="order-date">
                        <strong>Date</strong>
                        <?= date('M j, Y', strtotime($order['created_at'])) ?>
                    </div>
                    <span class="order-status <?= htmlspecialchars($statusClass) ?>"
                          aria-label="Status: <?= htmlspecialchars($order['status']) ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                    <div class="order-total">
                        <strong>Total</strong>
                        <span class="amount">₱<?= number_format((float)$order['total_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Line items -->
                <div class="order-card-body">
                    <div class="order-items-preview" role="list" aria-label="Items in order #<?= $paddedId ?>">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-line-item" role="listitem">
                            <img class="oli-img"
                                 src="<?= htmlspecialchars($item['image_url']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 loading="lazy"
                                 onerror="this.src='https://placehold.co/48x48/F2EAE0/C4956A?text=✦'">
                            <div>
                                <p class="oli-name"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="oli-qty">Qty: <?= (int)$item['quantity'] ?></p>
                            </div>
                            <p class="oli-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Footer: payment method -->
                <div class="order-card-foot">
                    <span class="payment-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <?= htmlspecialchars($payLabel) ?>
                    </span>
                    <span>Placed <?= date('g:i A, M j Y', strtotime($order['created_at'])) ?></span>
                </div>

            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /panel-orders -->

</main>

<footer id="contact" role="contentinfo" aria-label="Site footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="nav-logo-mark" aria-hidden="true">J</div>
                <p class="nav-logo-text">JBeauty</p>
                <p class="footer-brand-desc">Premium, inclusive cosmetics crafted for every skin tone. Beauty without compromise, luxury without exclusion.</p>
                <nav aria-label="Social media links">
                    <div class="footer-social">
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Follow JBeauty on TikTok">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                        </a>
                    </div>
                </nav>
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
                    <h4>My Account</h4>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.5rem;">
                        <li><a href="profile.php" style="color:inherit;text-decoration:none;font-size:.88rem;opacity:.75;">Profile</a></li>
                        <li><a href="profile.php" onclick="setTimeout(()=>document.getElementById('tab-orders').click(),100)" style="color:inherit;text-decoration:none;font-size:.88rem;opacity:.75;">Order History</a></li>
                        <li><a href="auth/logout.php" style="color:#C0392B;text-decoration:none;font-size:.88rem;">Log Out</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-copy">&copy; <?= date('Y') ?> JBeauty. All rights reserved. Made with love &amp; purpose.</p>
            <nav class="footer-legal" aria-label="Legal links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </nav>
        </div>
    </div>
</footer>

<script src="assets/js/home.js" defer></script>

<script>
/* ── Tab switching logic ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const tabs   = document.querySelectorAll('.tab-btn[role="tab"]');
    const panels = document.querySelectorAll('.tab-panel[role="tabpanel"]');

    function switchTab(targetBtn) {
        tabs.forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        panels.forEach(p => p.classList.remove('active'));

        targetBtn.classList.add('active');
        targetBtn.setAttribute('aria-selected', 'true');

        const panelId = targetBtn.getAttribute('aria-controls');
        document.getElementById(panelId)?.classList.add('active');
    }

    tabs.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn));

        // Keyboard navigation (←→ arrow keys)
        btn.addEventListener('keydown', e => {
            const idx  = [...tabs].indexOf(btn);
            if (e.key === 'ArrowRight') { tabs[idx + 1]?.focus(); tabs[idx + 1]?.click(); }
            if (e.key === 'ArrowLeft')  { tabs[idx - 1]?.focus(); tabs[idx - 1]?.click(); }
        });
    });

    // Open Orders tab if URL hash says so
    if (window.location.hash === '#orders') {
        const ordersTab = document.getElementById('tab-orders');
        if (ordersTab) switchTab(ordersTab);
    }
});
</script>

</body>
</html>