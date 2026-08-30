<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$user_id = $_SESSION["user_id"];
// Fetch orders for the logged-in user
$sql_orders = "SELECT o.order_id, o.order_date, o.status 
               FROM Orders o 
               WHERE o.user_id = ? 
               ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql_orders);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .track-orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }
        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        .order-header h3 {
            margin: 0;
        }
        .order-status {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            background: #f8f9fa;
        }
        .payment-status {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .order-details {
            margin-top: 1rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
        }
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .order-status {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <img src="../assets/static_pictures/artisan/download.png" alt="Logo" class="logo">
                <span class="brand-name">NepArt Creations</span>
            </div>
            <div class="nav-right">
                <a href="buyer_dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
            </div>
        </div>
    </nav>
    <div class="track-orders-container">
        <h1>Track Orders</h1>
        <?php
        if ($result_orders->num_rows > 0) {
            while ($order = $result_orders->fetch_assoc()) {
                $order_id = $order["order_id"];
                $order_date = $order["order_date"];
                $status = $order["status"];
                // Fetch payment status for this order
                $sql_payment = "SELECT payment_status FROM Payments WHERE order_id = ?";
                $stmt_payment = $conn->prepare($sql_payment);
                $stmt_payment->bind_param("i", $order_id);
                $stmt_payment->execute();
                $result_payment = $stmt_payment->get_result();
                $payment_status = "Pending"; // Default value
                if ($result_payment->num_rows > 0) {
                    $payment = $result_payment->fetch_assoc();
                    $payment_status = $payment["payment_status"];
                }
                // Fetch order details for this order
                $sql_order_details = "SELECT od.product_id, od.quantity, od.price_per_unit, od.subtotal, p.product_name 
                                     FROM OrderDetails od 
                                     JOIN Product p ON od.product_id = p.product_id 
                                     WHERE od.order_id = ?";
                $stmt_details = $conn->prepare($sql_order_details);
                $stmt_details->bind_param("i", $order_id);
                $stmt_details->execute();
                $result_details = $stmt_details->get_result();
                $total_amount = 0;
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?php echo $order_id; ?></h3>
                        <div class="order-status">Status: <?php echo $status; ?></div>
                    </div>
                    <p>Order Date: <?php echo $order_date; ?></p>
                    <div class="order-details">
                        <?php
                        while ($detail = $result_details->fetch_assoc()) {
                            $total_amount += $detail["subtotal"];
                            ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($detail['product_name']); ?> (×<?php echo $detail['quantity']; ?>)</span>
                                <span>Rs. <?php echo number_format($detail['subtotal'], 2); ?></span>
                            </div>
                            <div class="payment-status">
                        Payment Status: <?php echo $payment_status; ?> 
                    </div>
                            <?php
                        }
                        ?>
                        <div class="total-line">
                            <span>Total Amount:</span>
                            <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No orders found.</p>";
        }
        ?>
    </div>
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@nepart.com</p>
                <p>Phone: +977-123456789</p>
                <p>Address: Kathmandu, Nepal</p>
            </div>
            <div class="quicklinks">
                <h3>Quick Links</h3>
                <a href="buyer_dashboard.php">Home</a><br>
                <a href="track_order.php">Track Orders</a><br>
                <a href="cart.php">Cart</a>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <p>
                    <a href="#">Facebook</a> | 
                    <a href="#">Instagram</a> | 
                    <a href="#">Twitter</a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 NepArt Creations. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>