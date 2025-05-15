<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session to manage state
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

$admin = false;
if (isset($_SESSION['admin_id'])) {
    $admin = true;
}

$user_role = $_SESSION['role'] ?? '';

// Database connection
$host = 'localhost';
$db = 'cocdb';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching specific post based on the provided post ID from URL
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 1; // Default to first post if not set
$queryPost = "SELECT post_id, author, content, attachments, category, title, post_date FROM forum_post WHERE post_id = $postId";
$postResult = $conn->query($queryPost);
$post = $postResult->fetch_assoc();

if (!$post) {
    die("Post not found");
}

$queryReplies = "SELECT reply_id, author_email, replied_by, reply_content, reply_attachment, posted_date, edited_date, is_edited FROM replies WHERE post_id = $postId ORDER BY posted_date ASC";
$resultReplies = $conn->query($queryReplies);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $reply = $conn->real_escape_string($_POST['reply']);
    $userId = isset($_SESSION['user_email']) && $_SESSION['user_email'] ? $_SESSION['user_email'] : 'guest_' . uniqid();
    $username = isset($_SESSION['user_name']) && $_SESSION['user_name'] ? $_SESSION['user_name'] : 'Guest';

    $replyAttachmentPath = null;
    if (isset($_FILES['replyAttachment']) && $_FILES['replyAttachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                die("Failed to create upload directory.");
            }
        }
        $fileTmpPath = $_FILES['replyAttachment']['tmp_name'];
        $fileName = basename($_FILES['replyAttachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'pdf', 'doc', 'docx', 'txt'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Invalid file type. Only images, PDFs, and text documents are allowed.");
        }
        
        $newFileName = uniqid('reply_', true) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            die("Failed to move uploaded file.");
        }
        $replyAttachmentPath = $destPath;
    }

    $queryInsert = "INSERT INTO replies (post_id, author_email, replied_by, reply_content, posted_date, edited_date, is_edited, reply_attachment) 
                    VALUES ($postId, '" . $conn->real_escape_string($userId) . "', '" . $conn->real_escape_string($username) . "', '$reply', NOW(), NOW(), 0, " . 
                    ($replyAttachmentPath ? "'" . $conn->real_escape_string($replyAttachmentPath) . "'" : "''") . ")";
    
    if (!$conn->query($queryInsert)) {
        die("Database insert failed: " . $conn->error);
    }
    header("Location: {$_SERVER['PHP_SELF']}?post_id=$postId");
    exit();
}

