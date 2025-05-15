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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get parameters
$logged_in_email = isset($_GET['user_email']) ? $_GET['user_email'] : null;
$chat_with_email = isset($_GET['chat_with']) ? $_GET['chat_with'] : null;

if (!$logged_in_email || !$chat_with_email) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Fetch last message text between logged_in_email and chat_with_email
$stmt = $conn->prepare("
    SELECT text FROM message
    WHERE (sender_email = ? AND receiver_email = ?)
       OR (sender_email = ? AND receiver_email = ?)
    ORDER BY deliver_date DESC
    LIMIT 1
");
$stmt->bind_param("ssss", $logged_in_email, $chat_with_email, $chat_with_email, $logged_in_email);
$stmt->execute();
$stmt->bind_result($lastMessage);
$stmt->fetch();
$stmt->close();

echo json_encode(['last_message' => $lastMessage ? $lastMessage : '']);

$conn->close();
?>
