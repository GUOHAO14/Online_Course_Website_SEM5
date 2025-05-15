<?php
// edit_post.php - Backend to handle editing a forum post via JSON POST request

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $content = $_POST['content'] ?? null;
    $remove_attachment = $_POST['remove_attachment'] ?? '0';

    if (!$post_id || !$title || $content === null) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit;
    }

    // Fetch current attachment path
    $stmt = $pdo->prepare("SELECT attachments FROM forum_post WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $currentAttachment = $stmt->fetchColumn();

    $attachmentPath = $currentAttachment;

    // Handle attachment removal
    if ($remove_attachment === '1' && $currentAttachment) {
        if (file_exists($currentAttachment)) {
            unlink($currentAttachment);
        }
        $attachmentPath = '';
    }

    // Handle new attachment upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = basename($_FILES['attachment']['name']);
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Delete old attachment if exists
            if ($attachmentPath && file_exists($attachmentPath)) {
                unlink($attachmentPath);
            }
            $attachmentPath = $destPath;
        }
    }

    // Prepare update statement with attachment
    $stmt = $pdo->prepare("UPDATE forum_post SET title = :title, content = :content, attachments = :attachments WHERE post_id = :post_id");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':attachments', $attachmentPath);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'attachment' => $attachmentPath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update post.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
