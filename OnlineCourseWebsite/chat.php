<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user info from session
$logged_in_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$logged_in_role = isset($_SESSION["role"]) ? $_SESSION["role"] : null;
$logged_in_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

if (!$logged_in_email || !$logged_in_role) {
    die("User not logged in.");
}

// Determine which users to load based on logged-in user's role
if (strtolower($logged_in_role) === 'student') {
    // Load lecturers
    $sql = "SELECT name, user_email, role, pfp FROM user WHERE LOWER(role) = 'lecturer'";
    $chat_title = "Lecturers";
    $search_placeholder = "Search lecturers...";
    $chat_member_role = "Lecturer";
} elseif (strtolower($logged_in_role) === 'lecturer') {
    // Load students
    $sql = "SELECT name, user_email, role, pfp FROM user WHERE LOWER(role) = 'student'";
    $chat_title = "Students";
    $search_placeholder = "Search students...";
    $chat_member_role = "Student";
} else {
    $sql = "SELECT name, user_email, role, pfp FROM user";
    $chat_title = "Users";
    $search_placeholder = "Search users...";
    $chat_member_role = "User";
}

$result = $conn->query($sql);

$chat_users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch last message between logged-in user and this user
        $userEmail = $row['user_email'];
        $stmt = $conn->prepare("
            SELECT text, 
            (SELECT COUNT(*) FROM message m2 WHERE m2.sender_email = ? AND m2.receiver_email = ? AND m2.delivery_status = 'sent') AS unread_count
            FROM message
            WHERE (sender_email = ? AND receiver_email = ?)
               OR (sender_email = ? AND receiver_email = ?)
            ORDER BY deliver_date DESC
            LIMIT 1
        ");
        $stmt->bind_param("ssssss", $userEmail, $logged_in_email, $logged_in_email, $userEmail, $userEmail, $logged_in_email);
        $stmt->execute();
        $stmt->bind_result($lastMessage, $unreadCount);
        $stmt->fetch();
        $stmt->close();

        $row['last_message'] = $lastMessage ? $lastMessage : '';
        $row['unread_count'] = $unreadCount ? $unreadCount : 0;
        $chat_users[] = $row;
    }
}


// Use $logged_in_email and $logged_in_name for current user info in JS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Chat - Connect with <?php echo htmlspecialchars($chat_title); ?></title>
    <style>
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --text-color: #333;
    --light-text: #7f8c8d;
    --border-color: #e0e0e0;
    --bg-color: #f5f5f5;
    --card-bg: white;
    --unread-badge: #e74c3c;
    --sent-message: #e3f2fd;
    --received-message: #ffffff;
    --dark-mode-icon-fill: #333;
}

[data-theme="dark"] {
    --primary-color: #4dabf7;
    --secondary-color: #343a40;
    --text-color: #f8f9fa;
    --light-text: #adb5bd;
    --border-color: #495057;
    --bg-color: #212529;
    --card-bg: #343a40;
    --unread-badge: #ff6b6b;
    --sent-message: #495057;
    --received-message: #495057;
    --dark-mode-icon-fill: #f8f9fa;
}

/* Add dark mode toggle styles */
.dark-mode-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin-right: 10px;
    color: var(--text-color);
    transition: transform 0.2s;
}

.dark-mode-toggle:hover {
    transform: scale(1.1);
}

.dark-mode-icon {
    fill: var(--dark-mode-icon-fill);
    transition: fill 0.3s ease;
}

/* Message bubbles in dark mode */
[data-theme="dark"] .message.sent {
    background-color: #3a3f44;
    color: #f8f9fa;
}

[data-theme="dark"] .message.received {
    background-color: #495057;
    color: #f8f9fa;
}

/* Input area in dark mode */
[data-theme="dark"] .input-area {
    background-color: #343a40;
    border-top-color: #495057;
}

[data-theme="dark"] .message-input {
    background-color: #495057;
    color: #f8f9fa;
    border-color: #6c757d;
}

/* Sidebar in dark mode */
[data-theme="dark"] .sidebar {
    background-color: #343a40;
    border-right-color: #495057;
}

[data-theme="dark"] .sidebar-header {
    border-bottom-color: var(--border-color);
}

[data-theme="dark"] .chat-title {
    color: var(--text-color);
}

[data-theme="dark"] .search-bar {
    border-bottom-color: #495057;
}

[data-theme="dark"] .search-input {
    background-color: #495057;
    color: #f8f9fa;
    border-color: #6c757d;
}

[data-theme="dark"] .chat-item {
    border-bottom-color: #495057;
}

[data-theme="dark"] .chat-item:hover {
    background-color: #3a3f44;
}

[data-theme="dark"] .sidebar-header h1 {
    color: var(--text-color);
}

[data-theme="dark"] .chat-item.active {
    background-color: #495057;
    color: var(--text-color);
}

/* System messages in dark mode */
[data-theme="dark"] .system-message {
    color: #adb5bd;
}

