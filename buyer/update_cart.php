<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';
    $cart_id = $_POST["cart_id"] ?? 0;
    
    switch($action) {
        case 'update':
            handleUpdate($conn, $cart_id, $_POST["change"] ?? 0, $_SESSION["user_id"]);
            break;
        case 'remove':
            handleRemove($conn, $cart_id, $_SESSION["user_id"]);
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid action"]);
    }
}

function handleUpdate($conn, $cart_id, $change, $user_id) {
    try {
        // First, get current quantities
        $stmt = $conn->prepare("SELECT c.quantity, c.product_id, p.stock 
                               FROM Cart c 
                               JOIN Product p ON c.product_id = p.product_id 
                               WHERE c.cart_id = ? AND c.user_id = ?");
        
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $current_quantity = $row["quantity"];
            $new_quantity = $current_quantity + $change;
            $current_stock = $row["stock"];
            
            // Validate new quantity
            if ($new_quantity <= 0) {
                echo json_encode(["success" => false, "message" => "Quantity cannot be less than 1"]);
                return;
            }
            
            if ($change > 0 && $new_quantity > ($current_stock + $current_quantity)) {
                echo json_encode(["success" => false, "message" => "Not enough stock available"]);
                return;
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Update cart quantity
            $update_cart = $conn->prepare("UPDATE Cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            if (!$update_cart) {
                throw new Exception($conn->error);
            }
            $update_cart->bind_param("iii", $new_quantity, $cart_id, $user_id);
            $update_cart->execute();
            
            // Update product stock
            $new_stock = $current_stock - $change;
            $update_stock = $conn->prepare("UPDATE Product SET stock = ? WHERE product_id = ?");
            if (!$update_stock) {
                throw new Exception($conn->error);
            }
            $update_stock->bind_param("ii", $new_stock, $row["product_id"]);
            $update_stock->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Get updated cart count
            $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM Cart WHERE user_id = ?");
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $cart_count = $count_result->fetch_assoc()["count"];
            
            echo json_encode([
                "success" => true,
                "message" => "Cart updated successfully",
                "cartCount" => $cart_count,
                "newQuantity" => $new_quantity
            ]);
            
        } else {
            echo json_encode(["success" => false, "message" => "Cart item not found"]);
        }
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleRemove($conn, $cart_id, $user_id) {
    try {
        // Get item details before removal
        $stmt = $conn->prepare("SELECT c.quantity, c.product_id, p.stock 
                               FROM Cart c 
                               JOIN Product p ON c.product_id = p.product_id 
                               WHERE c.cart_id = ? AND c.user_id = ?");
        
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Begin transaction
            $conn->begin_transaction();
            
            // Update product stock (add back the quantity)
            $new_stock = $row["stock"] + $row["quantity"];
            $update_stock = $conn->prepare("UPDATE Product SET stock = ? WHERE product_id = ?");
            if (!$update_stock) {
                throw new Exception($conn->error);
            }
            $update_stock->bind_param("ii", $new_stock, $row["product_id"]);
            $update_stock->execute();
            
            // Remove cart item
            $delete_cart = $conn->prepare("DELETE FROM Cart WHERE cart_id = ? AND user_id = ?");
            if (!$delete_cart) {
                throw new Exception($conn->error);
            }
            $delete_cart->bind_param("ii", $cart_id, $user_id);
            $delete_cart->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Get updated cart count
            $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM Cart WHERE user_id = ?");
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $cart_count = $count_result->fetch_assoc()["count"];
            
            echo json_encode([
                "success" => true,
                "message" => "Item removed successfully",
                "cartCount" => $cart_count
            ]);
            
        } else {
            echo json_encode(["success" => false, "message" => "Cart item not found"]);
        }
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

$conn->close();
?>