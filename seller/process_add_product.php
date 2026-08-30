<?php
session_start();
include "../includes/db.php";

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $artisan_id = $_SESSION['user_id'];

    // Handle image upload
    $image_name = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        // Set the directory for image uploads
        $upload_dir = "../assets/dynamic_pictures/products/";

        // Create the directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Get file information
        $image_tmp_name = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = $upload_dir . $image_name;

        // Move the uploaded file to the uploads directory
        if (!move_uploaded_file($image_tmp_name, $image_path)) {
            die("Error uploading the image.");
        }
    }

    // Insert product data into the database
    $query = "INSERT INTO Product (product_name, price, description, stock, category_id, artisan_id, image) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdssiis", $product_name, $price, $description, $stock, $category_id, $artisan_id, $image_name);

    if ($stmt->execute()) {
        echo "Product added successfully!";
        header("Location: artisan_dashboard.php");
        exit();
    } else {
        echo "Error adding product: " . $conn->error;
    }
}
?>
