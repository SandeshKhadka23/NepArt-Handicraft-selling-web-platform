<?php
session_start();
include "../includes/db.php";

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Check if the product is in the cart
    $check_cart_query = "SELECT COUNT(*) AS cart_count FROM cart WHERE product_id = ?";
    $stmt_cart = $conn->prepare($check_cart_query);
    $stmt_cart->bind_param("i", $product_id);
    $stmt_cart->execute();
    $cart_result = $stmt_cart->get_result();
    $cart_data = $cart_result->fetch_assoc();

    // Check if the product is in any order (OrderDetails table)
    $check_order_query = "SELECT COUNT(*) AS order_count FROM OrderDetails WHERE product_id = ?";
    $stmt_order = $conn->prepare($check_order_query);
    $stmt_order->bind_param("i", $product_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    $order_data = $order_result->fetch_assoc();

    // If the product is in the cart or any order, display a JavaScript alert
    if ($cart_data['cart_count'] > 0) {
        echo "<script>alert('You cannot delete this product because it is currently in the cart.');window.location.href = 'artisan_dashboard.php';</script>";
    } elseif ($order_data['order_count'] > 0) {
        echo "<script>alert('You cannot delete this product because it has already been placed in an order.');window.location.href = 'artisan_dashboard.php';</script>";
    } else {
        // If the product is not in the cart or any order, proceed to delete it
        $query = "DELETE FROM Product WHERE product_id = ? AND artisan_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            echo "<script>alert('Product deleted successfully!'); window.location.href = 'artisan_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error deleting product: " . $stmt->error . "');</script>";
        }
    }
} else {
    echo "<script>alert('No product ID specified!');</script>";
}
?>
