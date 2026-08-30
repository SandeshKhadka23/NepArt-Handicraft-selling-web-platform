<?php
session_start();
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

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql_products = "SELECT p.product_id, p.product_name, p.price, p.description, p.stock, p.image, p.category_id, c.category_name 
                 FROM Product p 
                 JOIN Category c ON p.category_id = c.category_id 
                 WHERE p.stock > 0";

if (!empty($search_query)) {
    $search_query = "" . $search_query . ""; // Wildcard for partial matches
    $sql_products .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
}

$stmt_products = $conn->prepare($sql_products);

if (!empty($search_query)) {
    $stmt_products->bind_param("ss", $search_query, $search_query);
}

$stmt_products->execute();
$result_products = $stmt_products->get_result();

// Fetch categories
$sql_categories = "SELECT * FROM Category";
$result_categories = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-left">
            <img src="../assets/static_pictures/artisan/download.png" alt="Logo" class="logo">
            <span class="brand-name">NepArt Creations</span>
        </div>
        <div class="nav-right">
            <!-- Search Form -->
            <form id="search-form" method="GET" action="#products-section">
                <input type="text" name="search" placeholder="Search products..." class="search-bar" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
            <select class="category-select" id="category-select" onchange="filterAndScrollToProducts()">
                <option value="">Search By Category</option>
                <?php
                if ($result_categories->num_rows > 0) {
                    while($row = $result_categories->fetch_assoc()) {
                        echo '<option value="' . $row["category_id"] . '">' . $row["category_name"] . '</option>';
                    }
                }
                ?>
            </select>
            <a href="cart.php" class="nav-link cart-link">
                <i class="fas fa-shopping-cart"></i> Cart
                <span class="cart-badge" id="cart-count">0</span>
            </a>
            <a href="track_order.php" class="nav-link">Track Orders</a>
            <a href="../auth/logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</nav>
<section class="about">
    <div class="slider-container">
        <div class="about-content">
            <h1>Welcome to NepArt Creations</h1>
            <p><i>✨ "Handmade Treasures, One Click Away." ✨</i></p>
            <br><br>
            <p>Find and Purchase Your Favorite Handicrafts at the Best Prices.</p>
        </div>
    </div>
</section>
<section class="products" id="products-section">
    <h1>Featured Products</h1>
    <div class="product-container" id="product-container">
        <?php
        if ($result_products->num_rows > 0) {
            while($row = $result_products->fetch_assoc()) {
                echo '<div class="product-item" data-category-id="' . $row["category_id"] . '">';
                echo '<img src="../assets/dynamic_pictures/products/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="product-image">';
                echo '<h2>' . htmlspecialchars($row["product_name"]) . '</h2>';
                
                echo '<p class="price">Price: Rs. ' . number_format($row["price"], 2) . '</p>';
                
                echo '<div class="quantity-selector">';
                echo '<button class="qty-btn minus" onclick="updateQuantity(this, -1)">-</button>';
                echo '<input type="number" class="qty-input" value="1" min="1" max="' . $row["stock"] . '" readonly>';
                echo '<button class="qty-btn plus" onclick="updateQuantity(this, 1)">+</button>';
                echo '</div>';
                
                echo '<div class="product-details" style="display: none;">';
                echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
                echo '<p>Stock: ' . $row["stock"] . '</p>';
                echo '<p>Category: ' . htmlspecialchars($row["category_name"]) . '</p>';
                echo '</div>';
                
                echo '<div class="button-container">';
                echo '<button class="details-btn" onclick="toggleDetails(this)">Show Details</button>';
                echo '<button class="cart-btn" onclick="addToCart(this)" data-product-id="' . $row["product_id"] . '">Add to Cart</button>';
                echo '<button class="buy-now-btn" onclick="buyNow(this)" data-product-id="' . $row["product_id"] . '">Buy Now</button>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-products">' . (empty($search_query) ? 'No products available.' : 'No results found for your search.') . '</p>';
        }
        ?>
    </div>
</section>
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
            <a href="cart.php">Cart</a><br>
            <a href="track_order.php">Track Orders</a><br>
            <a href="#products-section">See Products</a>
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
function updateQuantity(button, change) {
    const qtyInput = button.parentElement.querySelector('.qty-input');
    let currentQty = parseInt(qtyInput.value);
    const maxQty = parseInt(qtyInput.max);
    if (change === 1 && currentQty < maxQty) {
        currentQty++;
    } else if (change === -1 && currentQty > 1) {
        currentQty--;
    }
    qtyInput.value = currentQty;
}

function toggleDetails(button) {
    const productItem = button.closest('.product-item');
    const details = productItem.querySelector('.product-details');
    details.style.display = details.style.display === 'none' ? 'block' : 'none';
    button.textContent = details.style.display === 'block' ? 'Hide Details' : 'Show Details';
}

function addToCart(button) {
    const productItem = button.closest('.product-item');
    const productId = button.getAttribute('data-product-id');
    const qtyInput = productItem.querySelector('.qty-input');
    const quantity = parseInt(qtyInput.value);
    // Show loading state
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = 'Adding...';
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count with the actual count from server
            updateCartCount();
            // Show success message
            alert(`${quantity} x ${data.product_name} added to cart.`);
        } else {
            alert(data.message || 'Error adding to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart');
    })
    .finally(() => {
        // Reset button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Function to update cart count dynamically
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

function buyNow(button) {
    const productItem = button.closest('.product-item');
    const productId = button.getAttribute('data-product-id');
    const qtyInput = productItem.querySelector('.qty-input');
    const quantity = parseInt(qtyInput.value);
    // Redirect to the checkout page with the product ID and quantity
    window.location.href = `checkout.php?product_id=${productId}&quantity=${quantity}`;
}

function filterAndScrollToProducts() {
    const selectedCategory = document.getElementById('category-select').value;
    const productItems = document.querySelectorAll('.product-item');
    productItems.forEach(item => {
        if (selectedCategory === "" || item.getAttribute('data-category-id') === selectedCategory) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    document.getElementById('products-section').scrollIntoView({ behavior: 'smooth' });
}
</script>
</body>
</html>