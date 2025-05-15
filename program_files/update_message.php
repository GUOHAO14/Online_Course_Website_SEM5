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
$json = file_get_contents('php://input');
$input = json_decode($json, true);

// Better error handling for JSON parsing
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate input
if (!isset($input['message_id'], $input['new_text'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (!is_numeric($input['message_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
    exit;
}

$message_id = (int)$input['message_id'];
$new_text = trim($input['new_text']);

if (empty($new_text)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message text cannot be empty']);
    exit;
}

try {
    // Verify that the message belongs to the logged-in user AND get the current uploads path
    $stmt = $pdo->prepare("SELECT sender_email, uploads FROM message WHERE message_id = :message_id");
    $stmt->execute([':message_id' => $message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }

    if ($message['sender_email'] !== $user_email) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized - you can only edit your own messages']);
        exit;
    }

    // Update the message text and set is_edited flag (preserving uploads)
    $updateStmt = $pdo->prepare("UPDATE message 
        SET text = :new_text, 
            is_edited = 1, 
            edited_date = NOW() 
        WHERE message_id = :message_id");
    $updateStmt->execute([
        ':new_text' => $new_text,
        ':message_id' => $message_id
    ]);

    // Get the updated message with the edited_date
    $updated = $pdo->prepare("SELECT edited_date, uploads FROM message WHERE message_id = :message_id");
    $updated->execute([':message_id' => $message_id]);
    $result = $updated->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'edited_date' => $result['edited_date'],
        'uploads' => $result['uploads'] // Include the preserved uploads path
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>