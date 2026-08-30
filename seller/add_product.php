<?php 
session_start(); 
include "../includes/db.php"; 

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

$categoryQuery = "SELECT * FROM Category";
$categoryResult = $conn->query($categoryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - NepArt Creations</title>
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

    <div class="product-header">
        <div class="header-content">
            <h1>Add New Product</h1>
            <p>Share your craftsmanship with the world</p>
        </div>
    </div>

    <section class="form-section">
        <div class="form-container">
            <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="product_name">
                            <i class="fas fa-tag"></i> Product Name
                        </label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label for="price">
                            <i class="fas fa-dollar-sign"></i> Price (NRS)
                        </label>
                        <input type="number" id="price" step="0.01" name="price" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="stock">
                            <i class="fas fa-boxes"></i> Stock
                        </label>
                        <input type="number" id="stock" name="stock" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">
                            <i class="fas fa-folder"></i> Category
                        </label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select a Category</option>
                            <?php while ($row = $categoryResult->fetch_assoc()) { ?>
                                <option value="<?php echo $row['category_id']; ?>">
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="product_image">
                            <i class="fas fa-image"></i> Product Image
                        </label>
                        <div class="file-input-container">
                            <input type="file" id="product_image" name="product_image" accept="image/*">
                            <p class="file-input-help">Upload a high-quality image of your product</p>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </button>
                    <a href="artisan_dashboard.php" class="cancel-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
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
                <a href="artisan_dashboard.php">Dashboard</a>
                <a href="view_orders.php">Orders</a>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-envelope"></i> support@artisancraft.com</p>
                <p><i class="fas fa-phone"></i> +977-1234567890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 ArtisanCraft. All rights reserved.</p>
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

        /* Product Header */
        .product-header {
            margin-top: 60px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('metal craft.webp');
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: bold;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #b91111;
            outline: none;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .file-input-container {
            border: 2px dashed #ddd;
            padding: 1.5rem;
            border-radius: 5px;
            text-align: center;
        }

        .file-input-help {
            color: #666;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
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
        }

        .submit-btn {
            background-color: #b91111;
            color: white;
            border: none;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #8b0000;
        }

        .cancel-btn {
            background-color: #f1f1f1;
            color: #333;
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

            .product-header {
                padding: 2rem 1rem;
            }

            .product-header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .submit-btn, .cancel-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</body>
</html>