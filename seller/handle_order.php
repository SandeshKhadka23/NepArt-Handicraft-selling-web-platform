<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $query = $query = "SELECT Orders.*, payments.payment_status 
    FROM Orders 
    LEFT JOIN payments ON Orders.order_id = payments.order_id 
    WHERE Orders.order_id = ?";
;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        echo "Order not found!";
        exit();
    }
} else {
    echo "No order ID specified!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST["status"];
    $payment_status = $_POST["payment_status"];

    // Update order status
    $update_order_query = "UPDATE Orders SET status = ? WHERE order_id = ?";
    $update_order_stmt = $conn->prepare($update_order_query);
    $update_order_stmt->bind_param("si", $status, $order_id);
    $update_order_stmt->execute();

    // Update payment status
    $update_payment_query = "UPDATE payments SET payment_status = ? WHERE order_id = ?";
    $update_payment_stmt = $conn->prepare($update_payment_query);
    $update_payment_stmt->bind_param("si", $payment_status, $order_id);
    $update_payment_stmt->execute();

    if ($update_order_stmt->affected_rows > 0 && $update_payment_stmt->affected_rows == 0 ) {
        $success_message = "Order Status updated successfully!";
        $error_message="Payment Status Updation Failed!!";
    } else if ( $update_payment_stmt->affected_rows > 0 && $update_order_stmt->affected_rows == 0 ) {
        $success_message = "Payment Status Updated successfully!";
        $error_message="Order Status Updation Failed!!";
    }
   else if ( $update_payment_stmt->affected_rows > 0 && $update_order_stmt->affected_rows > 0 ) {
    $success_message = "Order Status and Payment Status Updated successfully!";
    }
    else {
        $error_message="Status Updation Failed!!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status - NepArt Creations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo">
            <img src="download.png" alt="Logo" class="logo">
            <span class="brand-name">NepArt Creations</span>
        </div>
        <div class="nav-links">
            <a href="artisan_dashboard.php" class="nav-button"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_orders.php" class="nav-button"><i class="fas fa-shopping-cart"></i> Manage Orders</a>
        </div>
    </nav>

    <div class="status-header">
        <div class="header-content">
            <h1>Update Order Status</h1>
            <p>Order #<?php echo htmlspecialchars($order_id); ?></p>
        </div>
    </div>

    <section class="form-section">
        <div class="form-container">
            <?php if (isset($success_message)): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="order-summary">
                <h2><i class="fas fa-info-circle"></i> Order Details</h2>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Customer Name:</span>
                        <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Current Status:</span>
                        <span class="value status-badge"><?php echo htmlspecialchars($order['status']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Payment Status:</span>
                        <span class="value payment-status"><?php echo htmlspecialchars($order['payment_status'] ?? "Not Paid"); ?></span>
                    </div>
                </div>
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-tasks"></i> Update Order Status
                    </label>
                    <select id="status" name="status" required>
                        <option value="Pending" <?php if ($order["status"] == "Pending") echo "selected"; ?>>
                            <i class="fas fa-clock"></i> Pending
                        </option>
                        <option value="Processing" <?php if ($order["status"] == "Processing") echo "selected"; ?>>
                            <i class="fas fa-cog"></i> Processing
                        </option>
                        <option value="Shipped" <?php if ($order["status"] == "Shipped") echo "selected"; ?>>
                            <i class="fas fa-shipping-fast"></i> Shipped
                        </option>
                        <option value="Delivered" <?php if ($order["status"] == "Delivered") echo "selected"; ?>>
                            <i class="fas fa-check"></i> Delivered
                        </option>
                        <option value="Cancelled" <?php if ($order["status"] == "Cancelled") echo "selected"; ?>>
                            <i class="fas fa-times"></i> Cancelled
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_status">
                        <i class="fas fa-money-bill"></i> Update Payment Status
                    </label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="Pending" <?php if ($order["payment_status"] == "Pending") echo "selected"; ?>>
                            <i class="fas fa-clock"></i> Pending
                        </option>
                        <option value="Paid" <?php if ($order["payment_status"] == "Paid") echo "selected"; ?>>
                            <i class="fas fa-check"></i> Paid
                        </option>
                        <option value="Failed" <?php if ($order["payment_status"] == "Failed") echo "selected"; ?>>
                            <i class="fas fa-times"></i> Failed
                        </option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                    <a href="view_orders.php" class="cancel-btn">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </form>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>NepArt Creations</h3>
                <p>Handmade Treasures, One Click Away.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="dashboard.php">Dashboard</a>
                <a href="view_orders.php">Orders</a>
                <a href="profile.php">Profile</a>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-envelope"></i> support@artisancraft.com</p>
                <p><i class="fas fa-phone"></i> +977-1234567890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 NepArt Creations. All rights reserved.</p>
        </div>
    </footer>

    <style>
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        /* Navigation Bar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            height: 40px;
            border-radius: 50%;
        }

        .brand-name {
            font-size: 1.2rem;
            color: #b91111;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-button {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            background-color: #b91111;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #8b0000;
        }

        /* Status Header */
        .status-header {
            margin-top: 60px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('buddha.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }

        .header-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .header-content p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Form Section */
        .form-section {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-summary {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-summary h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .label {
            color: #666;
            font-size: 0.9rem;
        }

        .value {
            font-weight: bold;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        select:focus {
            border-color: #b91111;
            outline: none;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .submit-btn, .cancel-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #b91111;
            color: white;
            border: none;
            flex: 1;
        }

        .submit-btn:hover {
            background-color: #8b0000;
        }

        .cancel-btn {
            background-color: #f1f1f1;
            color: #333;
            flex: 1;
            justify-content: center;
        }

        .cancel-btn:hover {
            background-color: #e1e1e1;
        }

        /* Footer */
        .footer {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 3rem 2rem 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: white;
        }

        .footer-section a {
            display: block;
            color: #ffffff;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-section p {
            margin-bottom: 0.5rem;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }

            .brand-name {
                display: none;
            }

            .status-header {
                padding: 2rem 1rem;
            }

            .status-header h1 {
                font-size: 2rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .submit-btn, .cancel-btn {
                width: 100%;
                justify-content: center;
            }
        }
        /* Add this CSS for payment status */
        .payment-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .payment-status.Pending {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .payment-status.Paid {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-status.Failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>