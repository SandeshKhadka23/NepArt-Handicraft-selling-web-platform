<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get form data
$user_id = $_SESSION["user_id"];
$full_name = $_POST["full_name"];
$phone = $_POST["phone"];
$email = $_POST["email"];
$street_address = $_POST["street_address"];
$city = $_POST["city"];
$district = $_POST["district"];
$special_instructions = $_POST["special_instructions"];
$payment_method = $_POST["payment_method"];

// Insert into Orders table
$sql_order = "INSERT INTO Orders (user_id, full_name, phone, email, street_address, city, district, special_instructions, payment_method) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param(
    "issssssss",
    $user_id,
    $full_name,
    $phone,
    $email,
    $street_address,
    $city,
    $district,
    $special_instructions,
    $payment_method
);
$stmt_order->execute();
$order_id = $stmt_order->insert_id;

if (!$order_id) {
    echo json_encode(["success" => false, "message" => "Failed to create order"]);
    exit();
}

// Insert into Payments table with default payment_status "Pending"
$sql_payment = "INSERT INTO Payments (order_id, payment_status) VALUES (?, 'Pending')";
$stmt_payment = $conn->prepare($sql_payment);
$stmt_payment->bind_param("i", $order_id);
$stmt_payment->execute();

if ($stmt_payment->affected_rows <= 0) {
    echo json_encode(["success" => false, "message" => "Failed to create payment record"]);
    exit();
}

// Function to update stock
function updateStock($conn, $product_id, $quantity) {
    $sql_update_stock = "UPDATE Product SET stock = stock - ? WHERE product_id = ?";
    $stmt_update_stock = $conn->prepare($sql_update_stock);
    $stmt_update_stock->bind_param("ii", $quantity, $product_id);
    $stmt_update_stock->execute();

    if ($stmt_update_stock->affected_rows <= 0) {
        return false;
    }
    return true;
}

// Check if the order is from "Buy Now" or Cart
if (isset($_POST["product_id"]) && isset($_POST["quantity"])) {
    // Handle "Buy Now" order
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];

    // Fetch product details
    $sql_product = "SELECT price, stock FROM Product WHERE product_id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();

    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();
        $price_per_unit = $product["price"];
        $stock = $product["stock"];

        // Check if there's enough stock
        if ($stock < $quantity) {
            echo json_encode(["success" => false, "message" => "Not enough stock available"]);
            exit();
        }

        // Update stock
        if (!updateStock($conn, $product_id, $quantity)) {
            echo json_encode(["success" => false, "message" => "Failed to update stock"]);
            exit();
        }

        // Calculate subtotal
        $subtotal = $price_per_unit * $quantity;

        // Insert into OrderDetails table
        $sql_details = "INSERT INTO OrderDetails (order_id, product_id, quantity, price_per_unit, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("iiidd", $order_id, $product_id, $quantity, $price_per_unit, $subtotal);
        $stmt_details->execute();
    } else {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit();
    }
} else {
    // Handle cart-based order
    $sql_cart = "SELECT c.product_id, c.quantity, p.price, p.stock 
                 FROM Cart c 
                 JOIN Product p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    // Insert into OrderDetails table and update stock
    while ($row = $result_cart->fetch_assoc()) {
        $product_id = $row["product_id"];
        $quantity = $row["quantity"];
        $price_per_unit = $row["price"];
        $stock = $row["stock"];

        // Check if there's enough stock
        if ($stock < $quantity) {
            echo json_encode(["success" => false, "message" => "Not enough stock available for product ID: $product_id"]);
            exit();
        }

        // Update stock
        if (!updateStock($conn, $product_id, $quantity)) {
            echo json_encode(["success" => false, "message" => "Failed to update stock for product ID: $product_id"]);
            exit();
        }

        // Calculate subtotal
        $subtotal = $price_per_unit * $quantity;

        // Insert into OrderDetails table
        $sql_details = "INSERT INTO OrderDetails (order_id, product_id, quantity, price_per_unit, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("iiidd", $order_id, $product_id, $quantity, $price_per_unit, $subtotal);
        $stmt_details->execute();
    }

    // Clear the cart
    $sql_clear_cart = "DELETE FROM Cart WHERE user_id = ?";
    $stmt_clear_cart = $conn->prepare($sql_clear_cart);
    $stmt_clear_cart->bind_param("i", $user_id);
    $stmt_clear_cart->execute();
}

echo json_encode(["success" => true, "order_id" => $order_id]);
?>