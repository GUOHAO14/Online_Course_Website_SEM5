<?php
session_start(); // Start session to access user email from session

// report_post.php - Backend to handle saving report data into the database

// Database configuration
$host = 'localhost';
$dbname = 'capstone';
$username = 'root';
$password = '';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Debug: log entire input
    error_log("Report post input: " . var_export($input, true));

    // If JSON input is null, fallback to $_POST
    if ($input === null) {
        $input = $_POST;
        // Convert report_categories from comma-separated string to array if needed
        if (isset($input['report_categories']) && !is_array($input['report_categories'])) {
            $input['report_categories'] = explode(',', $input['report_categories']);
        }
    }

    if (!isset($input['post_id'], $input['report_categories']) || !is_array($input['report_categories'])) {
        echo json_encode(['success' => false, 'error' => 'Missing or invalid required fields.']);
        exit;
    }

    if (!isset($_SESSION['user_email'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in.']);
        exit;
    }

    $user_email = $_SESSION['user_email'];
    $post_id = (int)$input['post_id'];
    $report_categories = implode(',', $input['report_categories']);
    $reason = (isset($input['reason']) && $input['reason'] !== null && trim($input['reason']) !== '') ? $input['reason'] : 'No reason provided';

    // Debug: log reason value
    error_log("Reason desc value: " . var_export($reason, true));

    // Check if the user has already reported this post
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_email = :user_email AND post_id = :post_id");
    $checkStmt->bindParam(':user_email', $user_email);
    $checkStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'You have already reported this post before.']);
        exit;
    }

    // Insert one row with user_email included, assuming report_id is auto-increment
    $stmt = $pdo->prepare("INSERT INTO reports (user_email, post_id, report_category, reason_desc) VALUES (:user_email, :post_id, :report_category, :reason_desc)");

    $stmt->bindParam(':user_email', $user_email);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':report_category', $report_categories);
    $stmt->bindParam(':reason_desc', $reason);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to insert report.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
