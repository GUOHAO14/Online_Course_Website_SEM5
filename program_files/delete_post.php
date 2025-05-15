<?php
// delete_post.php - Backend to handle deleting a forum post via JSON POST request

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['post_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing post_id.']);
        exit;
    }

    $post_id = (int)$input['post_id'];

    // Optional: Add author verification here if user authentication is implemented

    // Prepare delete statement
    $stmt = $pdo->prepare("DELETE FROM forum_post WHERE post_id = :post_id");

    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete post.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
