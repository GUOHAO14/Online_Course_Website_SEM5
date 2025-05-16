<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

require_once 'db_config.php'; 

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if this is a file upload request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get form data
$senderEmail = $_POST['sender_email'] ?? '';
$receiverEmail = $_POST['receiver_email'] ?? '';
$file = $_FILES['file'];

// Validate inputs
if (empty($senderEmail) || empty($receiverEmail)) {
    echo json_encode(['error' => 'Missing sender or receiver email']);
    exit;
}

// File validation
$maxSize = 2 * 1024 * 1024; // 2MB
$allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'File too large (max 2MB)']);
    exit;
}

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'File type not allowed']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$destination = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['error' => 'File upload failed']);
    exit;
}

// Save to database
$relativePath = 'uploads/' . $filename;
$messageText = '[File: ' . $file['name'] . ']';

try {
    $stmt = $conn->prepare("INSERT INTO message (sender_email, receiver_email, text, file_path, deliver_date, delivery_status) 
                           VALUES (?, ?, ?, ?, NOW(), 'sent')");
    $stmt->bind_param("ssss", $senderEmail, $receiverEmail, $messageText, $relativePath);
    
    if (!$stmt->execute()) {
        unlink($destination); // Delete the file if DB insert fails
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'file_path' => $relativePath,
        'file_name' => $file['name']
    ]);
    
} catch (Exception $e) {
    unlink($destination); // Clean up file if error occurs
    echo json_encode(['error' => 'Database exception: ' . $e->getMessage()]);
}
?>