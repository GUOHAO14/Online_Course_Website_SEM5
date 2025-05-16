<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'capstone';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Get logged-in user info from session
    if (!isset($_SESSION['user_email'])) {
        throw new Exception("User not logged in");
    }
    $reporter_email = $_SESSION['user_email'];

    // Get raw POST data
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception("No data received");
    }

    // Decode JSON data
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    $required = ['message_id', 'reason', 'reported_by'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize inputs
    $message_id = intval($data['message_id']);
    $reason = trim($conn->real_escape_string($data['reason']));
    $reported_by = trim($conn->real_escape_string($data['reported_by']));

    if ($message_id <= 0) {
        throw new Exception("Invalid message ID");
    }

    // Check if the message exists
    $stmt = $conn->prepare("SELECT message_id FROM message WHERE message_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $message_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception("Message not found");
    }
    $stmt->close();

    // Check if this message has already been reported by this user
    $stmt = $conn->prepare("SELECT report_id FROM message_report WHERE message_id = ? AND reported_by = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("is", $message_id, $reporter_email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        throw new Exception("You have already reported this message");
    }
    $stmt->close();

    // Insert the report - using your exact column names
    $stmt = $conn->prepare("INSERT INTO message_report 
                          (message_id, reported_by, reported, report_reason, reported_date) 
                          VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Note the parameter order matches your schema:
    // 1. message_id
    // 2. reported_by (the reporter)
    // 3. reported (the person being reported)
    // 4. report_reason
    $stmt->bind_param("isss", $message_id, $reporter_email, $reported_by, $reason);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Failed to submit report: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>