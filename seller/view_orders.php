<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

$artisan_id = $_SESSION["user_id"];

// Fetch all orders containing products made by this artisan
$query = "
    SELECT DISTINCT o.order_id, o.order_date, o.status, 
           o.full_name, o.phone, o.email, o.street_address, o.city, o.district, 
           p.payment_status, p.amount
    FROM Orders o
    JOIN OrderDetails od ON o.order_id = od.order_id
    JOIN Product pr ON od.product_id = pr.product_id
    LEFT JOIN Payments p ON o.order_id = p.order_id
    WHERE pr.artisan_id = ?
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - NepArt Creations</title>
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
            <a href="add_product.php" class="nav-button"><i class="fas fa-plus"></i> Add Product</a>
        </div>
    </nav>

    <div class="orders-header">
        <div class="header-content">
            <h1>Manage Your Orders</h1>
            <p>Track and manage all your customer orders in one place</p>
        </div>
    </div>

    <section class="orders-section">
        <div class="orders-container">
            <?php while ($order = $result->fetch_assoc()) { 
                // Fetch product details for this order
                $order_id = $order["order_id"];
                $sql_products = "
                    SELECT pr.product_name, pr.image, od.quantity, od.price_per_unit, od.subtotal, o.order_date
                    FROM OrderDetails od
                    JOIN Product pr ON od.product_id = pr.product_id
                    JOIN Orders o ON od.order_id = o.order_id
                    WHERE od.order_id = ?
                ";
                $stmt_products = $conn->prepare($sql_products);
                $stmt_products->bind_param("i", $order_id);
                $stmt_products->execute();
                $result_products = $stmt_products->get_result();
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?php echo htmlspecialchars($order["order_id"]); ?></h3>
                        <span class="order-date"><?php echo date('F j, Y, g:i A', strtotime($order["order_date"])); ?></span>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-group">
                            <h4><i class="fas fa-user"></i> Customer Information</h4>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order["full_name"]); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order["phone"]); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order["email"]); ?></p>
                        </div>

                        <div class="detail-group">
                            <h4><i class="fas fa-map-marker-alt"></i> Shipping Address</h4>
                            <p><?php echo htmlspecialchars($order["street_address"]); ?></p>
                            <p><?php echo htmlspecialchars($order["city"] . ", " . $order["district"]); ?></p>
                        </div>

                        <div class="detail-group">
                            <h4><i class="fas fa-info-circle"></i> Order Status</h4>
                            <p class="status-badge"><?php echo htmlspecialchars($order["status"]); ?></p>
                            <p class="payment-status">
                                <strong>Payment:</strong> 
                                <span class="<?php echo ($order["payment_status"] == "Paid") ? "paid" : "unpaid"; ?>">
                                    <?php echo htmlspecialchars($order["payment_status"] ?? "Not Paid"); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Product Details Section -->
                    <div class="product-details">
                        <h4><i class="fas fa-box"></i> Products Ordered</h4>
                        <?php while ($product = $result_products->fetch_assoc()) { ?>
                            <div class="product-item">
                                <img src="../assets/dynamic_pictures/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image">
                                <div class="product-info">
                                    <p><strong>Product:</strong> <?php echo htmlspecialchars($product['product_name']); ?></p>
                                    <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
                                    <p><strong>Price per Unit:</strong> NRS <?php echo number_format($product['price_per_unit'], 2); ?></p>
                                    <p><strong>Subtotal:</strong> NRS <?php echo number_format($product['subtotal'], 2); ?></p>
                                    <p><strong>Ordered On:</strong> <?php echo date('F j, Y, g:i A', strtotime($product['order_date'])); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="order-actions">
                        <a href="handle_order.php?order_id=<?php echo $order['order_id']; ?>" class="action-button">
                            <i class="fas fa-tasks"></i> View & Manage Order
                        </a>
                    </div>
                </div>
            <?php } ?>
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
                <a href="artisan_dashboard.php">Dashboard</a>
                <a href="add_product.php">Add Product</a>
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

        /* Orders Header */
        .orders-header {
            margin-top: 60px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('thangka painting.webp');
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

        /* Orders Section */
        .orders-section {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .orders-container {
            display: grid;
            gap: 2rem;
            padding: 1rem;
        }

        .order-card {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .order-header h3 {
            color: #2c3e50;
        }

        .order-date {
            color: #666;
            font-size: 0.9rem;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-group {
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .detail-group h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-group p {
            margin-bottom: 0.5rem;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 15px;
            font-weight: bold;
        }

        .payment-status .paid {
            color: #4caf50;
        }

        .payment-status .unpaid {
            color: #f44336;
        }

        .order-actions {
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .action-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #b91111;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background: #8b0000;
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

            .orders-header {
                padding: 2rem 1rem;
            }

            .orders-header h1 {
                font-size: 2rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .action-button {
                width: 100%;
                text-align: center;
            }
        }
       /* Add this CSS for the product details section */
       .product-details {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .product-details h4 {
            margin-bottom: 1rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-info p {
            margin: 0.3rem 0;
            color: #666;
        }

        .product-info p strong {
            color: #2c3e50;
        }
    </style>
</body>
</html>