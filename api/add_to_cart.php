<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ── Decode product_id from whichever format cart.js sends ─────────────────────
$productId = 0;

// 1. JSON body  (Content-Type: application/json)
$rawBody = file_get_contents('php://input');
$json    = json_decode($rawBody, true);
if (is_array($json) && isset($json['product_id'])) {
    $productId = (int) $json['product_id'];
}

// 2. URL-encoded or FormData  (Content-Type: application/x-www-form-urlencoded)
if ($productId <= 0 && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
}

// 3. Query-string fallback  (?product_id= appended to the fetch URL)
if ($productId <= 0 && isset($_GET['product_id'])) {
    $productId = (int) $_GET['product_id'];
}

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing product_id.',
    ]);
    exit;
}

// ── Add / increment in session ────────────────────────────────────────────────
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]['qty']++;
} else {
    $_SESSION['cart'][$productId] = ['qty' => 1];
}

// ── Build the FULL enriched cart (same logic as update_cart.php) ──────────────
$cartItems = [];
$cartTotal = 0.0;

try {
    require_once __DIR__ . '/../config/config.php';

    $pdo = getDB();

    // Fetch every product currently in the session cart
    $ids  = array_keys($_SESSION['cart']);
    $ph   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, name, price, image_url FROM products WHERE id IN ($ph)"
    );
    $stmt->execute($ids);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $qty      = $_SESSION['cart'][$row['id']]['qty'] ?? 1;
        $subtotal = (float) $row['price'] * $qty;

        $cartItems[] = [
            'id'        => (int)   $row['id'],
            'name'      =>         $row['name'],
            'price'     => (float) $row['price'],
            'image_url' =>         $row['image_url'],
            'qty'       =>         $qty,
            'subtotal'  =>         $subtotal,
        ];

        $cartTotal += $subtotal;
    }

    $cartCount = array_sum(array_column($cartItems, 'qty'));

    echo json_encode([
        'success'    => true,
        'cart'       => $cartItems,   // ← full cart, not just the added item
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal,
    ]);

} catch (Throwable $e) {
    // Roll back the qty bump so session stays consistent
    if (isset($_SESSION['cart'][$productId])) {
        if ($_SESSION['cart'][$productId]['qty'] > 1) {
            $_SESSION['cart'][$productId]['qty']--;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not reach the database. Please try again.',
        // Uncomment during local development only:
        // 'debug'  => $e->getMessage(),
    ]);
}