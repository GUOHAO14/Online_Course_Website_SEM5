<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Query to get all admins
$sql = "SELECT name, user_email, role, pfp FROM user WHERE LOWER(role) = 'admin'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
    // Pick a random admin
    $randomAdmin = $admins[array_rand($admins)];
    echo json_encode(['success' => true, 'admin' => $randomAdmin]);
} else {
    echo json_encode(['success' => false, 'error' => 'No admin found']);
}

$conn->close();
?>