body, .message, .sidebar, .input-area, .chat-item {
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

.message-content, .chat-name, .chat-preview, .message-time {
    transition: color 0.3s ease;
}

.search-input, .message-input {
    transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 350px;
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-color);
            height: 100%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h1 {
            font-size: 20px;
            color: var(--secondary-color);
        }

        .search-bar {
            padding: 10px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .search-input {
            width: 100%;
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-color);
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
        }

        .chat-item:hover {
            background-color: var(--bg-color);
        }

        .chat-item.active {
            background-color: #e8f4fc;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        /* Restrict chat-avatar display to inside sidebar only */
        body:not(.sidebar) > .chat-avatar,
        .chat-avatar:not(.sidebar .chat-avatar) {
            display: none !important;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-name {
            font-weight: bold;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-preview {
            font-size: 14px;
            color: var(--light-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-preview.unread-message {
            font-weight: bold;
            color: var(--text-color);
        }

        .chat-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-left: 10px;
        }

        .chat-time {
            font-size: 12px;
            color: var(--light-text);
            margin-bottom: 5px;
        }

        .unread-badge {
            background-color: var(--unread-badge);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        /* Chat Area Styles */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            height: 64px;
        }

        .chat-title {
            font-size: 18px;
            font-weight: bold;
            margin-right: 10px;
        }

        .chat-members {
            font-size: 14px;
            color: var(--light-text);
        }

        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: var(--bg-color);
            max-width: 100%;
            margin-left: 10%;
            margin-right: 10%;
        }

        .message {
            display: block;
            max-width: 100%;
            white-space: normal;
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .message-content {
            display: inline-block;
            max-width: 50%;
            min-width: fit-content;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            box-sizing: content-box; 
            vertical-align: middle;
        }

        .message-content.short-message {
            max-width: none;
            width: fit-content;
            padding: 5px 10px;
            background-color: var(--sent-message);
            border-radius: 18px;
            display: inline-block;
            white-space: nowrap;
        }

        .message.short-message {
            max-width: fit-content;
            width: fit-content;
            padding: 5px 10px;
            border-radius: 18px;
            white-space: nowrap;
            margin-bottom: 15px;
        }

        .message.sent {
            background-color: var(--sent-message);
            margin-left: auto;
            border-top-right-radius: 5px;
            box-shadow: 0 1px 1px rgba(0.1,0.1,0.1,0.1);
            max-width: 50%;
            min-width: auto;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .message.received {
            background-color: var(--received-message);
            margin-right: auto;
            border-top-left-radius: 5px;
            box-shadow: 0 1px 1px rgba(0.1,0.1,0.1,0.1);
            max-width: 50%;
            min-width: auto;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .message-info {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 5px;
            font-size: 12px;
            color: var(--light-text);
        }

        .message-sender {
            font-weight: bold;
            margin-right: 5px;
        }

        .message-time {
            opacity: 0.8;
        }

        .message-status {
            margin-left: 5px;
            display: inline-flex;
            vertical-align: middle;
        }
        .message-status svg {
            stroke: currentColor;
            width: 16px;
            height: 12px;
        }

        .system-message {
            text-align: center;
            margin: 20px 0;
            color: var(--light-text);
            font-size: 14px;
        }

        .input-area {
            padding: 15px;
            border-top: 1px solid var(--border-color);
            background-color: var(--card-bg);
            position: relative;
            display: flex;
            align-items: center;
            
        }

        .attachment-button {
            position: absolute;
            right: calc(10% + 15px); /* Match textarea's right margin + padding */
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: var(--text-color);
            transition: transform 0.2s;
        }

.attachment-button:hover {
    transform: translateY(-50%) scale(1.1);
}

.attachment-button:active {
    transform: translateY(-50%) scale(0.95);
}

.message-input {
    margin: 0 10%;
    width: 80%;
            min-height: 40px;
            max-height: 120px;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            resize: none;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-left: 10%;
            margin-right: 10%;
        }



        .message-input:focus {
            border-color: var(--primary-color);
        }

        /* Add this to your existing CSS */
.message-actions {
    position: absolute;
    right: 10px;
    top: -15px;
    display: none;
    background: var(--card-bg);
    border-radius: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    padding: 3px;
    z-index: 10;
}

.message:hover .message-actions {
    display: flex;
    animation: popUp 0.2s ease-out;
}

.edited-indicator {
    color: var(--light-text);
    font-size: 0.8em;
    margin-right: 5px;
    font-style: italic;
}

.message-info {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    font-size: 12px;
    color: var(--light-text);
}

@keyframes popUp {
    0% { transform: translateY(5px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}

.message-action-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: var(--light-text);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-action-btn:hover {
    color: var(--primary-color);
    transform: scale(1.1);
}

.message-action-btn svg {
    width: 16px;
    height: 16px;
}

.message-action-btn.delete:hover {
    color: #e74c3c;
}

.message-action-btn.report:hover {
    color: #f39c12;
}

.message-action-btn.reported {
    color: #f39c12 !important;
    cursor: default;
}

.message-action-btn.reported:hover {
    transform: none !important;
}

/* Adjust message container to accommodate actions */
.message {
    position: relative;
    padding-right: 40px; /* Space for actions */
}

/* Edit Message Styles */
.edit-textarea {
    width: 100%;
    min-height: 60px;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    resize: vertical;
    font-size: 14px;
    margin-bottom: 8px;
    background-color: var(--card-bg);
    color: var(--text-color);
}

.edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.save-edit, .cancel-edit {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.save-edit {
    background-color: var(--primary-color);
    color: white;
}

.save-edit:hover {
    background-color: #2980b9;
}

.cancel-edit {
    background-color: #e0e0e0;
    color: var(--text-color);
}

.cancel-edit:hover {
    background-color: #d0d0d0;
}

.edited-indicator {
    color: var(--light-text);
    font-size: 0.8em;
    margin-right: 5px;
    font-style: italic;
    cursor: help;
    border-bottom: 1px dotted var(--light-text);
}

.edit-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.edit-modal {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    gap: 15px; 
}

.edit-modal h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--text-color);
}

.attachment-preview {
    margin-bottom: 15px;
    padding: 10px;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
    text-align: center;
}

.attachment-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 4px;
}

.attachment-preview a {
    display: inline-block;
    padding: 8px 12px;
    background: var(--primary-color);
    color: white;
    border-radius: 4px;
    text-decoration: none;
}

.edit-modal-textarea {
    width: 100%;
    min-height: 120px;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    margin-bottom: 15px;
    resize: vertical;
    margin-bottom: 0; 
}

.edit-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.edit-modal-actions button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.edit-modal-actions .cancel-edit {
    background: #e0e0e0;
}

.edit-modal-actions .save-edit {
    background: var(--primary-color);
    color: white;
}

/* Styles for the reply preview above the input box (when composing a reply) */
#replyPreview {
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    padding: 6px 8px;
    margin: 0 10% 8px 10%;
    border-left: 3px solid var(--primary-color);
    width: 80%;
    box-sizing: border-box;
}

#cancelReply {
    transition: all 0.2s;
}

#cancelReply:hover {
    color: var(--primary-color);
    transform: scale(1.2);
}

/* Styles for reply previews within chat messages (message history) */
.reply-preview {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    padding: 6px 8px;
    margin-bottom: 8px;
    border-left: 3px solid var(--primary-color);
    cursor: pointer;
}

.reply-preview:hover {
    background-color: rgba(0, 0, 0, 0.08);
}

/* Dark mode styles */
[data-theme="dark"] #replyPreview,
[data-theme="dark"] .reply-preview {
    background-color: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .reply-preview:hover {
    background-color: rgba(255, 255, 255, 0.08);
}

/* Dark mode adjustments */
[data-theme="dark"] .edit-textarea {
    background-color: #495057;
    border-color: #6c757d;
    color: #f8f9fa;
}

[data-theme="dark"] .cancel-edit {
    background-color: #6c757d;
    color: #f8f9fa;
}

[data-theme="dark"] .cancel-edit:hover {
    background-color: #5a6268;
}


        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: 40vh;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            
            .chat-area {
                height: 60vh;
            }
        }

        @media (max-width: 768px) {
    .chat-header,
    .messages-container,
    .input-area > div {
        margin-left: 5% !important;
        margin-right: 5% !important;
    }
}
.reply-highlight {
    background-color: turquoise !important;
    transition: background-color 0.5s ease;
}
/* Add styles for image zoom modal */
#imageZoomModal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.8);
    justify-content: center;
    align-items: center;
}

#imageZoomModal img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(255,255,255,0.5);
    cursor: zoom-out;
}

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 10000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* optional nice effect */
        }

        body {
            position: relative; 
            padding-top: 7rem;
        }

        @media (max-width: 1160px) {
            body {
                padding-top: 5.6rem
            }
        }

        @media (max-width: 1010px) {
            body {
                padding-top: 5.2rem
            }
        }

        @media (max-width: 326px) {
            body {
                padding-top: 10.2rem
            }
        }
</style>
</head>
<body>
    <header>
        <?php
        if (isset($_SESSION['student_id'])) {
            include "header.php";
        } else if (isset($_SESSION['lecturer_id'])) {
            include "lec_header.php";
        }
        ?>
    </header>
<!-- Image Zoom Modal -->
<div id="imageZoomModal">
    <img src="" alt="Zoomed Image" id="zoomedImage" />
</div>
    <!-- Sidebar with chat list -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1><?php echo htmlspecialchars($chat_title); ?></h1>
        </div>
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="<?php echo htmlspecialchars($search_placeholder); ?>" id="searchInput">
        </div>
        <div class="chat-list" id="chatList">
            <?php if (is_array($chat_users) && count($chat_users) > 0): ?>
                <?php foreach ($chat_users as $user): ?>
                    <div class="chat-item" data-email="<?php echo htmlspecialchars($user['user_email']); ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" data-pfp="<?php echo htmlspecialchars($user['pfp']); ?>">
                        <?php
                        $pfp = trim($user['pfp']);
                        if (file_exists($pfp)) {
                            echo '<img src="' . htmlspecialchars($pfp) . '" class="chat-avatar" style="width:50px;height:50px;border-radius:50%;object-fit:cover;" alt="Avatar">';
                        } else {
                            echo '<div class="chat-avatar">' . strtoupper(substr($user['name'], 0, 2)) . '</div>';
                        }
                        ?>
                        <div class="chat-info">
                            <div class="chat-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="chat-preview <?php echo ($user['unread_count'] > 0) ? 'unread-message' : ''; ?>"><?php echo htmlspecialchars($user['last_message'] ?: 'Click to start chat'); ?></div>
                        </div>
                        <div class="chat-meta">
                            <div class="unread-badge" style="display:<?php echo ($user['unread_count'] > 0) ? 'flex' : 'none'; ?>;"><?php echo ($user['unread_count'] > 99) ? '99+' : $user['unread_count']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 20px; text-align: center; color: var(--light-text)">No <?php echo htmlspecialchars(strtolower($chat_title)); ?> found</div>
            <?php endif; ?>
        </div>
    </div>

<!-- Main chat area -->
<div class="chat-area">
    <!-- Chat header with full-width bottom border -->
        <div class="chat-header" style="display: flex; align-items: center; justify-content: space-between; margin-left: 10%; margin-right: 10%;">
            <!-- Left side - Chat title and profile picture -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="" alt="Profile Picture" id="currentChatPfp" style="width:40px; height:40px; border-radius:50%; object-fit: cover; display:none;" />
                <div>
                    <div class="chat-title" id="currentChatTitle">Select a <?php echo htmlspecialchars(strtolower($chat_member_role)); ?> to chat</div>
                    <div class="chat-members" id="currentChatMembers"></div>
                </div>
            </div>
            
            <!-- Center spacer for balance -->
            <div style="flex-grow: 1;"></div>
            
            <!-- Right side buttons -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Contact Admin button -->
                <button id="adminChatBtn" style="background: var(--primary-color); color: white; border: none; padding: 5px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 24px; line-height: 1;">
                    👩‍💼💬
                </button>
                
                <!-- Dark mode toggle button -->
                <button id="darkModeToggle" class="dark-mode-toggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1-8.313-12.454z" class="dark-mode-icon"/>
                    </svg>
                </button>
            </div>
        </div>
    
    <div class="messages-container" id="messagesContainer" style="margin-left: 10%; margin-right: 10%;">
        <div class="system-message">Select a <?php echo htmlspecialchars(strtolower($chat_member_role)); ?> to start chatting</div>
    </div>
    
    <!-- Full-width input area background -->
<!-- Replace your existing input-area div with this -->
<div class="input-area" style="position: relative; display: flex; flex-direction: column; padding: 15px;">
    <!-- Reply preview container -->
    <div id="replyPreview" style="display: none; background: var(--card-bg); border-left: 3px solid var(--primary-color); border-radius: 8px; padding: 8px 12px; margin: 0 10% 8px 10%; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
            <div style="font-size: 12px; color: var(--primary-color); font-weight: bold;">Replying to <span id="replySenderName"></span></div>
            <button id="cancelReply" style="background: none; border: none; color: var(--light-text); cursor: pointer; font-size: 16px; padding: 0 4px;">×</button>
        </div>
        <div id="replyPreviewContent" style="font-size: 14px; color: var(--text-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
    </div>
    
    <!-- Message input container -->
    <div style="position: relative; display: flex; align-items: center; width: 100%;">
        <textarea class="message-input" placeholder="Type a message..." id="messageInput" disabled style="flex: 1; padding-right: 40px; margin: 0 10%;"></textarea>
        <button class="attachment-button" id="attachmentButton">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke="var(--text-color)" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
        <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*,application/pdf" />
    </div>
</div>

    <!-- Attachment Preview Modal -->
    <div id="attachmentModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: var(--card-bg); padding: 20px; border-radius: 8px; max-width: 90%; max-height: 90%; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
            <div id="attachmentPreview" style="max-height: 300px; overflow: auto; text-align: center;"></div>
            <textarea id="attachmentMessageInput" placeholder="Add a message..." style="width: 100%; min-height: 60px; resize: vertical; padding: 8px; border: 1px solid var(--border-color); border-radius: 5px; font-size: 14px;"></textarea>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button id="cancelAttachment" style="padding: 8px 16px; border: none; background-color: #ccc; border-radius: 5px; cursor: pointer;">Cancel</button>
                <button id="sendAttachment" style="padding: 8px 16px; border: none; background-color: var(--primary-color); color: white; border-radius: 5px; cursor: pointer;">Send</button>
            </div>
        </div>
    </div>
</div>

    <script>
        let currentReplyMessageId = null;
        let currentReplySenderName = null;
        let currentReplyContent = null;
        // Pass PHP variables to JS
        const loggedInEmail = <?php echo json_encode($logged_in_email); ?>;
        const loggedInName = <?php echo json_encode($logged_in_name); ?>;
        const chatMemberRole = <?php echo json_encode($chat_member_role); ?>;

        document.addEventListener('DOMContentLoaded', function() {

            // DOM elements
            const chatList = document.getElementById('chatList');
            const messagesContainer = document.getElementById('messagesContainer');
            const currentChatTitle = document.getElementById('currentChatTitle');
            const currentChatMembers = document.getElementById('currentChatMembers');
            const searchInput = document.getElementById('searchInput');

            const attachmentButton = document.getElementById('attachmentButton');
            const fileInput = document.getElementById('fileInput');
            const messageInput = document.getElementById('messageInput');
            const attachmentModal = document.getElementById('attachmentModal');
            const attachmentPreview = document.getElementById('attachmentPreview');
            const attachmentMessageInput = document.getElementById('attachmentMessageInput');
            const cancelAttachment = document.getElementById('cancelAttachment');
            const sendAttachment = document.getElementById('sendAttachment');

            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

            // Check for saved user preference or use system preference
            const currentTheme = localStorage.getItem('theme') || 
                                (prefersDarkScheme.matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', currentTheme);

            // Toggle dark mode
            darkModeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Update the icon rotation for visual feedback
                darkModeToggle.style.transform = newTheme === 'dark' ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            // Add initial rotation if dark mode is on
            if (currentTheme === 'dark') {
                darkModeToggle.style.transform = 'rotate(180deg)';
            }

            let activeChatEmail = null;
            let activeChatName = null;

            // Attachment button click opens file dialog
            attachmentButton.addEventListener('click', () => {
                fileInput.click();
            });

            // Handle file selection and show modal with preview
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (!file) return;

                // Clear previous preview and message
                attachmentPreview.innerHTML = '';
                attachmentMessageInput.value = '';

                // Create preview based on file type
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '300px';
                    attachmentPreview.appendChild(img);
                } else if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.controls = true;
                    video.style.maxWidth = '100%';
                    video.style.maxHeight = '300px';
                    attachmentPreview.appendChild(video);
                } else if (file.type === 'application/pdf') {
                    const iframe = document.createElement('iframe');
                    iframe.src = URL.createObjectURL(file);
                    iframe.style.width = '100%';
                    iframe.style.height = '300px';
                    attachmentPreview.appendChild(iframe);
                } else {
                    const p = document.createElement('p');
                    p.textContent = 'Selected file: ' + file.name;
                    attachmentPreview.appendChild(p);
                }

                // Show modal
                attachmentModal.style.display = 'flex';
            });

            // Cancel attachment modal
            cancelAttachment.addEventListener('click', () => {
                attachmentModal.style.display = 'none';
                fileInput.value = '';
            });

            // Send attachment with message
            sendAttachment.addEventListener('click', () => {
                const file = fileInput.files[0];
                if (!file || !activeChatEmail) {
                    alert('No file selected or no active chat.');
                    return;
                }

                const messageText = attachmentMessageInput.value.trim();

                const formData = new FormData();
                formData.append('sender_email', loggedInEmail);
                formData.append('receiver_email', activeChatEmail);
                formData.append('message', messageText);
                formData.append('attachment', file);

                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal and clear inputs
                        attachmentModal.style.display = 'none';
                        fileInput.value = '';
                        attachmentMessageInput.value = '';

                        // Reload messages and update sidebar preview
                        loadMessages(activeChatEmail);
                        const chatItem = document.querySelector(`.chat-list .chat-item[data-email="${activeChatEmail}"]`);
                        if (chatItem) {
                            const chatPreview = chatItem.querySelector('.chat-preview');
                            if (chatPreview) {
                                chatPreview.textContent = messageText || 'Attachment';
                            }
                        }
                    } else {
                        alert(data.error || 'Failed to send message with attachment.');
                    }
                })
                .catch(err => {
                    console.error('Send message with attachment failed:', err);
                    alert('Failed to send message with attachment.');
                });
            });

            // Filter chat list based on search input
            function filterChatList(filter = '') {
                const chatItems = document.querySelectorAll('.chat-list .chat-item');
                let visibleCount = 0;

                chatItems.forEach(item => {
                    const name = item.querySelector('.chat-name').textContent;
                    if (name.toLowerCase().includes(filter.toLowerCase())) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (visibleCount === 0) {
                    const noResults = document.createElement('div');
                    noResults.style.padding = '20px';
                    noResults.style.textAlign = 'center';
                    noResults.style.color = 'var(--light-text)';
                    noResults.textContent = 'No ' + chatMemberRole.toLowerCase() + 's found';

                    // Remove any existing "no results" message
                    const existingNoResults = document.querySelector('.chat-list .no-results');
                    if (existingNoResults) {
                        existingNoResults.remove();
                    }

                    noResults.className = 'no-results';
                    document.getElementById('chatList').appendChild(noResults);
                } else {
                    // Remove any existing "no results" message
                    const existingNoResults = document.querySelector('.chat-list .no-results');
                    if (existingNoResults) {
                        existingNoResults.remove();
                    }
                }
            }

            // Open a chat
            function openChat(email, name) {
                console.log('openChat called for', email);
                activeChatEmail = email;
                activeChatName = name;

                // Update UI
                currentChatTitle.textContent = name;
                currentChatMembers.textContent = chatMemberRole;
                messageInput.disabled = false;
                messageInput.value = '';
                messageInput.focus();

                // Update profile picture in chat header
                const chatItem = document.querySelector(`.chat-list .chat-item[data-email="${email}"]`);
                const pfpImg = document.getElementById('currentChatPfp');
                if (chatItem && pfpImg) {
                    const pfpUrl = chatItem.getAttribute('data-pfp');
                    if (pfpUrl) {
                        pfpImg.src = pfpUrl;
                        pfpImg.style.display = 'block';
                    } else {
                        pfpImg.style.display = 'none';
                    }
                }

                // Highlight active chat in sidebar
                document.querySelectorAll('.chat-list .chat-item').forEach(item => {
                    if (item.dataset.email === email) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

            // Load messages
            loadMessages(email);

            // Removed polling to prevent blink effect
            // startPolling();

            // Mark messages as delivered when chat is opened
            markMessagesAsDelivered(email);

            // Mark messages as read when window is focused
            window.addEventListener('focus', () => {
                if (activeChatEmail) {
                    markMessagesAsRead(activeChatEmail);
                }
            });

            // Mark messages as read when scrolling messages container
            messagesContainer.addEventListener('scroll', () => {
                if (activeChatEmail) {
                    markMessagesAsRead(activeChatEmail);
                }
            });

            // Function to mark messages as delivered
            function markMessagesAsDelivered(chatWithEmail) {
                fetch('update_message_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        status: 'delivered',
                        chat_with: chatWithEmail
                    })
                });
            }

            // Function to mark messages as read
            function markMessagesAsRead(chatWithEmail) {
                if (loggedInEmail && chatWithEmail) {
                    fetch('update_message_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            status: 'read',
                            chat_with: chatWithEmail
                        })
                    }).then(() => {
                        // Update sidebar UI to remove unread styling and badge
                        const chatItem = document.querySelector(`.chat-list .chat-item[data-email="${chatWithEmail}"]`);
                        if (chatItem) {
                            const chatPreview = chatItem.querySelector('.chat-preview');
                            const unreadBadge = chatItem.querySelector('.unread-badge');
                            if (chatPreview) {
                                chatPreview.classList.remove('unread-message');
                                // Update last message text to latest message in chat
                                console.log('loggedInEmail:', loggedInEmail, 'chatWithEmail:', chatWithEmail);
                                if (loggedInEmail && chatWithEmail) {
                                    const url = `get_last_message.php?user_email=${encodeURIComponent(loggedInEmail)}&chat_with=${encodeURIComponent(chatWithEmail)}`;
                                    console.log('Fetching last message from URL:', url);
                                    fetch(url)
                                        .then(response => response.json())
                                        .then(data => {
                                            chatPreview.textContent = data.last_message || 'Click to start chat';
                                        })
                                        .catch(err => {
                                            console.error('Error fetching last message:', err);
                                        });
                                } else {
                                    console.error('Invalid loggedInEmail or chatWithEmail:', loggedInEmail, chatWithEmail);
                                }
                            }
                            if (unreadBadge) {
                                unreadBadge.style.display = 'none';
                                unreadBadge.textContent = '';
                            }
                        }
                    });
                } else {
                    console.error('Invalid parameters for markMessagesAsRead:', loggedInEmail, chatWithEmail);
                }
            }
            }

            // Load messages for a chat via AJAX
            function loadMessages(email) {
                messagesContainer.innerHTML = '<div class="system-message">Loading messages...</div>';

                fetch(`get_messages.php?user_email=${encodeURIComponent(loggedInEmail)}&chat_with=${encodeURIComponent(email)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            messagesContainer.innerHTML = `<div class="system-message">${data.error}</div>`;
                        } else {
                            renderMessages(data.messages);
                        }
                    })
                    .catch(() => {
                        messagesContainer.innerHTML = '<div class="system-message">Failed to load messages.</div>';
                    });
            }

            // Removed polling functions to prevent blink effect

            // Start polling for new messages every 3 seconds
            function startPolling() {}

            // Stop polling
            function stopPolling() {}

            // Render messages
// Render messages
function renderMessages(messages) {
    messagesContainer.innerHTML = '';

    if (!messages || messages.length === 0) {
        messagesContainer.innerHTML = '<div class="system-message">No messages yet. Start the conversation!</div>';
        return;
    }

    messages.forEach(msg => {
        const messageDiv = document.createElement('div');
        const isSent = msg.sender_email === loggedInEmail;
        messageDiv.className = 'message ' + (isSent ? 'sent' : 'received');

        let sender = isSent ? 'You' : activeChatName;
        let statusIcon = '';
        if (isSent) {
            if (msg.delivery_status === 'sent') {
                statusIcon = '✓';
            } else if (msg.delivery_status === 'delivered') {
                statusIcon = '✓✓';
            } else if (msg.delivery_status === 'read') {
                statusIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            }
        }

        // Determine if message is short
        const isShortMessage = msg.text.length < 20;
        if (isShortMessage) {
            messageDiv.classList.add('short-message');
        }

        // Build message content
        let messageContentHtml = '';
        
        // Add replied message preview if this is a reply
        if (msg.reply_to_message_id) {
            // Find the replied message in the messages array
            const repliedMessage = messages.find(m => m.message_id === msg.reply_to_message_id);
            let repliedSender = 'Unknown';
            if (repliedMessage) {
                repliedSender = repliedMessage.sender_email === loggedInEmail ? 'You' : activeChatName;
            } else if (msg.replied_content) {
                // Fallback to using replied_content if available
                repliedSender = msg.sender_email === loggedInEmail ? activeChatName : 'You';
            }
            let repliedContent = '';
            if (msg.replied_content && msg.replied_content.trim().length > 0) {
                repliedContent = msg.replied_content.length > 50 
                    ? msg.replied_content.substring(0, 50) + '...' 
                    : msg.replied_content;
            } else if (msg.replied_uploads) {
                repliedContent = '';
            } else {
                repliedContent = 'Original message not found';
            }

                let repliedAttachmentHtml = '';
                if (msg.replied_uploads) {
                    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                    const fileExt = msg.replied_uploads.split('.').pop().toLowerCase();

                    let repliedAttachmentPath = msg.replied_uploads;
                    if (!repliedAttachmentPath.toLowerCase().startsWith('uploads/')) {
                        repliedAttachmentPath = 'uploads/' + repliedAttachmentPath;
                    }

                    if (imageExtensions.includes(fileExt)) {
                        repliedAttachmentHtml = `<div><img src="${repliedAttachmentPath}" alt="Attachment" style="max-width: 100px; max-height: 100px; border-radius: 5px; margin-top: 4px;"></div>`;

                    } else {
                        const fileName = repliedAttachmentPath.split('/').pop();
                        repliedAttachmentHtml = `<div><a href="${repliedAttachmentPath}" target="_blank" rel="noopener noreferrer">Download attachment: ${fileName}</a></div>`;
                    }
                }
            
            messageContentHtml += `
                    <div class="reply-preview" style="border-left: 3px solid var(--primary-color); padding-left: 8px; margin-bottom: 8px; color: var(--light-text); font-size: 13px; cursor: pointer;" data-reply-id="${msg.reply_to_message_id}">
                        <div style="font-weight: bold; color: var(--primary-color);">Replying to ${repliedSender}</div>
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(repliedContent)}</div>
                        ${repliedAttachmentHtml}
                    </div>
            `;
        }

        // Add attachments if present
        if (msg.uploads) {
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            const fileExt = msg.uploads.split('.').pop().toLowerCase();

            // Fix path prefix for attachment if missing or incorrect case
            let attachmentPath = msg.uploads;
            if (!attachmentPath.startsWith('Uploads/') && !attachmentPath.startsWith('uploads/')) {
                attachmentPath = 'Uploads/' + attachmentPath;
            }

            if (imageExtensions.includes(fileExt)) {
                messageContentHtml += `<div><img src="${attachmentPath}" alt="Attachment" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-bottom: 5px;"></div>`;
            } else {
                const fileName = attachmentPath.split('/').pop();
                messageContentHtml += `<div><a href="${attachmentPath}" target="_blank" rel="noopener noreferrer">Download attachment: ${fileName}</a></div>`;
            }
        }

        // Add the main message text
        const escapedText = escapeHtml(msg.text).replace(/\n/g, '<br>');
        messageContentHtml += `<div>${escapedText}</div>`;

        // Create action buttons
        const actionButtons = document.createElement('div');
        actionButtons.className = 'message-actions';
        
        // Reply button for all messages
        actionButtons.innerHTML += `
            <button class="message-action-btn reply" title="Reply" data-message-id="${msg.message_id}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 17 4 12 9 7"></polyline>
                    <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                </svg>
            </button>
        `;
        
        // Edit and Delete buttons only for sender's messages
        if (isSent) {
            actionButtons.innerHTML += `
                <button class="message-action-btn edit" title="Edit" data-message-id="${msg.message_id}">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
                <button class="message-action-btn delete" title="Delete" data-message-id="${msg.message_id}">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            `;
        } else {
            // Report button only for receiver's messages
            actionButtons.innerHTML += `
                <button class="message-action-btn report" title="Report" data-message-id="${msg.message_id}">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </button>
            `;
        }

        // Set message HTML
        messageDiv.innerHTML = `
            ${!isSent ? `<div class="message-sender">${sender}</div>` : ''}
            <div class="message-content">${messageContentHtml}</div>
            <div class="message-info">
                <span class="message-time">${formatDateTime(msg.deliver_date)}</span>
                ${statusIcon ? `<span class="message-status">${statusIcon}</span>` : ''}
            </div>
        `;

        // Add edited indicator if needed
        if (msg.is_edited) {
            const editedSpan = document.createElement('span');
            editedSpan.className = 'edited-indicator';
            editedSpan.textContent = msg.edited_date ? 
                ` (edited at ${formatDateTime(msg.edited_date)})` : 
                ' (edited)';
            
            const messageInfo = messageDiv.querySelector('.message-info');
            if (messageInfo) {
                const timeElement = messageInfo.querySelector('.message-time');
                timeElement.insertAdjacentElement('afterend', editedSpan);
            }
        }

        messageDiv.appendChild(actionButtons);
        messagesContainer.appendChild(messageDiv);
            });

            // Add event listeners for the action buttons
            document.querySelectorAll('.message-action-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const messageId = btn.getAttribute('data-message-id');
                    
                    if (btn.classList.contains('reply')) {
                        handleReply(messageId);
                    } else if (btn.classList.contains('edit')) {
                        handleEdit(messageId);
                    } else if (btn.classList.contains('delete')) {
                        handleDelete(messageId);
                    } else if (btn.classList.contains('report')) {
                        handleReport(messageId);
                    }
                });
            });

            // Add event listener for reply preview click to scroll and highlight original message
            document.querySelectorAll('.reply-preview').forEach(replyPreview => {
                replyPreview.addEventListener('click', () => {
                    const replyId = replyPreview.getAttribute('data-reply-id');
                    if (!replyId) return;
                    const originalMessage = document.querySelector(`.message-action-btn.reply[data-message-id="${replyId}"]`)?.closest('.message');
                    if (originalMessage) {
                        originalMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        originalMessage.style.transition = 'background-color 0.5s ease';
                        originalMessage.style.backgroundColor = 'turquoise';
                        setTimeout(() => {
                            originalMessage.style.backgroundColor = '';
                        }, 1000);
                    }
                });
            });

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

