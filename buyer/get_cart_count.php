
<?php
session_start();
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
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$user_id = $_SESSION["user_id"];
$sql = "SELECT SUM(quantity) AS total_items FROM Cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $cart_count = $row["total_items"] ?? 0;
} else {
    $cart_count = 0;
}

echo json_encode(["success" => true, "cart_count" => $cart_count]);
?>