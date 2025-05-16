<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$replyId = filter_var($data['reply_id'] ?? null, FILTER_VALIDATE_INT);
$categories = $data['report_categories'] ?? [];
$reason = filter_var($data['reason'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$replyId || empty($categories)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user already reported this reply
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM reply_reports WHERE reply_id = :reply_id AND reporter_id = :reporter_id");
    $checkStmt->execute([
        ':reply_id' => $replyId,
        ':reporter_id' => $_SESSION['user_email']
    ]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'You have already reported this reply.']);
        exit;
    }
    
    // Insert report into database
    $stmt = $pdo->prepare("INSERT INTO reply_reports (reply_id, reporter_id, report_categories, reason, report_date) 
                          VALUES (:reply_id, :reporter_id, :categories, :reason, NOW())");
    $stmt->execute([
        ':reply_id' => $replyId,
        ':reporter_id' => $_SESSION['user_email'],
        ':categories' => implode(', ', $categories),
        ':reason' => $reason
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