// Add these handler functions to your JavaScript
function handleReply(messageId) {
    // Find the message element
    const messageElement = document.querySelector(`.message-action-btn.reply[data-message-id="${messageId}"]`).closest('.message');
    
    // Get message content and sender info
    const isSent = messageElement.classList.contains('sent');
    currentReplySenderName = isSent ? 'yourself' : activeChatName;
    currentReplyMessageId = messageId;
    
    // Get the message content HTML to include attachments
    const messageContentElement = messageElement.querySelector('.message-content');
    let replyHtml = messageContentElement.innerHTML;

    // Show the reply preview
    const replyPreview = document.getElementById('replyPreview');
    const replySenderName = document.getElementById('replySenderName');
    const replyPreviewContent = document.getElementById('replyPreviewContent');
    
    replySenderName.textContent = currentReplySenderName;
    replyPreviewContent.innerHTML = replyHtml;
    
    // Animate the reply preview
    replyPreview.style.display = 'block';
    replyPreview.style.opacity = '0';
    replyPreview.style.transform = 'translateY(20px)';
    replyPreview.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        replyPreview.style.opacity = '1';
        replyPreview.style.transform = 'translateY(0)';
    }, 10);
    
    // Focus the input
    messageInput.focus();
    
    // Add event listener for cancel button
    document.getElementById('cancelReply').addEventListener('click', cancelReply);
}

