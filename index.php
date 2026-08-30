<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Searchbar functionality
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
    <link rel="stylesheet" href="assets/css/styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .about .slider-container .about-content .buynowbttn {
            background-color: #b91111;
            height: 32px;
            width: 127px;
            border-radius: 20px;
            border: none;
            color: white;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-left">
            <img src="assets/static_pictures/artisan/download.png" alt="Logo" class="logo">
            <span class="brand-name">NepArt Creations</span>
        </div>
        <div class="nav-right">
            <!-- Updated Search Form -->
            <form id="search-form" method="GET" action="#products-section">
                <input type="text" name="search" placeholder="Search products..." class="search-bar" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
            <select class="category-select" id="category-select" onchange="filterAndScrollToProducts()">
                <option value="">Search By Category</option>
                <?php
                if ($result_categories->num_rows > 0) {
                    while ($row = $result_categories->fetch_assoc()) {
                        echo '<option value="' . $row["category_id"] . '">' . $row["category_name"] . '</option>';
                    }
                }
                ?>
            </select>
            <a href="#" onclick="showLoginAlert('cart')" class="nav-link cart-link">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="auth/login.php" class="nav-link">Login</a>
            <a href="auth/register.php" class="nav-link">Register</a>
        </div>
    </div>
</nav>

<section class="about">
    <div class="slider-container">
        <div class="about-content">
            <h1>Welcome to NepArt Creations</h1>
            <p><i>✨ "Handmade Treasures, One Click Away." ✨</i></p>
            <br><br>
            <p>Every craft embodies Nepal’s tradition, passion, and creativity.</p>
            <p>Celebrate artistry with handmade products that connect cultures and generations.</p>
            <button class="buynowbttn" onclick="showLoginAlert('buynowbttn')">Buy Now</button>
        </div>
    </div>
</section>

<section class="products" id="products-section">
    <h1>Featured Products</h1>
    <div class="product-container" id="product-container">
        <?php if ($result_products->num_rows > 0): ?>
            <?php while ($row = $result_products->fetch_assoc()): ?>
                <div class="product-item" data-category-id="<?php echo $row['category_id']; ?>">
                    <img src="assets/dynamic_pictures/products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                    <h2><?php echo htmlspecialchars($row["product_name"]); ?></h2>
                    <p class="price">Price: Rs. <?php echo number_format($row["price"], 2); ?></p>
                    <div class="quantity-selector">
                        <button class="qty-btn minus" onclick="updateQuantity(this, -1)">-</button>
                        <input type="number" class="qty-input" value="1" min="1" max="<?php echo $row['stock']; ?>" readonly>
                        <button class="qty-btn plus" onclick="updateQuantity(this, 1)">+</button>
                    </div>
                    <div class="product-details" style="display: none;">
                        <p><?php echo htmlspecialchars($row["description"]); ?></p>
                        <p>Stock: <?php echo $row["stock"]; ?></p>
                        <p>Category: <?php echo htmlspecialchars($row["category_name"]); ?></p>
                    </div>
                    <div class="button-container">
                        <button class="details-btn" onclick="toggleDetails(this)">Show Details</button>
                        <button class="cart-btn" onclick="showLoginAlert('cart')">Add to Cart</button>
                        <button class="buy-now-btn" onclick="showLoginAlert('buy')">Buy Now</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-products"><?php echo empty($search_query) ? 'No products available.' : 'No results found for your search.'; ?></p>
        <?php endif; ?>
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
        <p>&copy; 2025  NepArt Creations. All rights reserved.</p>
    </div>
</footer>

<script>
    // Custom Alert CSS
    const css = `
    .custom-alert {
        display: none;
        position: fixed;
        left: 50%;
        top: 10%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        min-width: 300px;
    }

    .custom-alert-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    .custom-alert-buttons {
        margin-top: 20px;
        text-align: right;
    }

    .custom-alert-buttons button {
        padding: 8px 16px;
        margin-left: 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .custom-alert-buttons .login-btn {
        background: #b91111;
        color: white;
        border: none;
    }

    .custom-alert-buttons .cancel-btn {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    `;

    // Add the CSS to the document
    const styleSheet = document.createElement("style");
    styleSheet.innerText = css;
    document.head.appendChild(styleSheet);

    // Create and append alert HTML
    const alertHTML = `
    <div class="custom-alert-overlay" id="alertOverlay"></div>
    <div class="custom-alert" id="customAlert">
        <div id="alertMessage"></div>
        <div class="custom-alert-buttons">
            <button class="cancel-btn" onclick="closeCustomAlert()">Cancel</button>
            <button class="login-btn" onclick="redirectToLogin()">Login</button>
        </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHTML);

    // Helper functions
    function closeCustomAlert() {
        document.getElementById('customAlert').style.display = 'none';
        document.getElementById('alertOverlay').style.display = 'none';
    }

    function redirectToLogin() {
        window.location.href = 'auth/login.php';
    }

    // Modified showLoginAlert function
    function showLoginAlert(action) {
        let message = '';
        switch (action) {
            case 'cart':
                message = 'Please login or register to add items to cart.';
                break;
            case 'buy':
                message = 'Please login or register to purchase items.';
                break;
            case 'track':
                message = 'Please login or register to track orders.';
                break;
            case 'buynowbttn':
                message = 'Please login or register to purchase items.';
                break;
            default:
                message = 'Please login or register to continue.';
        }

        // Show custom alert
        document.getElementById('alertMessage').textContent = message;
        document.getElementById('customAlert').style.display = 'block';
        document.getElementById('alertOverlay').style.display = 'block';
    }

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