<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

$artisan_id = $_SESSION["user_id"];
$query = "SELECT p.*, c.category_name FROM Product p JOIN Category c ON p.category_id = c.category_id WHERE p.artisan_id = ?";
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
    <title>Artisan Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo">
            <img src="download.png" alt="Logo" class="logo">
            <span class="brand-name">NepArt Creations</span>
        </div>
        <div class="nav-links">
             <a href="view_orders.php" class="nav-button">Manage Orders</a>
            <a href="add_product.php" class="nav-button"><i class="fas fa-plus"></i> Add Product</a>
            <a href="../auth/logout.php" class="nav-button"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="about">
        <div class="slider-container">
            <div class="about-content">
                <h1>Welcome to Your Artisan Dashboard</h1>
                <p><i>✨ "Handmade Treasures, One Click Away." ✨</i></p>
                <br><br>
                <p>Showcase your handcrafted masterpieces to the world. Create, manage, and grow your artisan business with our platform.</p>
            </div>
        </div>
    </div>

    <section class="products-section">
        <h2>Your Products</h2>
        <div class="products-container">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <?php if ($row['image']) { ?>
                            <img src="../assets/dynamic_pictures/products/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" class="product-image">
                        <?php } else { ?>
                            <div class="no-image">No image available</div>
                        <?php } ?>
                    </div>
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <div class="price-stock">
                            <span class="price">NRS <?php echo number_format($row['price']); ?></span>
                            <span class="stock">Stock: <?php echo $row['stock']; ?></span>
                        </div>
                        <div class="category-tag">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['category_name']); ?>
                        </div>
                        
                        <button class="toggle-description" onclick="toggleDescription(<?php echo $row['product_id']; ?>)">
                            <i class="fas fa-info-circle"></i> Show Details
                        </button>
                        
                        <div id="description-<?php echo $row['product_id']; ?>" class="description" style="display:none;">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </div>

                        <div class="product-actions">
                            <a href="update_product.php?product_id=<?php echo $row['product_id']; ?>" class="edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_product.php?product_id=<?php echo $row['product_id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this product?')"
                               class="delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="add_product.php">Add Product</a>
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

    <script>
        function toggleDescription(productId) {
            const description = document.getElementById('description-' + productId);
            const button = description.previousElementSibling;
            
            if (description.style.display === 'none') {
                description.style.display = 'block';
                button.innerHTML = '<i class="fas fa-info-circle"></i> Hide Details';
            } else {
                description.style.display = 'none';
                button.innerHTML = '<i class="fas fa-info-circle"></i> Show Details';
            }
        }
    </script>

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
            font-size: 1 rem;
            color:#b91111;
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
            background-color:  #b91111;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #b91111;
        }

        /* About Section with Sliding Background */
        .about {
            margin-top: 60px;
            height: 600px;
            position: relative;
            overflow: hidden;
        }

        .slider-container {
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('/assets/slide1.jpg');
            background-size: cover;
            animation: slideBackground 20s infinite linear;
        }

        @keyframes slideBackground {
            0%, 33% {
                background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                                 url('buddha.jpg');
            }
            34%, 66% {
                background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                                 url('metal craft.webp');
            }
            67%, 100% {
                background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                                 url('thangka painting.webp');
            }
        }

        .about-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #ffffff;
            width: 80%;
            max-width: 800px;
        }

        .about-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .about-content p {
            font-size: 1.2rem;
            line-height: 1.6;
        }

        /* Products Section */
        .products-section {
            padding: 2rem;
            margin-top: 2rem;
        }

        .products-section h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .product-card {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image-container {
            height: 200px;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f1f1;
            color: #666;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-details h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .price-stock {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .price {
            color: #e74c3c;
            font-weight: bold;
        }

        .stock {
            color: #7f8c8d;
        }

        .category-tag {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: #f1f1f1;
            border-radius: 15px;
            margin-bottom: 1rem;
            color: #666;
        }

        .toggle-description {
            width: 100%;
            padding: 0.5rem;
            background: #b91111;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .description {
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }

        .edit-btn, .delete-btn {
            flex: 1;
            padding: 0.5rem;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .edit-btn {
           
            color: black;
        }

        .delete-btn {
          
            color: Black;
        }

        .edit-btn:hover {
            color:rgb(1, 5, 51);
        }

        .delete-btn:hover {
            color:rgb(1, 5, 51);
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
            color:white;
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

            .about-content h1 {
                font-size: 2rem;
            }

            .about-content p {
                font-size: 1rem;
            }

            .products-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>