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

// Initialize variables
$cart_items = [];
$total = 0;

// Check if the user is coming from "Buy Now"
if (isset($_GET['product_id']) && isset($_GET['quantity'])) {
    $product_id = $_GET['product_id'];
    $quantity = $_GET['quantity'];

    // Fetch the product details
    $sql_product = "SELECT product_id, product_name, price, image FROM Product WHERE product_id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();

    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();
        $product['quantity'] = $quantity;
        $cart_items[] = $product;
        $total += $product['price'] * $quantity;
    }
} else {
    // Fetch cart items and total
    $user_id = $_SESSION["user_id"];
    $sql_cart = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image 
                 FROM Cart c 
                 JOIN Product p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    while ($row = $result_cart->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row["price"] * $row["quantity"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/checkout.css">
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
                <a href="track_order.php" class="nav-link">Track Orders</a>
            </div>
        </div>
    </nav>
    <div id="response-message"></div>

    <div class="checkout-container">
        <div class="checkout-form">
            <h1>Checkout</h1>
            <form id="checkoutForm" onsubmit="placeOrder(event)">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="street_address">Street Address *</label>
                    <input type="text" id="street_address" name="street_address" required>
                </div>

                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="district">District *</label>
                    <input type="text" id="district" name="district" required>
                </div>

                <div class="form-group">
                    <label for="special_instructions">Special Instructions (Optional)</label>
                    <textarea id="special_instructions" name="special_instructions"></textarea>
                </div>

                <div class="payment-method">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked>
                        Cash on Delivery
                    </label>
                    <p class="payment-info">Pay when you receive your items.</p>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php
                foreach ($cart_items as $item) {
                    $subtotal = $item["price"] * $item["quantity"];
                    ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['product_name']); ?> (×<?php echo $item['quantity']; ?>)</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="total-line">
                <span>Total Amount:</span>
                <span>Rs. <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
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
                <a href="trackorders.php">Track Orders</a><br>
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

    <script>
    function showMessage(message, isSuccess = true) {
        const messageElement = document.getElementById('response-message');
        messageElement.textContent = message;
        messageElement.className = isSuccess ? 'success-message' : 'error-message';
        messageElement.style.display = 'block';
        
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 3000);
    }

    function placeOrder(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('checkoutForm'));

    // Add product_id and quantity for "Buy Now" orders
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');
    const quantity = urlParams.get('quantity');

    if (productId && quantity) {
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
    }

    fetch('place_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Order placed successfully!');
            setTimeout(() => {
                window.location.href = 'track_order.php?order_id=' + data.order_id;
            }, 2000);
        } else {
            showMessage(data.message || 'Error placing order', false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error placing order', false);
    });
}
    </script>
</body>
</html>