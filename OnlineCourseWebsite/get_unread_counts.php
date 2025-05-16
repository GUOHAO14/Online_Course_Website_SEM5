<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_email = $_SESSION['user_email'];

try {
    $stmt = $pdo->prepare("
        SELECT sender_email, COUNT(*) AS unread_count
        FROM message
        WHERE receiver_email = :user_email
          AND delivery_status = 'sent'
        GROUP BY sender_email
    ");
    $stmt->execute([':user_email' => $user_email]);
    $unreadCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['unreadCounts' => $unreadCounts]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch unread counts']);
}
?>
