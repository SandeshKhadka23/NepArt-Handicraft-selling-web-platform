<?php
// cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cart items
$user_id = $_SESSION["user_id"];
$sql_cart = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image, p.stock 
             FROM Cart c 
             JOIN Product p ON c.product_id = p.product_id 
             WHERE c.user_id = ?";
             
$stmt = $conn->prepare($sql_cart);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_cart = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .item-details {
            flex: 1;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            padding: 5px 10px;
            border: none;
            background: #4a90e2;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-btn {
            padding: 8px 16px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-summary {
            margin-top: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
            text-decoration: none;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
        }

        #response-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }

        .success-message {
            background-color: #28a745;
            color: white;
        }

        .error-message {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div id="response-message"></div>
    
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
                    <span class="cart-badge" id="cart-count">
                        <?php echo $result_cart->num_rows; ?>
                    </span>
                </a>
                <a href="track_order.php" class="nav-link">Track Orders</a>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <h1>Shopping Cart</h1>
        
        <?php
        if ($result_cart->num_rows > 0) {
            $total = 0;
            while($row = $result_cart->fetch_assoc()) {
                $subtotal = $row["price"] * $row["quantity"];
                $total += $subtotal;
                ?>
                <div class="cart-item" data-cart-id="<?php echo $row['cart_id']; ?>">
                    <img src="../assets/dynamic_pictures/products/<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>Price: Rs. <?php echo number_format($row['price'], 2); ?></p>
                        <p>Subtotal: Rs. <span class="subtotal"><?php echo number_format($subtotal, 2); ?></span></p>
                    </div>
                    
                    <div class="item-actions">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $row['cart_id']; ?>, -1, <?php echo $row['stock']; ?>)">-</button>
                            <span class="quantity"><?php echo $row['quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $row['cart_id']; ?>, 1, <?php echo $row['stock']; ?>)">+</button>
                        </div>
                        <button class="remove-btn" onclick="removeCartItem(<?php echo $row['cart_id']; ?>)">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <p>Total Items: <span id="total-items"><?php echo $result_cart->num_rows; ?></span></p>
                <p>Total Amount: Rs. <span id="total-amount"><?php echo number_format($total, 2); ?></span></p>
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    Proceed to Checkout
                </button>
            </div>
            <?php
        } else {
            ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-3x"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to your cart and they will appear here</p>
                <a href="buyer_dashboard.php" class="checkout-btn">Continue Shopping</a>
            </div>
            <?php
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

    <script>
        
        function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountElement = document.getElementById('cart-count');
                cartCountElement.textContent = data.cart_count;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Call updateCartCount when the page loads
document.addEventListener('DOMContentLoaded', updateCartCount);
    function showMessage(message, isSuccess = true) {
        const messageElement = document.getElementById('response-message');
        messageElement.textContent = message;
        messageElement.className = isSuccess ? 'success-message' : 'error-message';
        messageElement.style.display = 'block';
        
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 3000);
    }

    function updateCartQuantity(cartId, change, maxStock) {
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('change', change);
    formData.append('action', 'update');

    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
            const quantityElement = cartItem.querySelector('.quantity');
            const cartCountElement = document.getElementById('cart-count');
            
              // Update cart count after quantity change
              updateCartCount();
            // Reload the page to reflect changes
            location.reload();
            
            // Update quantity
            if (quantityElement && data.newQuantity !== undefined) {
                quantityElement.textContent = data.newQuantity;
            }
            
            // Reload page to update all calculations
            location.reload();
            showMessage(data.message || 'Cart updated successfully');
        } else {
            showMessage(data.message || 'Error updating cart', false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error updating cart', false);
    });
}

function removeCartItem(cartId) {
    if (confirm('Are you sure you want to remove this item?')) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('action', 'remove');

        fetch('update_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                 // Update cart count after quantity change
            updateCartCount();
            // Reload the page to reflect changes
            location.reload();
                location.reload();
                showMessage(data.message || 'Item removed successfully');
            } else {
                showMessage(data.message || 'Error removing item', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error removing item', false);
        });
    }
}

    function proceedToCheckout() {
        window.location.href = 'checkout.php';
    }
    </script>
</body>
</html>

<?php
// update_cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (isset($_POST['cart_id']) && isset($_POST['change'])) {
    $cart_id = intval($_POST['cart_id']);
    $change = intval($_POST['change']);
    
    // First get current quantity and check stock
    $stmt = $conn->prepare("SELECT c.quantity, p.stock, c.product_id FROM Cart c JOIN Product p ON c.product_id = p.product_id WHERE c.cart_id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $cart_id, $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $change;
        
        if ($new_quantity > 0 && $new_quantity <= $row['stock']) {
            $update_stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $update_stmt->bind_param("iii", $new_quantity, $cart_id, $_SESSION["user_id"]);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    }
// } else {
//     echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>

<?php
// remove_from_cart.php (continued)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);
    
    // First verify the cart item belongs to the user
    $verify_stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE cart_id = ? AND user_id = ?");
    $verify_stmt->bind_param("ii", $cart_id, $_SESSION["user_id"]);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        // Proceed with deletion
        $delete_stmt = $conn->prepare("DELETE FROM Cart WHERE cart_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $_SESSION["user_id"]);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Item removed successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No items were removed'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error removing item: ' . $conn->error
            ]);
        }
        $delete_stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found in your cart'
        ]);
    }
    $verify_stmt->close();
}
// } else {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Invalid parameters: cart_id is required'
//     ]);
// }

$conn->close();
?>