function cancelReply() {
    const replyPreview = document.getElementById('replyPreview');
    
    // Animate out
    replyPreview.style.opacity = '0';
    replyPreview.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        replyPreview.style.display = 'none';
        currentReplyMessageId = null;
        currentReplySenderName = null;
        currentReplyContent = null;
    }, 300);
}

function handleEdit(messageId) {
    const messageElement = document.querySelector(`.message-action-btn.edit[data-message-id="${messageId}"]`).closest('.message');
    const messageContentElement = messageElement.querySelector('.message-content');
    
    // Get original content
    const originalText = messageContentElement.cloneNode(true); // Clone to preserve HTML
    const textContent = originalText.textContent.trim();
    const attachment = originalText.querySelector('img, a[href*="uploads/"]');
    
    // Create modal - conditionally include attachment preview
    const modalHTML = `
    <div class="edit-modal-overlay">
        <div class="edit-modal">
            <h3>Edit Message</h3>
            
            ${attachment ? `
            <div class="attachment-preview">
                ${attachment.outerHTML}
            </div>
            ` : ''}
            
            <textarea class="edit-modal-textarea">${textContent}</textarea>
            
            <div class="edit-modal-actions">
                <button class="cancel-edit">Cancel</button>
                <button class="save-edit">Save Changes</button>
            </div>
        </div>
    </div>`;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = document.querySelector('.edit-modal-overlay');
    const textarea = modal.querySelector('textarea');
    
    // Focus and select text
    textarea.focus();
    textarea.select();
    
    // Save handler
    modal.querySelector('.save-edit').addEventListener('click', () => {
        const newContent = textarea.value.trim();
        if (newContent && newContent !== textContent) {
            updateMessage(messageId, newContent, messageElement);
        }
        modal.remove();
    });
    
    // Cancel handler
    modal.querySelector('.cancel-edit').addEventListener('click', () => {
        modal.remove();
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
    
    // Close on Escape key
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

function updateMessage(messageId, newContent, messageElement) {
    // Get the original attachment HTML
    const originalAttachment = messageElement.querySelector('.message-content img, .message-content a[href*="uploads/"]');
    
    fetch('update_message.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message_id: messageId,
            new_text: newContent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Rebuild message content
            const messageContentElement = messageElement.querySelector('.message-content');
            let newHtml = '';
            
            // Preserve original attachment if exists
            if (originalAttachment) {
                newHtml += originalAttachment.outerHTML + '<br>';
            }
            
            // Add the edited text
            newHtml += escapeHtml(newContent).replace(/\n/g, '<br>');
            
            // Update DOM
            messageContentElement.innerHTML = newHtml;
            
            // Update edited indicator
            const messageInfo = messageElement.querySelector('.message-info');
            if (messageInfo) {
                const existingIndicator = messageInfo.querySelector('.edited-indicator');
                const editedTime = data.edited_date || new Date().toISOString();
                
                if (existingIndicator) {
                    existingIndicator.textContent = ` (edited at ${formatDateTime(editedTime)})`;
                } else {
                    const editedSpan = document.createElement('span');
                    editedSpan.className = 'edited-indicator';
                    editedSpan.textContent = ` (edited at ${formatDateTime(editedTime)})`;
                    messageInfo.querySelector('.message-time').insertAdjacentElement('afterend', editedSpan);
                }
            }
        }
    })
    .catch(error => {
        console.error('Update failed:', error);
        alert('Failed to update message');
    });
}

function cancelEdit(messageElement, originalContent) {
    const messageContentElement = messageElement.querySelector('.message-content');
    messageContentElement.innerHTML = escapeHtml(originalContent).replace(/\n/g, '<br>');
}

function handleDelete(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch('delete_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message_id: messageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the message from the DOM
                const messageElement = document.querySelector(`.message-action-btn.delete[data-message-id="${messageId}"]`).closest('.message');
                if (messageElement) {
                    messageElement.remove();
                }
            } else {
                alert(data.error || 'Failed to delete message');
            }
        })
        .catch(err => {
            console.error('Delete message failed:', err);
            alert('Failed to delete message');
        });
    }
}