// Handle AJAX edit reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_reply_id']) && isset($_POST['edit_reply_content'])) {
    $editReplyId = (int)$_POST['edit_reply_id'];
    $editReplyContent = $conn->real_escape_string($_POST['edit_reply_content']);
    $editReplyUser = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';

    // Check author
    $checkAuthorResult = $conn->query("SELECT replied_by FROM replies WHERE reply_id = $editReplyId");
    if (!$checkAuthorResult || !$row = $checkAuthorResult->fetch_assoc()) {
        echo json_encode(['success' => false, 'error' => 'Reply not found.']);
        exit();
    }
    
    if ($row['replied_by'] !== $editReplyUser) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized: You can only edit your own replies.']);
        exit();
    }

    // Handle attachments
    $removeAttachment = isset($_POST['remove_attachment']) && $_POST['remove_attachment'] === '1';
    $newAttachmentPath = null;
    
    // Get existing attachment
    $result = $conn->query("SELECT reply_attachment FROM replies WHERE reply_id = $editReplyId");
    $existingAttachment = ($result && $row = $result->fetch_assoc()) ? $row['reply_attachment'] : '';

    // Handle new file upload
    if (isset($_FILES['new_attachment']) && $_FILES['new_attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'Failed to create upload directory.']);
                exit();
            }
        }
        
        $fileTmpPath = $_FILES['new_attachment']['tmp_name'];
        $fileName = basename($_FILES['new_attachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'pdf', 'doc', 'docx', 'txt'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only images, PDFs, and text documents are allowed.']);
            exit();
        }
        
        $newFileName = uniqid('reply_', true) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
            exit();
        }
        $newAttachmentPath = $destPath;
        
        // Delete old attachment if exists
        if ($existingAttachment && file_exists($existingAttachment)) {
            unlink($existingAttachment);
        }
    } elseif ($removeAttachment) {
        // Remove existing attachment
        if ($existingAttachment && file_exists($existingAttachment)) {
            unlink($existingAttachment);
        }
        $newAttachmentPath = '';
    } else {
        $newAttachmentPath = $existingAttachment;
    }

    // Update reply
    $updateQuery = "UPDATE replies SET 
                    reply_content = '$editReplyContent', 
                    reply_attachment = " . ($newAttachmentPath ? "'" . $conn->real_escape_string($newAttachmentPath) . "'" : "NULL") . ",
                    edited_date = NOW(), 
                    is_edited = 1 
                    WHERE reply_id = $editReplyId";
    
    if ($conn->query($updateQuery)) {
        $result = $conn->query("SELECT edited_date, reply_attachment FROM replies WHERE reply_id = $editReplyId");
        if ($result && $row = $result->fetch_assoc()) {
            $editedDate = date('F j, Y \a\t g:i a', strtotime($row['edited_date']));
            $attachmentPath = $row['reply_attachment'];
            $newAttachmentHtml = '';
            
            if (!empty($attachmentPath)) {
                $fileExtension = pathinfo($attachmentPath, PATHINFO_EXTENSION);
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                if (in_array(strtolower($fileExtension), $imageExtensions)) {
                    $newAttachmentHtml = '<img src="' . htmlspecialchars($attachmentPath) . '" alt="Reply Attachment" style="max-width: 100%; max-height: 200px; margin-top: 10px; border-radius: 5px;">';
                } else {
                    $newAttachmentHtml = '<a href="' . htmlspecialchars($attachmentPath) . '" target="_blank" rel="noopener noreferrer">View Attachment</a>';
                }
            }
            
            echo json_encode([
                'success' => true, 
                'edited_date' => $editedDate, 
                'new_attachment_html' => $newAttachmentHtml
            ]);
        } else {
            echo json_encode(['success' => true, 'edited_date' => date('F j, Y \a\t g:i a'), 'new_attachment_html' => '']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// Handle AJAX delete reply
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reply_id'])) {
    $deleteReplyId = (int)$_POST['delete_reply_id'];
    $deleteReplyUser = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';

    // Check author
    $checkAuthorResult = $conn->query("SELECT replied_by, reply_attachment FROM replies WHERE reply_id = $deleteReplyId");
    if (!$checkAuthorResult || !$row = $checkAuthorResult->fetch_assoc()) {
        echo json_encode(['success' => false, 'error' => 'Reply not found.']);
        exit();
    }
    
    if ($row['replied_by'] !== $deleteReplyUser) {
        // Check if user is admin
        $user_role = $_SESSION['role'] ?? '';
        if ($user_role !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized: You can only delete your own replies.']);
            exit();
        }
    }

    // Delete attachment file if exists
    if (!empty($row['reply_attachment']) && file_exists($row['reply_attachment'])) {
        unlink($row['reply_attachment']);
    }

    // Delete the reply
    $deleteResult = $conn->query("DELETE FROM replies WHERE reply_id = $deleteReplyId");
    if ($deleteResult) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Discussion - Forum Post</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --text-color: #333;
            --light-text: #7f8c8d;
            --border-color: #eee;
            --bg-color: #f9f9f9;
            --card-bg: white;
            --modal-bg: white;
            --input-bg: white;
            --shadow-color: rgba(0,0,0,0.1);
            --hover-bg: #f0f4f8;
        }

        [data-theme="dark"] {
            --primary-color: #66b3ff;
            --secondary-color: #e0e0e0;
            --text-color: #d0d0d0;
            --light-text: #a0a0a0;
            --border-color: #444;
            --bg-color: #1a1a1a;
            --card-bg: #2a2a2a;
            --modal-bg: #2a2a2a;
            --input-bg: #333;
            --shadow-color: rgba(0,0,0,0.3);
            --hover-bg: #3a3a3a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            padding: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .theme-toggle {
            position: absolute;
            top: 0;
            right: 0;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
            transition: color 0.3s ease;
        }

        .theme-toggle:hover {
            color: var(--primary-color);
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-button:hover {
            color: var(--secondary-color);
        }

        .original-post {
            background-color: var(--card-bg);
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px var(--shadow-color);
            position: relative;
            transition: background-color 0.3s ease;
        }

        .post-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--secondary-color);
        }

        .post-meta {
            font-size: 14px;
            color: var(--light-text);
            margin-bottom: 15px;
        }

        .post-time,
        .edit-time {
            margin-top: 5px;
            font-size: 13px;
        }

        .post-content {
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .post-attachment {
            margin-top: 10px;
        }

        .replies-container {
            margin-top: 30px;
        }

        .reply {
            background-color: var(--card-bg);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px var(--shadow-color);
            position: relative;
            transition: background-color 0.3s ease;
        }

        .reply-attachment {
            margin-top: 10px;
        }

        .reply-form {
            margin-top: 30px;
        }

        .reply-input {
            width: 100%;
            max-width: 100%;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            min-height: 100px;
            max-height: 200px;
            margin-bottom: 10px;
            font-family: inherit;
            resize: vertical;
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .reply-input:focus {
            border-color: var(--primary-color);
        }

        .submit-button {
            background-color: var(--primary-color);
            font-family: inherit;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: var(--secondary-color);
        }

        .file-input-container {
            margin-bottom: 15px;
        }

        .file-input-label {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .file-input-label:hover {
            background-color: var(--secondary-color);
        }

        .file-input {
            display: none;
        }

        /* Edit/Delete Menu Styles */
        .post-actions {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .post-menu {
            cursor: pointer;
            font-size: 20px;
            color: var(--light-text);
            user-select: none;
            border-radius: 50%;
            padding: 6px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }

        .post-menu:hover {
            background-color: var(--hover-bg);
        }

        .post-dropdown {
            display: none;
            position: absolute;
            right: 0;
            background-color: var(--card-bg);
            box-shadow: 0 2px 5px var(--shadow-color);
            border-radius: 5px;
            z-index: 1;
            min-width: 120px;
            transition: background-color 0.3s ease;
        }

        .post-dropdown a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.3s ease;
        }

        .post-dropdown a:hover {
            background-color: var(--hover-bg);
        }

        .menu-icon {
            font-size: 18px;
            vertical-align: middle;
            margin-right: 6px;
        }

        .edit-form {
            display: none;
            margin-top: 10px;
        }

        .edit-textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            min-height: 100px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease;
            resize: vertical;
        }

        .edit-textarea:focus {
            border-color: var(--primary-color);
        }

        .edit-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-buttons button {
            padding: 5px 10px;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: background-color 0.3s ease;
        }

        .save-button {
            background-color: var(--primary-color);
            color: white;
        }

        .save-button:hover {
            background-color: var(--secondary-color);
        }

        .cancel-button {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }

        .attachment-preview {
            margin-top: 10px;
        }

        .attachment-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }

        .attachment-preview a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .attachment-preview a:hover {
            color: var(--secondary-color);
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .theme-toggle {
                top: 10px;
                right: 10px;
            }

            .post-meta {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <button class="theme-toggle" id="themeToggle" title="Toggle Theme">🌙</button>

    <div style="cursor: pointer;" onclick="window.history.back();" class="back-button">← Back to Forum</div>
    
    <div class="original-post">
        <?php if (isset($_SESSION['user_name']) && $_SESSION['user_name'] === $post['author']): ?>
        <div class="post-actions">
            <span class="post-menu">⋮</span>
            <div class="post-dropdown">
                <a href="#" class="edit-post">Edit</a>
                <a href="#" class="delete-post">Delete</a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="post-title"><strong><?php echo htmlspecialchars($post['title']); ?></strong></div>
        <div class="post-meta">
            Posted by <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('F j, Y \a\t g:i a', strtotime($post['post_date'])); ?>
        </div>
        <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <?php
            if (!empty($post['attachments'])) {
                $attachmentPath = htmlspecialchars($post['attachments']);
                $fileExtension = pathinfo($attachmentPath, PATHINFO_EXTENSION);
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                if (in_array(strtolower($fileExtension), $imageExtensions)) {
                    echo '<div class="post-attachment"><img src="' . $attachmentPath . '" alt="Attachment" style="max-width: 100%; max-height: 300px; margin-top: 10px; border-radius: 5px;"></div>';
                } else {
                    echo '<div class="post-attachment"><a href="' . $attachmentPath . '" target="_blank" rel="noopener noreferrer">View Attachment</a></div>';
                }
            }
        ?>
    </div>

    <div class="replies-container">
        <h3>Replies</h3>
        <?php if ($resultReplies->num_rows > 0): ?>
            <?php while ($reply = $resultReplies->fetch_assoc()): ?>
                <div class="reply" data-reply-id="<?php echo $reply['reply_id']; ?>">
                <div class="post-actions">
                    <span class="post-menu">⋮</span>
                    <div class="post-dropdown">
                        <?php if (isset($_SESSION['user_name']) && $_SESSION['user_name'] === $reply['replied_by']): ?>
                            <a href="#" class="edit-reply">Edit</a>
                            <a href="#" class="delete-reply">Delete</a>
                        <?php elseif ($user_role === 'admin'): ?>
                            <a href="#" class="delete-reply">Delete</a>
                        <?php else: ?>
                            <a href="#" class="report-reply" data-reply-id="<?php echo $reply['reply_id']; ?>">Report</a>
                        <?php endif; ?>
                    </div>
                </div>

<!-- Report Modal -->
<div id="reportModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg); padding: 20px; border-radius: 8px; max-width: 400px; width: 90%;">
        <h3>Report Reply</h3>
                <form id="reportForm">
                    <input type="hidden" name="reply_id" id="reportReplyId" value="">
                    <div>
                        <label><input type="checkbox" name="report_categories[]" value="Spam"> Spam</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Harassment"> Harassment</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Hate Speech"> Hate Speech</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Off-topic"> Off-topic</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Political"> Political</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Misinformation"> Misinformation</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Inappropriate"> Inappropriate</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Vulgar Language"> Vulgar Language</label><br>
                        <label><input type="checkbox" name="report_categories[]" value="Other"> Other</label>
                    </div>
                    <div style="margin-top: 10px;">
                        <label for="reportReason">Additional Details (optional):</label><br>
                        <textarea id="reportReason" name="reason" rows="4" style="width: 100%;"></textarea>
                    </div>
                    <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="cancelReport" style="background-color: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Cancel</button>
                        <button type="submit" style="background-color: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Submit</button>
                    </div>
                </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportModal');
    const reportForm = document.getElementById('reportForm');
    const reportReplyIdInput = document.getElementById('reportReplyId');
    const cancelReportBtn = document.getElementById('cancelReport');

    // Open report modal on clicking report link
    document.querySelectorAll('.report-reply').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const replyId = this.getAttribute('data-reply-id');
            reportReplyIdInput.value = replyId;
            reportModal.style.display = 'flex';
        });
    });

    // Cancel button closes modal
    cancelReportBtn.addEventListener('click', function() {
        reportModal.style.display = 'none';
        reportForm.reset();
    });

    // Submit report form via AJAX
    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Prevent multiple submissions with a flag
        if (reportForm.dataset.submitting === 'true') {
            return;
        }
        reportForm.dataset.submitting = 'true';

        // Disable submit button to prevent multiple submissions
        const submitButton = reportForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        const formData = new FormData(reportForm);
        const reportCategories = formData.getAll('report_categories[]');

        if (reportCategories.length === 0) {
            alert('Please select at least one report category.');
            submitButton.disabled = false;
            reportForm.dataset.submitting = 'false';
            return;
        }

        const data = {
            reply_id: formData.get('reply_id'),
            report_categories: reportCategories,
            reason: formData.get('reason')
        };

        fetch('report_reply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            submitButton.disabled = false;
            reportForm.dataset.submitting = 'false';
            if (result.success) {
                alert('Report submitted successfully.');
                reportModal.style.display = 'none';
                reportForm.reset();
            } else {
                alert('Error submitting report: ' + (result.error || 'Unknown error'));
            }
        })
        .catch(error => {
            submitButton.disabled = false;
            reportForm.dataset.submitting = 'false';
            console.error('Error:', error);
            alert('An error occurred while submitting the report.');
        });
    });

    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === reportModal) {
            reportModal.style.display = 'none';
            reportForm.reset();
        }
    });
});
</script>
                    
                    <div class="post-meta">
                        <strong>
                        <?php
                            if (isset($_SESSION['user_name']) && $reply['replied_by'] === $_SESSION['user_name']) {
                                echo "You";
                            } else {
                                echo htmlspecialchars($reply['replied_by']);
                            }
                        ?>
                        </strong>
                        <div style="font-size: 13px; color: #7f8c8d; margin-top: 3px;">
                            Posted: <?php echo date('F j, Y \a\t g:i a', strtotime($reply['posted_date'])); ?>
                            <?php if ($reply['is_edited'] && $reply['edited_date'] !== $reply['posted_date']): ?>
                                <br><span style="font-size: 13px; color: #7f8c8d;">Edited: <?php echo date('F j, Y \a\t g:i a', strtotime($reply['edited_date'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="post-content"><?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?></div>
                    <?php
                        if (!empty($reply['reply_attachment'])) {
                            $attachmentPath = htmlspecialchars($reply['reply_attachment']);
                            $fileExtension = pathinfo($attachmentPath, PATHINFO_EXTENSION);
                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                            if (in_array(strtolower($fileExtension), $imageExtensions)) {
                                echo '<div class="reply-attachment"><img src="' . $attachmentPath . '" alt="Reply Attachment" style="max-width: 100%; max-height: 200px; margin-top: 10px; border-radius: 5px;"></div>';
                            } else {
                                echo '<div class="reply-attachment"><a href="' . $attachmentPath . '" target="_blank" rel="noopener noreferrer">View Attachment</a></div>';
                            }
                        }
                    ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No replies yet. Be the first to reply!</p>
        <?php endif; ?>
        <br> 
    </div>

    <!-- Report Modal -->
    <div id="reportModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--card-bg); padding: 20px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
            <h3>Report Reply</h3>
            <form id="reportForm">
                <input type="hidden" id="reportReplyId" name="reply_id" value="" />
                <div>
                    <label><input type="checkbox" name="report_categories" value="Spam" /> Spam</label><br/>
                    <label><input type="checkbox" name="report_categories" value="Harassment" /> Harassment</label><br/>
                    <label><input type="checkbox" name="report_categories" value="Inappropriate Content" /> Inappropriate Content</label><br/>
                    <label><input type="checkbox" name="report_categories" value="Other" /> Other</label>
                </div>
                <div style="margin-top: 10px;">
                    <label for="reportReason">Reason (optional):</label><br/>
                    <textarea id="reportReason" name="reason" rows="3" style="width: 100%;"></textarea>
                </div>
                <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" id="cancelReport" style="background-color: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Cancel</button>
                    <button type="submit" style="background-color: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Submit</button>
                </div>
                <div id="reportError" style="color: red; margin-top: 10px; display: none;"></div>
                <div id="reportSuccess" style="color: green; margin-top: 10px; display: none;">Report submitted successfully.</div>
            </form>
        </div>
    </div>

    <h3>Add Your Reply</h3>
    <form method="POST" enctype="multipart/form-data" id="replyForm">
        <div class="file-input-container">
            <input type="file" id="replyAttachment" name="replyAttachment" accept="image/*,application/pdf,.doc,.docx,.txt" />
        </div>
        <textarea class="reply-input" name="reply" id="replyInput" required placeholder="Write your reply..."></textarea>
        <button type="submit" class="submit-button">Post Reply</button>
    </form>
</div>

<script>
    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('themeToggle');
        
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }
        
        function updateThemeIcon(theme) {
            themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
        
        function loadTheme() {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            let savedTheme = localStorage.getItem('theme') || (prefersDark ? 'dark' : 'light');
            
            if (savedTheme !== 'dark' && savedTheme !== 'light') {
                savedTheme = 'light';
            }
            
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }
        
        loadTheme();
        themeToggle.addEventListener('click', toggleTheme);

        // Toggle dropdown menus
        function setupDropdowns() {
            document.querySelectorAll('.post-menu').forEach(menu => {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling;
                    document.querySelectorAll('.post-dropdown').forEach(d => {
                        if (d !== dropdown) d.style.display = 'none';
                    });
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                });
            });
            
            document.addEventListener('click', function() {
                document.querySelectorAll('.post-dropdown').forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
            });
        }
        setupDropdowns();

        // Edit reply functionality
        document.querySelectorAll('.edit-reply').forEach(editLink => {
            editLink.addEventListener('click', function(e) {
                e.preventDefault();
                const reply = this.closest('.reply');
                
                if (!reply.querySelector('.edit-form')) {
                    const editForm = document.createElement('form');
                    editForm.className = 'edit-form';
                    editForm.style.display = 'block';

                    // Get current content and attachment
                    const currentContent = reply.querySelector('.post-content').textContent.trim();
                    const currentAttachment = reply.querySelector('.reply-attachment img, .reply-attachment a');
                    let attachmentHtml = '';
                    
                    if (currentAttachment) {
                        if (currentAttachment.tagName.toLowerCase() === 'img') {
                            attachmentHtml = `
                                <div class="attachment-preview">
                                    <p>Current Attachment:</p>
                                    <img src="${currentAttachment.src}" alt="Attachment" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                                </div>
                            `;
                        } else if (currentAttachment.tagName.toLowerCase() === 'a') {
                            attachmentHtml = `
                                <div class="attachment-preview">
                                    <p>Current Attachment: <a href="${currentAttachment.href}" target="_blank">View Attachment</a></p>
                                </div>
                            `;
                        }
                    }

                    editForm.innerHTML = `
                        <textarea class="edit-textarea" name="edit_reply_content">${currentContent}</textarea>
                        ${attachmentHtml}
                        <div>
                            <label><input type="checkbox" name="remove_attachment" value="1"> Remove Attachment</label>
                        </div>
                        <div class="file-input-container">
                            <label for="newAttachment" class="file-input-label">Change Attachment</label>
                            <input type="file" id="newAttachment" name="new_attachment" class="file-input" accept="image/*,application/pdf,.doc,.docx,.txt" />
                        </div>
                        <div class="edit-buttons">
                            <button type="button" class="save-button">Save</button>
                            <button type="button" class="cancel-button">Cancel</button>
                        </div>
                    `;
                    
                    reply.appendChild(editForm);
                    reply.querySelector('.post-content').style.display = 'none';

                    // Save button handler
                    editForm.querySelector('.save-button').addEventListener('click', function() {
                        const newContent = editForm.querySelector('.edit-textarea').value.trim();
                        if (!newContent) {
                            alert('Reply content cannot be empty');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('edit_reply_id', reply.dataset.replyId);
                        formData.append('edit_reply_content', newContent);
                        formData.append('remove_attachment', editForm.querySelector('[name="remove_attachment"]').checked ? '1' : '0');
                        
                        const fileInput = editForm.querySelector('#newAttachment');
                        if (fileInput.files[0]) {
                            formData.append('new_attachment', fileInput.files[0]);
                        }

                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                reply.querySelector('.post-content').textContent = newContent;
                                
                                // Update attachment display
                                const replyAttachmentDiv = reply.querySelector('.reply-attachment');
                                if (data.new_attachment_html === '') {
                                    if (replyAttachmentDiv) {
                                        replyAttachmentDiv.remove();
                                    }
                                } else {
                                    if (replyAttachmentDiv) {
                                        replyAttachmentDiv.innerHTML = data.new_attachment_html;
                                    } else {
                                        const newDiv = document.createElement('div');
                                        newDiv.className = 'reply-attachment';
                                        newDiv.innerHTML = data.new_attachment_html;
                                        reply.appendChild(newDiv);
                                    }
                                }
                                
                                // Update edited date
                                const editedDateSpan = reply.querySelector('.edited-date');
                                if (editedDateSpan) {
                                    editedDateSpan.textContent = 'Edited: ' + data.edited_date;
                                } else {
                                    const postMeta = reply.querySelector('.post-meta div');
                                    if (postMeta) {
                                        const postedText = postMeta.textContent.match(/Posted:.*?(?=Edited:|$)/)[0];
                                        postMeta.innerHTML = postedText + '<br><span class="edited-date" style="font-size: 13px; color: #7f8c8d;">Edited: ' + data.edited_date + '</span>';
                                    }
                                }
                                
                                editForm.remove();
                                reply.querySelector('.post-content').style.display = 'block';
                            } else {
                                alert('Error: ' + (data.error || 'Failed to update reply'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while updating the reply');
                        });
                    });

                    // Cancel button handler
                    editForm.querySelector('.cancel-button').addEventListener('click', function() {
                        editForm.remove();
                        reply.querySelector('.post-content').style.display = 'block';
                    });
                }
                
                this.closest('.post-dropdown').style.display = 'none';
            });
        });

        // Delete reply functionality
        document.querySelectorAll('.delete-reply').forEach(deleteLink => {
            deleteLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this reply?')) {
                    return;
                }

                const reply = this.closest('.reply');
                const replyId = reply.dataset.replyId;

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete_reply_id=' + encodeURIComponent(replyId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reply.remove();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete reply'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the reply');
                });
                
                this.closest('.post-dropdown').style.display = 'none';
            });
        });

        // File input styling
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.previousElementSibling;
                if (this.files.length > 0) {
                    label.textContent = this.files[0].name;
                } else {
                    label.textContent = 'Add Attachment';
                }
            });
        });
    });
</script>
</body>
</html>