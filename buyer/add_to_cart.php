<?php
session_start();
header('Content-Type: application/json');

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart'
    ]);
    exit;
}

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

// Validate product_id and quantity
if (!$product_id || !$quantity || $quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID or quantity'
    ]);
    exit;
}

try {
    // Database connection
    $db = new PDO(
        "mysql:host=localhost;dbname=HandicraftStore",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Start transaction
    $db->beginTransaction();

    // Check if product exists and get its details
    $stmt = $db->prepare("
        SELECT product_name, price, stock
        FROM Product 
        WHERE product_id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Check stock availability
    if ($product['stock'] < $quantity) {
        throw new Exception('Not enough stock available');
    }

    // Update product stock
    $stmt = $db->prepare("
        UPDATE Product 
        SET stock = stock - ? 
        WHERE product_id = ?
    ");
    $stmt->execute([$quantity, $product_id]);

    // Check if product already exists in cart
    $stmt = $db->prepare("
        SELECT cart_id, quantity 
        FROM Cart 
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$user_id, $product_id]);
    $existing_cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_cart_item) {
        // Update existing cart item
        $stmt = $db->prepare("
            UPDATE Cart 
            SET quantity = quantity + ? 
            WHERE cart_id = ?
        ");
        $stmt->execute([$quantity, $existing_cart_item['cart_id']]);
    } else {
        // Insert new cart item
        $stmt = $db->prepare("
            INSERT INTO Cart (user_id, product_id, quantity, price_per_unit) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $product_id, $quantity, $product['price']]);
    }

    // Get updated cart count
    $stmt = $db->prepare("
        SELECT SUM(quantity) as cart_count 
        FROM Cart 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'product_name' => $product['product_name'],
        'cart_count' => $cart_count,
        'message' => 'Product added to cart successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>