<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}

$cart      = $_SESSION['cart'];
$cartItems = [];
$dbError   = null;

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
    $dbError = $e->getMessage();
}

$subtotal    = array_sum(array_column($cartItems, 'subtotal'));
$shippingFee = $subtotal >= 2000 ? 0.00 : 150.00;   // Free shipping over ₱2 000
$orderTotal  = $subtotal + $shippingFee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JBeauty Checkout — Secure order placement for your curated cosmetics.">
    <title>Checkout - JBeauty</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="icon" type="image/png" href="assets/img/jbeautylogo.jpg">
    <link rel="stylesheet" href="assets/css/checkout.css">
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
                <?php $profileRoute = isset($_SESSION['user_id']) ? 'profile.php' : 'auth/login.php'; ?>
                    <a href="<?= $profileRoute ?>" class="nav-icon-btn" aria-label="View your profile">
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

    <hr style="border:none; border-top:1px solid rgba(44,27,14,.12); margin:.5rem 1.2rem;">

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php">My Profile</a>
        <a href="auth/logout.php" style="color:#C0392B;">Log Out</a>
    <?php else: ?>
        <a href="auth/login.php">Log In / Register</a>
    <?php endif; ?>
</nav>
</header>

<section class="checkout-hero" aria-labelledby="checkoutHeading">
    <h1 id="checkoutHeading">Complete Your <em>Order</em></h1>

    <nav class="checkout-steps" aria-label="Checkout progress">
        <div class="step">
            <span class="step-num" aria-hidden="true">✓</span>
            Cart
        </div>
        <div class="step-divider" aria-hidden="true"></div>
        <div class="step active" aria-current="step">
            <span class="step-num" aria-hidden="true">2</span>
            Checkout
        </div>
        <div class="step-divider" aria-hidden="true"></div>
        <div class="step">
            <span class="step-num" aria-hidden="true">3</span>
            Confirmation
        </div>
    </nav>
</section>

