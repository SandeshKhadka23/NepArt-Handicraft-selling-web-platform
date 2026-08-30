<?php 
session_start(); 
include "../includes/db.php"; 

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch product details for editing
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $query = "SELECT * FROM Product WHERE product_id = ? AND artisan_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        echo "Product not found!";
        exit();
    }
} else {
    echo "No product ID specified!";
    exit();
}

// Fetch categories for the dropdown
$categoryQuery = "SELECT * FROM Category";
$categoryResult = $conn->query($categoryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        button {
            background-color: #b91111;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #b91111;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #333;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            input[type="text"],
            input[type="number"],
            textarea,
            select {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>

        <form action="process_update_product.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

            <div class="form-group">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" 
                    value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" step="0.01" name="price" 
                    value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" 
                    required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" 
                    value="<?php echo $product['stock']; ?>" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php while ($row = $categoryResult->fetch_assoc()) { ?>
                        <option value="<?php echo $row['category_id']; ?>" 
                            <?php if ($row['category_id'] == $product['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit">Update Product</button>
        </form>

        <a href="artisan_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>