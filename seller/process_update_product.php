<?php
session_start();
include "../includes/db.php";

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    $query = "UPDATE Product SET product_name = ?, price = ?, description = ?, stock = ?, category_id = ? WHERE product_id = ? AND artisan_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdsiisi", $product_name, $price, $description, $stock, $category_id, $product_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "Product updated successfully!";
        header("Location: artisan_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
