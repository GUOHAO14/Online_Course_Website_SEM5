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
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_email = $_SESSION['user_email'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['status']) || !in_array($input['status'], ['delivered', 'read'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

$status = $input['status'];

// Bulk update by sender and receiver emails
if (isset($input['chat_with']) && is_string($input['chat_with'])) {
    $chat_with = $input['chat_with'];

    try {
        $stmt = $pdo->prepare("
            UPDATE message
            SET delivery_status = :status
            WHERE sender_email = :chat_with
              AND receiver_email = :user_email
              AND delivery_status != :status
        ");
        $stmt->execute([
            ':status' => $status,
            ':chat_with' => $chat_with,
            ':user_email' => $user_email
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update message status']);
    }
    exit;
}

// Fallback: update by message_id if provided
if (isset($input['message_id'])) {
    $message_id = $input['message_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE message
            SET delivery_status = :status
            WHERE message_id = :message_id
              AND receiver_email = :user_email
              AND delivery_status != :status
        ");
        $stmt->execute([
            ':status' => $status,
            ':message_id' => $message_id,
            ':user_email' => $user_email
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update message status']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid input']);
?>