<main class="checkout-layout" id="main-content">

    <div class="checkout-form-col">

        <a href="shop.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Shop
        </a>

        <!-- Global error alert (populated by JS) -->
        <div class="form-alert" id="formAlert" role="alert" aria-live="assertive">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="formAlertText"></span>
        </div>

        <form id="checkoutForm" novalidate aria-label="Checkout form">

            <!-- ── Shipping Information ── -->
            <div class="form-card">
                <h2 class="section-heading">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Shipping Information
                </h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required" aria-label="required">*</span></label>
                        <input type="text" id="full_name" name="full_name"
                               autocomplete="name" placeholder="Maria Santos"
                               required aria-required="true" aria-describedby="full_name_err">
                        <span class="field-error" id="full_name_err" role="alert">Please enter your full name.</span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               autocomplete="tel" placeholder="09XX XXX XXXX"
                               aria-describedby="phone_hint">
                        <span class="field-error" id="phone_hint" style="color:var(--clr-ink-light);display:block;">Optional — for delivery updates</span>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required" aria-label="required">*</span></label>
                        <input type="email" id="email" name="email"
                               autocomplete="email" placeholder="maria@example.com"
                               required aria-required="true" aria-describedby="email_err">
                        <span class="field-error" id="email_err" role="alert">Please enter a valid email address.</span>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="shipping_address">Delivery Address <span class="required" aria-label="required">*</span></label>
                        <textarea id="shipping_address" name="shipping_address"
                                  autocomplete="street-address"
                                  placeholder="House/Unit No., Street, Barangay, City, Province, ZIP Code"
                                  required aria-required="true" aria-describedby="address_err"></textarea>
                        <span class="field-error" id="address_err" role="alert">Please enter your complete delivery address.</span>
                    </div>
                </div>
            </div><!-- /form-card -->

            <!-- ── Payment Method ── -->
            <div class="form-card">
                <h2 class="section-heading">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    Payment Method
                </h2>

                <div class="payment-options" role="radiogroup" aria-label="Select payment method">

                    <div class="payment-option">
                        <input type="radio" id="pay_credit" name="payment_method"
                               value="credit_card" aria-describedby="pay_credit_label">
                        <label class="payment-label" id="pay_credit_label" for="pay_credit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            Credit / Debit Card
                        </label>
                    </div>

                    <div class="payment-option">
                        <input type="radio" id="pay_cod" name="payment_method"
                               value="cash_on_delivery" checked aria-describedby="pay_cod_label">
                        <label class="payment-label" id="pay_cod_label" for="pay_cod">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M17 12h2M5 12H3"/>
                            </svg>
                            Cash on Delivery
                        </label>
                    </div>

                </div><!-- /payment-options -->

                <!-- Credit Card fields (shown when CC is selected) -->
                <div class="card-fields" id="cardFields" aria-live="polite">
                    <div class="form-group" style="margin-top:1.1rem;">
                        <label for="card_name">Name on Card</label>
                        <input type="text" id="card_name" name="card_name"
                               autocomplete="cc-name" placeholder="Maria Santos">
                    </div>
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <div class="card-number-wrap">
                            <input type="text" id="card_number" name="card_number"
                                   autocomplete="cc-number" placeholder="•••• •••• •••• ••••"
                                   maxlength="19" inputmode="numeric">
                            <div class="card-icons" aria-hidden="true">
                                <!-- Visa mock -->
                                <svg viewBox="0 0 28 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="28" height="18" rx="3" fill="#1A1F71"/>
                                    <text x="4" y="13" font-size="7" fill="#fff" font-family="sans-serif" font-weight="bold">VISA</text>
                                </svg>
                                <!-- MC mock -->
                                <svg viewBox="0 0 28 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="28" height="18" rx="3" fill="#252525"/>
                                    <circle cx="10" cy="9" r="5" fill="#EB001B" opacity=".9"/>
                                    <circle cx="18" cy="9" r="5" fill="#F79E1B" opacity=".9"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_expiry">Expiry Date</label>
                            <input type="text" id="card_expiry" name="card_expiry"
                                   autocomplete="cc-exp" placeholder="MM / YY" maxlength="7">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv"
                                   autocomplete="cc-csc" placeholder="•••" maxlength="4"
                                   inputmode="numeric">
                        </div>
                    </div>
                    <p style="font-size:.73rem;color:var(--clr-ink-light);margin-top:.3rem;">
                         This is a mock payment field. No real card data is stored.
                    </p>
                </div><!-- /card-fields -->

            </div><!-- /form-card -->

            <!-- ── Place Order ── -->
            <button type="submit" class="btn-place-order" id="placeOrderBtn">
                <span class="btn-spinner" aria-hidden="true"></span>
                <span class="btn-label">Place Order →</span>
            </button>

            <p class="secure-badge" aria-label="SSL secured checkout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                SSL Encrypted &amp; Secure Checkout
            </p>

        </form>
    </div>

    <div class="order-summary-col">
        <aside class="summary-card" aria-label="Order summary">

            <h2 class="section-heading" style="margin-bottom:1.2rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0 2-2h2a2 2 0 0 0 2 2"/>
                </svg>
                Order Summary
            </h2>

            <?php if ($dbError): ?>
            <p style="color:var(--clr-error);font-size:.85rem;">Unable to load cart items.</p>
            <?php else: ?>

            <div class="summary-items" role="list" aria-label="Items in your order">
                <?php foreach ($cartItems as $item): ?>
                <div class="summary-item" role="listitem">
                    <img
                        class="summary-item-img"
                        src="<?= htmlspecialchars($item['image_url']) ?>"
                        alt="<?= htmlspecialchars($item['name']) ?>"
                        loading="lazy"
                        onerror="this.src='https://placehold.co/60x60/F2EAE0/C4956A?text=✦'">
                    <div>
                        <p class="summary-item-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="summary-item-qty">Qty: <?= (int)$item['qty'] ?></p>
                    </div>
                    <p class="summary-item-price">₱<?= number_format($item['subtotal'], 2) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="totals-row">
                    <span>Shipping</span>
                    <?php if ($shippingFee === 0.00): ?>
                        <span class="shipping-free">FREE</span>
                    <?php else: ?>
                        <span>₱<?= number_format($shippingFee, 2) ?></span>
                    <?php endif; ?>
                </div>
                <div class="totals-row total">
                    <span>Total</span>
                    <span class="amount-val">₱<?= number_format($orderTotal, 2) ?></span>
                </div>
            </div>

            <?php if ($shippingFee > 0): ?>
            <p class="summary-note">
                 Add ₱<?= number_format(2000 - $subtotal, 2) ?> more to qualify for free shipping.
            </p>
            <?php else: ?>
            <p class="summary-note" style="color:var(--clr-success);">
                 You've qualified for free shipping!
            </p>
            <?php endif; ?>

            <!-- Hidden fields read by JS for the fetch payload -->
            <input type="hidden" id="orderTotal" value="<?= number_format($orderTotal, 2, '.', '') ?>">

            <?php endif; ?>
        </aside>
    </div>

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
<script src="assets/js/checkout.js" defer></script>

</body>
</html>