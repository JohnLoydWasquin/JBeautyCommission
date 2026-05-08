<?php
declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

// ── Helpers ──────────────────────────────────────────────────────
header('Content-Type: application/json');

/**
 * Emit a JSON response and halt execution.
 *
 * @param bool   $success
 * @param string $message  Human-readable message for the client.
 * @param array  $data     Optional payload merged into the response.
 */
function jsonResponse(bool $success, string $message, array $data = []): never
{
    $status = $success ? 200 : 400;
    http_response_code($status);
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Guard: POST only ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// ── Guard: cart must not be empty ────────────────────────────────
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    jsonResponse(false, 'Your cart is empty. Please add items before placing an order.');
}

// ── Sanitise & validate incoming fields ──────────────────────────
$fullName       = trim($_POST['full_name']       ?? '');
$email          = trim($_POST['email']           ?? '');
$phone          = trim($_POST['phone']           ?? '');
$shippingAddr   = trim($_POST['shipping_address'] ?? '');
$paymentMethod  = trim($_POST['payment_method']  ?? '');

$validPayments  = ['credit_card', 'cash_on_delivery'];

$errors = [];

if ($fullName === '') {
    $errors[] = 'Full name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if ($shippingAddr === '') {
    $errors[] = 'Shipping address is required.';
}
if (!in_array($paymentMethod, $validPayments, true)) {
    $errors[] = 'Please select a valid payment method.';
}

if (!empty($errors)) {
    jsonResponse(false, implode(' ', $errors));
}

// ── Fetch live prices from DB (never trust client-submitted prices) ─
try {
    $pdo        = getDB();
    $ids        = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT id, price FROM products WHERE id IN ({$placeholders})"
    );
    $stmt->execute($ids);
    $dbProducts = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);   // [id => price]
} catch (\RuntimeException $e) {
    jsonResponse(false, 'Database error. Please try again later.');
}

// Calculate total using only DB prices
$totalAmount = 0.0;
$lineItems   = [];

foreach ($cart as $productId => $row) {
    $pid = (int) $productId;
    $qty = max(1, (int) ($row['qty'] ?? 1));

    if (!isset($dbProducts[$pid])) {
        // Product was removed from DB — skip silently
        continue;
    }

    $unitPrice     = (float) $dbProducts[$pid];
    $totalAmount  += $unitPrice * $qty;

    $lineItems[] = [
        'product_id' => $pid,
        'quantity'   => $qty,
        'price'      => $unitPrice,
    ];
}

if (empty($lineItems)) {
    jsonResponse(false, 'None of the cart items are available. Please return to the shop.');
}

// Add shipping fee (mirrors checkout.php logic)
$shippingFee  = $totalAmount >= 2000.0 ? 0.0 : 150.0;
$totalAmount += $shippingFee;

// ── Insert order & order_items inside a transaction ──────────────
try {
    $pdo->beginTransaction();

    // 1. Insert parent order row
    $insertOrder = $pdo->prepare(
        'INSERT INTO orders
            (user_id, full_name, email, phone, shipping_address,
             total_amount, payment_method, status)
         VALUES
            (:user_id, :full_name, :email, :phone, :shipping_address,
             :total_amount, :payment_method, \'Pending\')'
    );

    $insertOrder->execute([
        ':user_id'          => $_SESSION['user_id'] ?? null,   // null = guest
        ':full_name'        => $fullName,
        ':email'            => $email,
        ':phone'            => $phone !== '' ? $phone : null,
        ':shipping_address' => $shippingAddr,
        ':total_amount'     => round($totalAmount, 2),
        ':payment_method'   => $paymentMethod,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // 2. Insert each line item
    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, price)
         VALUES (:order_id, :product_id, :quantity, :price)'
    );

    foreach ($lineItems as $item) {
        $insertItem->execute([
            ':order_id'   => $orderId,
            ':product_id' => $item['product_id'],
            ':quantity'   => $item['quantity'],
            ':price'      => $item['price'],
        ]);
    }

    $pdo->commit();

} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the real error server-side; never expose it to the client
    error_log('[JBeauty] process_order error: ' . $e->getMessage());
    jsonResponse(false, 'An unexpected error occurred. Your order was not placed. Please try again.');
}

// ── Success — clear cart, return order ID ────────────────────────
unset($_SESSION['cart']);

jsonResponse(true, 'Order placed successfully!', [
    'order_id' => $orderId,
]);