<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['forumTitle'] ?? '';
        $content = $_POST['forumContent'] ?? '';
        $category = $_POST['category'] ?? '';
        $author_email = $_SESSION['user_email'] ?? 'Unknown';
        $author = $_SESSION['user_name'] ?? 'Unknown';
        $post_date = date('Y-m-d H:i:s');

        $attachmentPath = '';

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
                $attachmentPath = $destPath;
            }
        }

        $sql = "INSERT INTO forum_post (title, content, category, author, author_email, post_date, attachments) VALUES (:title, :content, :category, :author, :author_email, :post_date, :attachments)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':author_email', $author_email);
        $stmt->bindParam(':post_date', $post_date);
        $stmt->bindParam(':attachments', $attachmentPath);

        if ($stmt->execute()) {
            $postId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'post_id' => $postId, 'attachment' => $attachmentPath]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create post']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