function handleReport(messageId) {
    const reason = prompt('Please enter the reason for reporting this message:');
    if (reason) {
        const reportBtn = document.querySelector(`.message-action-btn.report[data-message-id="${messageId}"]`);
        
        // Disable the button immediately to prevent multiple reports
        reportBtn.disabled = true;
        reportBtn.classList.add('reported');
        
        // Find the message element to get the sender's email
        const messageElement = reportBtn.closest('.message');
        const isSent = messageElement.classList.contains('sent');
        
        // The reported_by is the sender of the message
        const reported_by = isSent ? loggedInEmail : activeChatEmail;
        
        // Create the request data
        const requestData = { 
            message_id: messageId,
            reason: reason,
            reported_by: reported_by
        };
        
        console.log('Sending report data:', requestData);

        fetch('report_message.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json().catch(() => {
                throw new Error('Invalid JSON response');
            });
        })
        .then(data => {
            if (data.success) {
                alert('Message reported successfully. Thank you for your feedback.');
                // Change the icon to indicate it's been reported
                reportBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                `;
                reportBtn.title = "Message reported";
            } else {
                throw new Error(data.error || 'Failed to report message');
            }
        })
        .catch(err => {
            console.error('Report message failed:', err);
            alert('Failed to report message: ' + err.message);
            // Re-enable the button if report failed
            reportBtn.disabled = false;
            reportBtn.classList.remove('reported');
        });
    }
}

// Modify your sendMessage function to handle both new messages and edits
function sendMessage() {
    const text = messageInput.value.trim();
    if (!text || !activeChatEmail) return;

    const isEditing = messageInput.dataset.editingMessageId;
    const url = isEditing ? 'update_message.php' : 'send_message.php';
    const method = isEditing ? 'PUT' : 'POST';

    // Get the replied message content and attachment if available
    let repliedContent = null;
    let repliedUploads = null;
    if (currentReplyMessageId) {
        const repliedMessageElement = document.querySelector(`.message-action-btn.reply[data-message-id="${currentReplyMessageId}"]`)?.closest('.message');
        if (repliedMessageElement) {
            repliedContent = repliedMessageElement.querySelector('.message-content').textContent.trim();
            const attachmentElement = repliedMessageElement.querySelector('.message-content img, .message-content a[href*="uploads/"]');
            if (attachmentElement) {
                if (attachmentElement.tagName.toLowerCase() === 'img') {
                    repliedUploads = attachmentElement.getAttribute('src');
                } else if (attachmentElement.tagName.toLowerCase() === 'a') {
                    repliedUploads = attachmentElement.getAttribute('href');
                }
            }
        }
    }

    const messageData = {
    sender_email: loggedInEmail,
    receiver_email: activeChatEmail,
    message: text,
    ...(isEditing && { message_id: messageInput.dataset.editingMessageId }),
    ...(currentReplyMessageId && { 
        reply_to_message_id: currentReplyMessageId,
        replied_content: repliedContent,
        replied_uploads: repliedUploads
    })
};

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(messageData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            delete messageInput.dataset.editingMessageId;
            messageInput.placeholder = "Type a message...";
            
            // Clear reply if one was active
            if (currentReplyMessageId) {
                cancelReply();
            }
            
            loadMessages(activeChatEmail);
        } else {
            alert(data.error || 'Failed to send message');
        }
    })
    .catch(err => {
        console.error('Send message failed:', err);
        alert('Failed to send message');
    });
}

            // Helper functions
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function formatDateTime(dt, full = false) {
    if (!dt) return '';
    const date = new Date(dt.includes(' ') ? dt.replace(' ', 'T') : dt);
    if (isNaN(date.getTime())) return '';
    
    if (full) {
        return date.toLocaleString(); // Full date and time
    }
    
    const now = new Date();
    if (date.toDateString() === now.toDateString()) {
        // Today: show time only
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return `${hours}:${minutes} ${ampm}`;
    } else {
        // Show date and time
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
}

            // Event listeners
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            searchInput.addEventListener('input', (e) => {
                filterChatList(e.target.value);
            });

            // Add click event listeners to chat items
            document.querySelectorAll('.chat-list .chat-item').forEach(item => {
                item.addEventListener('click', () => {
                    const email = item.dataset.email;
                    const name = item.dataset.name;
                    openChat(email, name);
                });
            });

            // Function to fetch unread counts and update badges
            function fetchUnreadCounts() {
                fetch('get_unread_counts.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.unreadCounts) {
                            // Reset all badges
                            document.querySelectorAll('.chat-list .chat-item .unread-badge').forEach(badge => {
                                badge.style.display = 'none';
                                badge.textContent = '';
                            });

                            data.unreadCounts.forEach(item => {
                                const senderEmail = item.sender_email;
                                const count = parseInt(item.unread_count, 10);
                                if (count > 0) {
                                    const chatItem = document.querySelector(`.chat-list .chat-item[data-email="${senderEmail}"]`);
                                    if (chatItem) {
                                        const badge = chatItem.querySelector('.unread-badge');
                                        if (badge) {
                                            badge.style.display = 'flex';
                                            badge.textContent = count > 99 ? '99+' : count;
                                        }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Failed to fetch unread counts:', err);
                    });
            }

            // Initial fetch
            fetchUnreadCounts();

            // Poll every 5 seconds
            setInterval(fetchUnreadCounts, 5000);
        });

        // Add this to your existing JavaScript
document.getElementById('adminChatBtn').addEventListener('click', () => {
    // Fetch a random admin from the server
    fetch('get_random_admin.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.admin && data.admin.user_email) {
                // Redirect to admin_chat.php with the selected admin's email as query parameter
                const adminEmail = encodeURIComponent(data.admin.user_email);
                window.location.href = `admin_chat.php?admin_email=${adminEmail}`;
            } else {
                alert('Failed to get a random admin for chat.');
            }
        })
        .catch(err => {
            console.error('Error fetching random admin:', err);
            alert('Failed to get a random admin for chat.');
        });
});



    </script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const imageZoomModal = document.getElementById('imageZoomModal');
    const zoomedImage = document.getElementById('zoomedImage');

    // Delegate click event to images inside messages container, excluding images inside reply previews
    document.getElementById('messagesContainer').addEventListener('click', (e) => {
        if (e.target.tagName.toLowerCase() === 'img' && e.target.closest('.message-content') && !e.target.closest('.reply-preview')) {
            zoomedImage.src = e.target.src;
            imageZoomModal.style.display = 'flex';
        }
    });

    // Close modal on click outside image or on image
    imageZoomModal.addEventListener('click', (e) => {
        if (e.target === imageZoomModal || e.target === zoomedImage) {
            imageZoomModal.style.display = 'none';
            zoomedImage.src = '';
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && imageZoomModal.style.display === 'flex') {
            imageZoomModal.style.display = 'none';
            zoomedImage.src = '';
        }
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>
