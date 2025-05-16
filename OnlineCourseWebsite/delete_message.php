<?php
session_start();

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

if (!isset($input['message_id']) || !is_numeric($input['message_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$message_id = (int)$input['message_id'];

try {
    // Verify that the message belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT sender_email FROM message WHERE message_id = :message_id");
    $stmt->execute([':message_id' => $message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }

    if ($message['sender_email'] !== $user_email) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    // Delete the message
    $deleteStmt = $pdo->prepare("DELETE FROM message WHERE message_id = :message_id");
    $deleteStmt->execute([':message_id' => $message_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete message']);
}
?>
