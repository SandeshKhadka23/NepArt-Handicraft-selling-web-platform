<?php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default is empty in XAMPP
$database = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
