<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Function to log debug messages
function debug_log($message) {
    error_log("[send_message.php] " . $message);
}

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    debug_log("Database connection established.");
} catch (PDOException $e) {
    debug_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    debug_log("User not logged in.");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_email = $_SESSION['user_email'];

$upload_path = null; // Explicitly set to null by default
$message_text = '';
$receiver_email = '';
$reply_to_message_id = null;
$replied_content = null;
$replied_uploads = null;

// Check if request is multipart/form-data (for attachments)
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Handle multipart/form-data request
    if (!isset($_POST['receiver_email']) || !isset($_POST['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing receiver_email or message']);
        exit;
    }
    $receiver_email = $_POST['receiver_email'];
    $message_text = trim($_POST['message']);
    $reply_to_message_id = isset($_POST['reply_to_message_id']) ? (int)$_POST['reply_to_message_id'] : null;
    $replied_content = isset($_POST['replied_content']) ? trim($_POST['replied_content']) : null;
    $replied_uploads = isset($_POST['replied_uploads']) ? trim($_POST['replied_uploads']) : null;

    if (empty($receiver_email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Empty receiver_email']);
        exit;
    }

    // Handle file upload
    if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Sanitize file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Directory for uploads
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            if (!mkdir($uploadFileDir, 0755, true)) {
                debug_log("Failed to create upload directory");
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                exit;
            }
        }

        if (!is_writable($uploadFileDir)) {
            debug_log("Upload directory not writable");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Upload directory not writable']);
            exit;
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $upload_path = $dest_path;
            debug_log("File uploaded successfully to: " . $upload_path);
        } else {
            debug_log("Failed to move uploaded file. Temp path: $fileTmpPath, Dest: $dest_path");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error moving uploaded file']);
            exit;
        }
    } else {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMsg = $uploadErrors[$_FILES['attachment']['error']] ?? 'Unknown upload error';
        debug_log("File upload error: " . $errorMsg);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $errorMsg]);
        exit;
    }
} else {
    // Handle JSON input for text-only messages
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['receiver_email'], $input['message']) || !is_string($input['receiver_email']) || !is_string($input['message']) || trim($input['message']) === '' || trim($input['receiver_email']) === '') {
        debug_log("Invalid input: missing or empty receiver_email or message.");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing or empty message or receiver_email']);
        exit;
    }

    $receiver_email = $input['receiver_email'];
    $message_text = trim($input['message']);
    $reply_to_message_id = isset($input['reply_to_message_id']) ? (int)$input['reply_to_message_id'] : null;
    $replied_content = isset($input['replied_content']) ? trim($input['replied_content']) : null;
    $replied_uploads = isset($input['replied_uploads']) ? trim($input['replied_uploads']) : null;
}

$delivery_status = 'sent';
$deliver_date = date('Y-m-d H:i:s');
$is_edited = 0;

try {
    // Check unread messages count from sender to receiver
    $checkUnreadStmt = $pdo->prepare("SELECT COUNT(*) FROM message WHERE sender_email = :sender_email AND receiver_email = :receiver_email AND delivery_status = 'sent'");
    $checkUnreadStmt->execute([
        ':sender_email' => $user_email,
        ':receiver_email' => $receiver_email
    ]);
    $unreadCount = (int)$checkUnreadStmt->fetchColumn();

    if ($unreadCount >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'You have 5 or more unread messages sent to this user. Please wait for them to read before sending more.']);
        exit;
    }

    // Check last message sent time from sender to receiver
    $checkLastMsgStmt = $pdo->prepare("SELECT deliver_date FROM message WHERE sender_email = :sender_email AND receiver_email = :receiver_email ORDER BY deliver_date DESC LIMIT 1");
    $checkLastMsgStmt->execute([
        ':sender_email' => $user_email,
        ':receiver_email' => $receiver_email
    ]);
    $lastMsgTime = $checkLastMsgStmt->fetchColumn();

    if ($lastMsgTime) {
        $lastMsgTimestamp = strtotime($lastMsgTime);
        $currentTimestamp = time();
        if (($currentTimestamp - $lastMsgTimestamp) < 10) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'You can only send one message every 10 seconds. Please wait before sending another message.']);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO message 
        (text, deliver_date, delivery_status, is_edited, edited_date, receiver_email, sender_email, uploads, reply_to_message_id, replied_content, replied_uploads)
        VALUES 
        (:text, :deliver_date, :delivery_status, :is_edited, :edited_date, :receiver_email, :sender_email, :uploads, :reply_to_message_id, :replied_content, :replied_uploads)
    ");
    $stmt->execute([
        ':text' => $message_text,
        ':deliver_date' => $deliver_date,
        ':delivery_status' => $delivery_status,
        ':is_edited' => $is_edited,
        ':edited_date' => null,
        ':receiver_email' => $receiver_email,
        ':sender_email' => $user_email,
        ':uploads' => $upload_path,
        ':reply_to_message_id' => $reply_to_message_id,
        ':replied_content' => $replied_content,
        ':replied_uploads' => $replied_uploads
    ]);
    debug_log("Message inserted successfully. ID: " . $pdo->lastInsertId());
    echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    debug_log("Send message error: " . $e->getMessage() . "\nQuery: " . $stmt->queryString);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send message',
        'debug_info' => $e->getMessage()
    ]);
}
?>