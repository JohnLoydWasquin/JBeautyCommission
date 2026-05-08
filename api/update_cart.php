<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$productId || !isset($_SESSION['cart'][$productId])) {
    echo json_encode(['success' => false, 'message' => 'Item not in cart']);
    exit;
}

if ($action === 'increase') {
    $_SESSION['cart'][$productId]['qty'] += 1;
} elseif ($action === 'decrease') {
    $_SESSION['cart'][$productId]['qty'] -= 1;
    if ($_SESSION['cart'][$productId]['qty'] <= 0) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Fetch current full cart state to send back
require_once __DIR__ . '/../config/config.php';
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    try {
        $pdo = getDB();
        $ids = array_keys($_SESSION['cart']);
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($ph)");
        $stmt->execute($ids);
        
        foreach ($stmt->fetchAll() as $row) {
            $row['qty'] = $_SESSION['cart'][$row['id']]['qty'];
            $cartItems[$row['id']] = $row;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}

echo json_encode(['success' => true, 'cart' => $cartItems]);
?>