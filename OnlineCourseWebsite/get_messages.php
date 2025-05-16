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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$user_email = $_SESSION['user_email'];
$chat_with = isset($_GET['chat_with']) ? $conn->real_escape_string($_GET['chat_with']) : null;

if (!$chat_with) {
    die(json_encode(['error' => 'Missing chat_with parameter']));
}

// Fetch messages
$sql = "
    SELECT message_id, text, uploads, deliver_date, delivery_status, 
           is_edited, edited_date, receiver_email, sender_email,
           reply_to_message_id, replied_content, replied_uploads
    FROM message
    WHERE (sender_email = ? AND receiver_email = ?)
       OR (sender_email = ? AND receiver_email = ?)
    ORDER BY deliver_date ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("ssss", $user_email, $chat_with, $chat_with, $user_email);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);

$stmt->close();
$conn->close();
?>