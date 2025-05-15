<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user info from session
$logged_in_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
$logged_in_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$logged_in_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

if (!$logged_in_email || !$logged_in_role) {
    die("User not logged in.");
}

$chat_title = "Admin Support";
$search_placeholder = "Search admin...";
$chat_member_role = "Admin";

$sql = "SELECT name, user_email, role, pfp FROM user WHERE LOWER(role) = 'admin'";
$result = $conn->query($sql);

$chat_users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch last message between logged-in user and admin
        $userEmail = $row['user_email'];
        $lastMessage = null;
        $unreadCount = 0;
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
} else {
    die("No admin found in the system.");
}

// Use $logged_in_email and $logged_in_name for current user info in JS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Chat</title>
    <!-- Use the same CSS as in chat.php -->
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
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
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
            width: 100%;
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
    </style>
</head>
<body>
    <!-- Sidebar with chat list - simplified since we only have admin -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1><?php echo htmlspecialchars($chat_title); ?></h1>
        </div>
        <div class="chat-list" id="chatList">
            <?php if (is_array($chat_users) && count($chat_users) > 0): ?>
                <?php foreach ($chat_users as $user): ?>
                    <div class="chat-item" data-email="<?php echo htmlspecialchars($user['user_email']); ?>" data-name="<?php echo htmlspecialchars($user['user_name']); ?>" data-pfp="<?php echo htmlspecialchars($user['pfp']); ?>">
                        <?php
                            if (file_exists($user['pfp'])) {
                                echo '<img src="' . htmlspecialchars($user['pfp']) . '" class="chat-avatar" style="width:50px;height:50px;border-radius:50%;object-fit:cover;" alt="Avatar">';
                            } else {
                                echo '<div class="chat-avatar">' . strtoupper(substr($user['user_name'], 0, 2)) . '</div>';
                            }
                        ?>
                        <div class="chat-info">
                            <div class="chat-name"><?php echo htmlspecialchars($user['user_name']); ?></div>
                            <div class="chat-preview <?php echo ($user['unread_count'] > 0) ? 'unread-message' : ''; ?>"><?php echo htmlspecialchars($user['last_message'] ?: 'Click to start chat'); ?></div>
                        </div>
                        <div class="chat-meta">
                            <div class="unread-badge" style="display:<?php echo ($user['unread_count'] > 0) ? 'flex' : 'none'; ?>;"><?php echo ($user['unread_count'] > 99) ? '99+' : $user['unread_count']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 20px; text-align: center; color: var(--light-text)">No admin found</div>
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
                        <div class="chat-title" id="currentChatTitle">Admin Support</div>
                        <div class="chat-members" id="currentChatMembers"></div>
                    </div>
                </div>
                
                <!-- Right side buttons -->
                <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Add back button to return to main chat -->
            <button id="backToChatBtn" style="background: var(--primary-color); color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                Back to Chat
            </button>
                    
                    <!-- Dark mode toggle button -->
                    <button id="darkModeToggle" class="dark-mode-toggle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1-8.313-12.454z" class="dark-mode-icon"/>
                        </svg>
                    </button>
                </div>
            </div>
        
        <div class="messages-container" id="messagesContainer">
            <div class="system-message">Start chatting with admin for support</div>
        </div>
        
    <!-- Full-width input area background -->
    <div class="input-area" style="position: relative; display: flex; align-items: center;">
            <textarea class="message-input" placeholder="Type a message..." id="messageInput" disabled style="flex: 1; padding-right: 40px;"></textarea>
            <button class="attachment-button" id="attachmentButton">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M5 12H19" stroke="var(--text-color)" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </button>
    <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*,application/pdf" />
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
            const messageInput = document.getElementById('messageInput');
            const adminChatBtn = document.getElementById('adminChatBtn');
            
            // Attachment elements
            const attachmentButton = document.getElementById('attachmentButton');
            const fileInput = document.getElementById('fileInput');
            const attachmentModal = document.getElementById('attachmentModal');
            const attachmentPreview = document.getElementById('attachmentPreview');
            const attachmentMessageInput = document.getElementById('attachmentMessageInput');
            const cancelAttachment = document.getElementById('cancelAttachment');
            const sendAttachment = document.getElementById('sendAttachment');
            
            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            const currentTheme = localStorage.getItem('theme') || 
                                (prefersDarkScheme.matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', currentTheme);

            darkModeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                darkModeToggle.style.transform = newTheme === 'dark' ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            if (currentTheme === 'dark') {
                darkModeToggle.style.transform = 'rotate(180deg)';
            }

            let activeChatEmail = null;
            let activeChatName = null;
            // Back button functionality
            document.getElementById('backToChatBtn').addEventListener('click', () => {
                window.location.href = 'chat.php';
            });

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
            }

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
                                if (loggedInEmail && chatWithEmail) {
                                    const url = `get_last_message.php?user_email=${encodeURIComponent(loggedInEmail)}&chat_with=${encodeURIComponent(chatWithEmail)}`;
                                    fetch(url)
                                        .then(response => response.json())
                                        .then(data => {
                                            chatPreview.textContent = data.last_message || 'Click to start chat';
                                        })
                                        .catch(err => {
                                            console.error('Error fetching last message:', err);
                                        });
                                }
                            }
                            if (unreadBadge) {
                                unreadBadge.style.display = 'none';
                                unreadBadge.textContent = '';
                            }
                        }
                    });
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

                    // Escape HTML and replace newlines with <br> for multiline support
                    const escapedText = escapeHtml(msg.text).replace(/\n/g, '<br>');

                    // Build message content with attachment if present
                    let messageContentHtml = '';
                    if (msg.uploads) {
                        // Check file extension for image preview
                        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                        const fileExt = msg.uploads.split('.').pop().toLowerCase();
                        if (imageExtensions.includes(fileExt)) {
                            messageContentHtml += `<div><img src="${msg.uploads}" alt="Attachment" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-bottom: 5px;"></div>`;
                        } else {
                            // For other file types, show a download link
                            const fileName = msg.uploads.split('/').pop();
                            messageContentHtml += `<div><a href="${msg.uploads}" target="_blank" rel="noopener noreferrer">Download attachment: ${fileName}</a></div>`;
                        }
                    }

                    messageContentHtml += `<div>${escapedText}</div>`;

                    messageDiv.innerHTML = `
                        ${!isSent ? `<div class="message-sender">${sender}</div>` : ''}
                        <div class="message-content">${messageContentHtml}</div>
                        <div class="message-info">
                            <span class="message-time">${formatDateTime(msg.deliver_date)}</span>
                            ${statusIcon ? `<span class="message-status">${statusIcon}</span>` : ''}
                        </div>
                    `;

                    messagesContainer.appendChild(messageDiv);
                });

                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Send a new message via AJAX
            function sendMessage() {
                const text = messageInput.value.trim();
                if (!text || !activeChatEmail) {
                    console.error('sendMessage aborted: empty text or no active chat');
                    return;
                }

                console.log('Sending message:', text, 'to', activeChatEmail);

                messageInput.value = '';
                messageInput.disabled = true;

                fetch('send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        sender_email: loggedInEmail,
                        receiver_email: activeChatEmail,
                        message: text
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const chatItem = document.querySelector(`.chat-list .chat-item[data-email="${activeChatEmail}"]`);
                        if (chatItem) {
                            const chatPreview = chatItem.querySelector('.chat-preview');
                            if (chatPreview) {
                                chatPreview.textContent = text;
                            }
                        }
                        loadMessages(activeChatEmail);
                    } else {
                        alert(data.error || 'Failed to send message.');
                    }
                })
                .catch(err => {
                    console.error('Send message failed:', err);
                    alert('Failed to send message.');
                })
                .finally(() => {
                    messageInput.disabled = false;
                    messageInput.focus();
                });
            }

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

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDateTime(dt) {
            if (!dt) return '';
            const date = new Date(dt.replace(' ', 'T'));
            const now = new Date();

            if (date.toDateString() === now.toDateString()) {
                let hours = date.getHours();
                const minutes = date.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                return `${hours}:${minutes} ${ampm}`;
            } else {
                return date.toLocaleDateString();
            }
        }

        // Event listeners
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
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

        // Automatically open the first admin chat when page loads
        function autoOpenAdminChat() {
            // Removed auto open to let user select admin manually
        }

        // Initial fetch
        fetchUnreadCounts();

        // Poll every 5 seconds
        setInterval(fetchUnreadCounts, 5000);

        // Removed auto open call to let user select admin manually
        // autoOpenAdminChat();
        
    });
</script>
</body> 
</html>
<?php $conn->close(); ?>