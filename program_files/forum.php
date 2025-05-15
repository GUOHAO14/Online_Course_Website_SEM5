<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

$user_name = $_SESSION["user_name"] ?? '';
$user_role = $_SESSION["role"] ?? '';

// Database configuration
$host = 'localhost';
$dbname = 'cocdb';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute a query to retrieve forum posts
    $stmt = $pdo->query('SELECT post_id, author, category, content, attachments, post_date, title FROM forum_post');

    // Fetch all posts
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forum - Recent Posts</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, sans-serif;
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
            padding: 5px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background-color: var(--hover-bg);
        }

        h1 {
            color: var(--secondary-color);
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        h2 {
            color: var(--primary-color);
            font-size: 18px;
            margin: 15px 0 10px 0;
        }

        .category-list {
            list-style-type: none;
            padding: 0;
            margin: 0 0 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-list li {
            padding: 5px 10px;
            color: var(--light-text);
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .category-list li:hover, .category-list li.active {
            background-color: var(--primary-color);
            color: white;
        }

        .divider {
            border: none;
            height: 1px;
            background-color: var(--border-color);
            margin: 20px 0;
        }

        .posts-container {
            margin-bottom: 30px;
        }

        .post {
            background-color: var(--card-bg);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px var(--shadow-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.3s ease;
            position: relative;
        }

        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-color);
            cursor: pointer;
        }

        .post-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
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
            z-index: 20;
            min-width: 120px;
        }

        .post-dropdown a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            text-decoration: none;
            color: var(--text-color);
        }

        .post-dropdown a:hover {
            background-color: var(--hover-bg);
        }

        .post-dropdown a.report-post {
            color: #ff6b6b;
            font-weight: bold;
        }

        .post-title {
            font-weight: bold;
            font-size: 16px;
            margin: 0 0 10px 0;
            color: var(--secondary-color);
        }

        .menu-icon {
            font-size: 18px;
            vertical-align: middle;
            margin-right: 6px;
        }

        .menu-icon-pen {
            display: inline-block;
            transform: scale(1.3);
            transform-origin: center;
        }

        .post-category {
            display: inline-block;
            background-color: var(--hover-bg);
            color: var(--primary-color);
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: capitalize;
        }

        .post-content {
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .post-meta {
            font-size: 12px;
            color: var(--light-text);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .post-votes {
            position: absolute;
            bottom: 10px;
            right: 15px;
            display: flex;
            flex-direction: row;
            align-items: center;
            font-size: 14px;
            user-select: none;
            color: var(--light-text);
            font-weight: 600;
            width: auto;
            gap: 8px;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            padding-left: 10px;
            padding-right: 10px;
            margin-left: 10px;
        }

        .vote-button {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 3px;
            border: 1px solid transparent;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            color: var(--secondary-color);
            font-weight: 600;
            user-select: none;
            margin-right: 4px;
        }

        .vote-button:hover {
            background-color: var(--hover-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .vote-button.active {
            box-shadow: none;
            color: #40e0d0;
        }

        [data-theme="dark"] .vote-button.active {
            color: #40e0d0;
        }

        .vote-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            user-select: none;
            color: var(--secondary-color);
            transition: color 0.3s ease;
        }

        .vote-count {
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
            margin: 0 6px;
            color: var(--secondary-color);
            user-select: none;
            min-width: 24px;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .search-section {
            margin-bottom: 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease, background-color 0.3s ease;
            font-family: inherit;
            background-color: var(--input-bg);
            color: var(--text-color);
        }

        .search-input:focus {
            border-color: var(--primary-color);
        }

        .top-posts {
            font-weight: bold;
            color: var(--secondary-color);
            margin-top: 15px;
            padding: 10px 0;
            border-top: 1px solid var(--border-color);
        }

        .hidden {
            display: none;
        }

        .add-forum-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-size: 30px;
            cursor: pointer;
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: background-color 0.3s ease, transform 0.3s ease;
            z-index: 1000;
        }

        .add-forum-button:hover {
            background-color: var(--secondary-color);
            transform: scale(1.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--modal-bg);
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px var(--shadow-color);
            position: relative;
            transition: background-color 0.3s ease;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: var(--light-text);
            cursor: pointer;
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        .modal h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .category-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-checkboxes label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            cursor: pointer;
            color: var(--text-color);
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-create {
            background-color: var(--primary-color);
            color: white;
            font-family: inherit;
        }

        .btn-create:hover {
            background-color: var(--secondary-color);
        }

        .btn-cancel {
            background-color: var(--light-text);
            color: var(--text-color);
            font-family: inherit;
        }

        .btn-cancel:hover {
            background-color: var(--border-color);
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .post-meta {
                gap: 8px;
            }

            .theme-toggle {
                top: 10px;
                right: 10px;
            }
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
    <div class="container">
        <button class="theme-toggle" id="themeToggle" title="Toggle Theme">🌙</button>
        <h1>Recent Posts</h1>
        
        <h2>Category</h2>
        <ul class="category-list" id="categoryFilter">
            <li class="active" data-category="all">All</li>
            <li data-category="discussion">Discussion</li>
            <li data-category="feedback">Feedback</li>
            <li data-category="personal stories">Personal Stories</li>
            <li data-category="tools & resources">Tools & Resources</li>
            <li data-category="tutorials">Tutorials</li>
        </ul>
        
        <div class="divider"></div>
        
        <div class="search-section">
            <input type="text" class="search-input" id="searchInput" placeholder="Search posts..." />
        </div>
        
        <div class="posts-container" id="postsContainer">
<?php
    if (!empty($posts)) {
        foreach ($posts as $post) {
            echo '<div class="post" data-id="' . htmlspecialchars($post['post_id']) . '">';
            echo '<div class="post-actions">';
            echo '<span class="post-menu">⋮</span>';
            echo '<div class="post-dropdown" style="display:none;">';
            if ($post['author'] === $user_name) {
                echo '<a href="#" class="edit-post"><span class="menu-icon menu-icon-pen">✎</span> Edit</a>';
                echo '<a href="#" class="delete-post"><span class="menu-icon">🗑</span> Delete</a>';
            } elseif ($user_role === 'admin') {
                // Admin can delete any post
                echo '<a href="#" class="delete-post"><span class="menu-icon">🗑</span> Delete</a>';
            } else {
                echo '<a href="#" class="report-post"><span class="menu-icon">❗</span> Report</a>';
            }
            echo '</div>';
            echo '</div>';
            $categories = explode(',', $post['category']);
            echo '<div class="post-categories">';
            foreach ($categories as $category) {
                $category = trim($category);
                echo '<span class="post-category">' . htmlspecialchars($category) . '</span> ';
            }
            echo '</div>';
            echo '<h3 class="post-title">';
            echo '<a href="discussion.php?post_id=' . htmlspecialchars($post['post_id']) . '" style="text-decoration: none; color: inherit;">' . htmlspecialchars($post['title']) . '</a>';
            echo '</h3>';
            echo '<div class="post-content">' . htmlspecialchars($post['content']) . '</div>';

            if (!empty($post['attachments'])) {
                $attachmentPath = htmlspecialchars($post['attachments']);
                $fileExtension = pathinfo($attachmentPath, PATHINFO_EXTENSION);
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                if (in_array(strtolower($fileExtension), $imageExtensions)) {
                    $webPath = strpos($attachmentPath, 'uploads/') === 0 ? $attachmentPath : 'uploads/' . ltrim($attachmentPath, '/');
                    echo '<div class="post-attachment"><img src="' . htmlspecialchars($webPath) . '" alt="Attachment" style="max-width: 100%; max-height: 300px; margin-top: 10px; border-radius: 5px;"></div>';
                } else {
                    echo '<div class="post-attachment"><a href="' . htmlspecialchars($attachmentPath) . '" target="_blank" rel="noopener noreferrer">View Attachment</a></div>';
                }
            }

            echo '<div class="post-meta">';
            echo '<span>Author: ' . htmlspecialchars($post['author']) . '</span>';
            echo '<span>Created: ' . htmlspecialchars($post['post_date']) . '</span>';
            echo '</div>';

            echo '<div class="post-votes" data-post-id="' . htmlspecialchars($post['post_id']) . '">';
            echo '<div class="vote-button vote-up" title="Upvote" tabindex="0">';
            echo '<span class="vote-icon">';
            echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
            echo '<path d="M12 4 L16 10 L13 10 L13 18 L11 18 L11 10 L8 10 Z" fill="currentColor"/>';
            echo '</svg>';
            echo '</span>';
            echo '</div>';
            echo '<div class="vote-count" title="Vote Count">0</div>';
            echo '<div class="vote-button vote-down" title="Downvote" tabindex="0">';
            echo '<span class="vote-icon">';
            echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
            echo '<path d="M12 20 L16 14 L13 14 L13 6 L11 6 L11 14 L8 14 Z" fill="currentColor"/>';
            echo '</svg>';
            echo '</span>';
            echo '</div>';
            echo '</div>';

            echo '</div>';
        }
    } else {
        echo '<p>No posts available.</p>';
    }
?>
        </div>
        <button class="add-forum-button" id="addForumBtn" title="Add Forum">+</button>

        <div class="modal" id="addForumModal">
            <div class="modal-content">
                <span class="modal-close" id="modalCloseBtn">×</span>
                <h2>Add New Forum Post</h2>
                <form id="addForumForm" method="POST" action="create_post.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select Categories:</label>
                        <div class="category-checkboxes">
                            <label><input type="checkbox" name="category" value="discussion" /> Discussion</label>
                            <label><input type="checkbox" name="category" value="feedback" /> Feedback</label>
                            <label><input type="checkbox" name="category" value="personal stories" /> Personal Stories</label>
                            <label><input type="checkbox" name="category" value="tools & resources" /> Tools & Resources</label>
                            <label><input type="checkbox" name="category" value="tutorials" /> Tutorials</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="forumTitle">Title (required):</label>
                        <input type="text" id="forumTitle" name="forumTitle" required />
                    </div>
                    <div class="form-group">
                        <label for="forumContent">Content (optional):</label>
                        <textarea id="forumContent" name="forumContent"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="forumAttachment">Attachment (optional):</label>
                        <input type="file" id="forumAttachment" name="attachment" accept="image/*,application/pdf" />
                    </div>
                    <div class="modal-buttons">
                        <button type="submit" class="btn btn-create">Create</button>
                        <button type="button" class="btn btn-cancel" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="reportPostModal" style="display:none;">
            <div class="modal-content">
                <span class="modal-close" id="reportModalCloseBtn">×</span>
                <h2>Report Post</h2>
                <form id="reportPostForm">
                    <div class="form-group">
                        <label>Select Report Categories:</label>
                        <div class="category-checkboxes">
                            <label><input type="checkbox" name="reportCategory" value="spam" /> Spam</label>
                            <label><input type="checkbox" name="reportCategory" value="harassment" /> Harassment</label>
                            <label><input type="checkbox" name="reportCategory" value="hate_speech" /> Hate Speech</label>
                            <label><input type="checkbox" name="reportCategory" value="off_topic" /> Off-topic</label>
                            <label><input type="checkbox" name="reportCategory" value="other" /> Other</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reportReason">Reason (optional):</label>
                        <textarea id="reportReason" name="reportReason" placeholder="Explain why you are reporting this post..."></textarea>
                    </div>
                    <div class="modal-buttons">
                        <button type="submit" class="btn btn-create">Submit</button>
                        <button type="button" class="btn btn-cancel" id="reportCancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="editPostModal" style="display:none;">
            <div class="modal-content">
                <span class="modal-close" id="editModalCloseBtn">×</span>
                <h2>Edit Forum Post</h2>
                <form id="editPostForm" enctype="multipart/form-data">
                    <input type="hidden" id="editPostId" name="post_id" />
                    <input type="hidden" id="removeAttachment" name="remove_attachment" value="0" />
                    <div class="form-group">
                        <label for="editForumTitle">Title (required):</label>
                        <input type="text" id="editForumTitle" name="title" required />
                    </div>
                    <div class="form-group">
                        <label for="editForumContent">Content (optional):</label>
                        <textarea id="editForumContent" name="content"></textarea>
                    </div>
                    <div class="form-group" id="currentAttachmentContainer" style="display:none;">
                        <label>Current Attachment:</label>
                        <div id="currentAttachmentPreview"></div>
                        <button type="button" id="removeAttachmentBtn" class="btn btn-cancel" style="margin-top: 5px;">Remove Image</button>
                        <button type="button" id="changeAttachmentBtn" class="btn btn-create" style="margin-top: 5px;">Change Image</button>
                    </div>
                    <div class="form-group" id="changeAttachmentContainer" style="display:none;">
                        <label for="editForumAttachment">New Attachment:</label>
                        <input type="file" id="editForumAttachment" name="attachment" accept="image/*,application/pdf" />
                    </div>
                    <div class="modal-buttons">
                        <button type="submit" class="btn btn-create">Save</button>
                        <button type="button" class="btn btn-cancel" id="editCancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const loggedInUserName = <?php echo json_encode($user_name); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            const postsContainer = document.getElementById('postsContainer');
            const categoryFilter = document.getElementById('categoryFilter');
            const searchInput = document.getElementById('searchInput');
            const addForumBtn = document.getElementById('addForumBtn');
            const addForumModal = document.getElementById('addForumModal');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const addForumForm = document.getElementById('addForumForm');
            const editPostModal = document.getElementById('editPostModal');
            const editModalCloseBtn = document.getElementById('editModalCloseBtn');
            const editCancelBtn = document.getElementById('editCancelBtn');
            const editPostForm = document.getElementById('editPostForm');
            const reportPostModal = document.getElementById('reportPostModal');
            const reportModalCloseBtn = document.getElementById('reportModalCloseBtn');
            const reportCancelBtn = document.getElementById('reportCancelBtn');
            const reportPostForm = document.getElementById('reportPostForm');
            const themeToggle = document.getElementById('themeToggle');

            let activeCategory = 'all';
            let searchQuery = '';
            let currentReportPostId = null;

            // Theme toggle functionality
            function toggleTheme() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
            }

            // Load saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            themeToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

            themeToggle.addEventListener('click', toggleTheme);

            const acronymMap = {
                'ai': 'artificial intelligence',
                'ml': 'machine learning',
                'nlp': 'natural language processing',
                // Add more acronyms and their expansions here as needed
            };

            function renderPosts() {
                const posts = Array.from(postsContainer.children);
                posts.forEach(post => {
                    const categoryElements = post.querySelectorAll('.post-category');
                    const categories = Array.from(categoryElements).map(el => el.textContent.toLowerCase());
                    const title = post.querySelector('.post-title').textContent.toLowerCase();
                    const content = post.querySelector('.post-content').textContent.toLowerCase();
                    const author = post.querySelector('.post-meta span').textContent.toLowerCase();

                    // Expand search query if it matches an acronym
                    let expandedQueries = [searchQuery];
                    if (acronymMap[searchQuery]) {
                        expandedQueries.push(acronymMap[searchQuery]);
                    }

                    const matchesCategory = activeCategory === 'all' || categories.includes(activeCategory);
                    const matchesSearch = expandedQueries.some(q =>
                        title.includes(q) || content.includes(q) || author.includes(q)
                    );

                    post.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
                });
            }

            categoryFilter.addEventListener('click', (e) => {
                if (e.target.tagName === 'LI') {
                    document.querySelectorAll('#categoryFilter li').forEach(li => li.classList.remove('active'));
                    e.target.classList.add('active');
                    activeCategory = e.target.dataset.category;
                    renderPosts();
                }
            });

            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value.toLowerCase();
                renderPosts();
            });

            addForumBtn.addEventListener('click', () => {
                addForumModal.style.display = 'block';
            });

            modalCloseBtn.addEventListener('click', () => {
                addForumModal.style.display = 'none';
                addForumForm.reset();
            });

            cancelBtn.addEventListener('click', () => {
                addForumModal.style.display = 'none';
                addForumForm.reset();
            });

            editModalCloseBtn.addEventListener('click', () => {
                editPostModal.style.display = 'none';
                editPostForm.reset();
            });

            editCancelBtn.addEventListener('click', () => {
                editPostModal.style.display = 'none';
                editPostForm.reset();
            });

            editPostForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const postId = document.getElementById('editPostId').value;
                const title = document.getElementById('editForumTitle').value.trim();
                const content = document.getElementById('editForumContent').value.trim();
                const removeAttachment = document.getElementById('removeAttachment').value;
                const attachmentInput = document.getElementById('editForumAttachment');
                const attachmentFile = attachmentInput.files[0];

                if (!title) {
                    alert('Title is required.');
                    return;
                }

                const formData = new FormData();
                formData.append('post_id', postId);
                formData.append('title', title);
                formData.append('content', content);
                formData.append('remove_attachment', removeAttachment);
                if (attachmentFile) {
                    formData.append('attachment', attachmentFile);
                }

                fetch('edit_post.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the post in the DOM
                        const postDiv = postsContainer.querySelector(`.post[data-id="${postId}"]`);
                        if (postDiv) {
                            const postTitleLink = postDiv.querySelector('.post-title a');
                            const postContentDiv = postDiv.querySelector('.post-content');
                            postTitleLink.textContent = title;
                            postContentDiv.textContent = content;
                            // Update attachment preview if needed
                            if (removeAttachment === '1' && !data.attachment) {
                                const attachmentDiv = postDiv.querySelector('.post-attachment');
                                if (attachmentDiv) {
                                    attachmentDiv.remove();
                                }
                            } else if (data.attachment) {
                                let attachmentDiv = postDiv.querySelector('.post-attachment');
                                if (!attachmentDiv) {
                                    attachmentDiv = document.createElement('div');
                                    attachmentDiv.className = 'post-attachment';
                                    postDiv.appendChild(attachmentDiv);
                                }
                                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                const ext = data.attachment.split('.').pop().toLowerCase();
                                let attachmentPath = data.attachment;
                                if (!attachmentPath.startsWith('uploads/')) {
                                    attachmentPath = 'uploads/' + attachmentPath;
                                }
                                if (imageExtensions.includes(ext)) {
                                    // Append timestamp to force reload
                                    const cacheBustedSrc = `${attachmentPath}?t=${new Date().getTime()}`;
                                    attachmentDiv.innerHTML = `<img src="${cacheBustedSrc}" alt="Attachment" style="max-width: 100%; max-height: 300px; margin-top: 10px; border-radius: 5px;">`;
                                } else {
                                    attachmentDiv.innerHTML = `<a href="${attachmentPath}" target="_blank" rel="noopener noreferrer">View Attachment</a>`;
                                }
                            }
                        }
                        alert('Post updated successfully!');
                        editPostModal.style.display = 'none';
                        editPostForm.reset();
                        document.getElementById('removeAttachment').value = '0';
                        document.getElementById('currentAttachmentContainer').style.display = 'none';
                        document.getElementById('changeAttachmentContainer').style.display = 'none';
                    } else {
                        alert('Failed to update post: ' + (data.error || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the post. Please try again later.');
                });
            });

            // Add event listeners for remove and change buttons in edit modal
            const removeAttachmentBtn = document.getElementById('removeAttachmentBtn');
            const changeAttachmentBtn = document.getElementById('changeAttachmentBtn');
            const removeAttachmentInput = document.getElementById('removeAttachment');
            const currentAttachmentContainer = document.getElementById('currentAttachmentContainer');
            const changeAttachmentContainer = document.getElementById('changeAttachmentContainer');
            const currentAttachmentPreview = document.getElementById('currentAttachmentPreview');
            const editForumAttachmentInput = document.getElementById('editForumAttachment');

            removeAttachmentBtn.addEventListener('click', () => {
                removeAttachmentInput.value = '1';
                currentAttachmentPreview.innerHTML = '';
                currentAttachmentContainer.style.display = 'none';
                changeAttachmentContainer.style.display = 'block';
            });

            changeAttachmentBtn.addEventListener('click', () => {
                changeAttachmentContainer.style.display = 'block';
            });

window.addEventListener('click', (e) => {
                if (e.target === addForumModal) {
                    addForumModal.style.display = 'none';
                    addForumForm.reset();
                }
                if (e.target === editPostModal) {
                    editPostModal.style.display = 'none';
                    editPostForm.reset();
                }
                if (e.target === reportPostModal) {
                    reportPostModal.style.display = 'none';
                    reportPostForm.reset();
                }
            });



            reportModalCloseBtn.addEventListener('click', () => {
                reportPostModal.style.display = 'none';
                reportPostForm.reset();
            });

            reportCancelBtn.addEventListener('click', () => {
                reportPostModal.style.display = 'none';
                reportPostForm.reset();
            });

            reportPostForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const selectedCategories = Array.from(reportPostForm.querySelectorAll('input[name="reportCategory"]:checked')).map(checkbox => checkbox.value);
                const reason = reportPostForm.reportReason.value.trim();

                if (selectedCategories.length === 0) {
                    alert('Please select at least one report category.');
                    return;
                }

                const reportData = {
                    post_id: currentReportPostId,
                    report_categories: selectedCategories,
                    reason: reason,
                };

                fetch('report_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(reportData),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Report submitted successfully!');
                        reportPostModal.style.display = 'none';
                        reportPostForm.reset();
                    } else {
                        alert('Failed to submit report: ' + (data.error || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the report. Please try again later.');
                });
            });

            addForumForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const selectedCategories = Array.from(addForumForm.querySelectorAll('input[name="category"]:checked')).map(checkbox => checkbox.value);
                const title = addForumForm.forumTitle.value.trim();
                const content = addForumForm.forumContent.value.trim();
                const attachmentInput = document.getElementById('forumAttachment');
                const attachmentFile = attachmentInput.files[0];

                if (selectedCategories.length === 0) {
                    alert('Please select at least one category.');
                    return;
                }

                if (!title) {
                    alert('Title is required.');
                    return;
                }

                function formatDate(date) {
                    const d = new Date(date);
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0'); // Months are zero-based
                    const day = String(d.getDate()).padStart(2, '0');
                    const hours = String(d.getHours()).padStart(2, '0');
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    const seconds = String(d.getSeconds()).padStart(2, '0');

                    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                }

                const formData = new FormData();
                formData.append('forumTitle', title);
                formData.append('forumContent', content);
                formData.append('category', selectedCategories.join(', '));
                // Removed author field to let server set it from session
                formData.append('post_date', formatDate(new Date()));
                if (attachmentFile) {
                    formData.append('attachment', attachmentFile);
                }

                fetch('create_post.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Log the response to see the error message
                    if (data.success) {
                        alert('Post created successfully!');
                        addForumModal.style.display = 'none';
                        addForumForm.reset();

                        // Append new post to DOM
                        const postId = data.post_id || Date.now(); // Use returned post_id or fallback to timestamp
                        const attachment = data.attachment;
                        console.log('Attachment path:', attachment);

                        const postDiv = document.createElement('div');
                        postDiv.className = 'post';
                        postDiv.setAttribute('data-id', postId);

                        // Create post actions div
                        const postActionsDiv = document.createElement('div');
                        postActionsDiv.className = 'post-actions';

                        const postMenuSpan = document.createElement('span');
                        postMenuSpan.className = 'post-menu';
                        postMenuSpan.textContent = '⋮';

                        const postDropdownDiv = document.createElement('div');
                        postDropdownDiv.className = 'post-dropdown';
                        postDropdownDiv.style.display = 'none';

                        // Add edit and delete or report links
                        if ('You' === 'You') { // Since author is 'You'
                            const editLink = document.createElement('a');
                            editLink.href = '#';
                            editLink.className = 'edit-post';
                            editLink.innerHTML = '<span class="menu-icon menu-icon-pen">&#9998;</span> &nbsp Edit';
                            postDropdownDiv.appendChild(editLink);

                            const deleteLink = document.createElement('a');
                            deleteLink.href = '#';
                            deleteLink.className = 'delete-post';
                            deleteLink.innerHTML = '<span class="menu-icon">&#128465;</span> Delete';
                            postDropdownDiv.appendChild(deleteLink);
                        } else {
                            const reportLink = document.createElement('a');
                            reportLink.href = '#';
                            reportLink.className = 'report-post';
                            reportLink.style.color = 'red';
                            reportLink.innerHTML = '<span class="menu-icon">&#10071;</span> Report';
                            postDropdownDiv.appendChild(reportLink);
                        }

                        postActionsDiv.appendChild(postMenuSpan);
                        postActionsDiv.appendChild(postDropdownDiv);
                        postDiv.appendChild(postActionsDiv);

                        // Post categories
                        const postCategoriesDiv = document.createElement('div');
                        postCategoriesDiv.className = 'post-categories';
                        selectedCategories.forEach(cat => {
                            const span = document.createElement('span');
                            span.className = 'post-category';
                            span.textContent = cat;
                            postCategoriesDiv.appendChild(span);
                            postCategoriesDiv.appendChild(document.createTextNode(' '));
                        });
                        postDiv.appendChild(postCategoriesDiv);

                        // Post title
                        const postTitleH3 = document.createElement('h3');
                        postTitleH3.className = 'post-title';
                        const postTitleLink = document.createElement('a');
                        postTitleLink.href = `discussion.php?post_id=${postId}`;
                        postTitleLink.style.textDecoration = 'none';
                        postTitleLink.style.color = 'inherit';
                        postTitleLink.textContent = title;
                        postTitleH3.appendChild(postTitleLink);
                        postDiv.appendChild(postTitleH3);

                        // Post content
                        const postContentDiv = document.createElement('div');
                        postContentDiv.className = 'post-content';
                        postContentDiv.textContent = content;
                        postDiv.appendChild(postContentDiv);

                        // Add attachment if present
                        if (attachment) {
                            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                            const ext = attachment.split('.').pop().toLowerCase();
                            let attachmentHTML = '';
                            if (imageExtensions.includes(ext)) {
                                attachmentHTML = `<div class="post-attachment"><img src="${attachment}" alt="Attachment" style="max-width: 100%; max-height: 300px; margin-top: 10px; border-radius: 5px;"></div>`;
                            } else {
                                attachmentHTML = `<div class="post-attachment"><a href="${attachment}" target="_blank" rel="noopener noreferrer">View Attachment</a></div>`;
                            }
                            postDiv.insertAdjacentHTML('beforeend', attachmentHTML);
                        }

                        // Post meta
                    const postMetaDiv = document.createElement('div');
                    postMetaDiv.className = 'post-meta';
                    const authorSpan = document.createElement('span');
                    authorSpan.textContent = 'Author: ' + loggedInUserName;
                    const dateSpan = document.createElement('span');
                    const now = new Date();
                    dateSpan.textContent = `Created ${now.toISOString().slice(0, 19).replace('T', ' ')}`;
                    postMetaDiv.appendChild(authorSpan);
                    postMetaDiv.appendChild(dateSpan);
                    postDiv.appendChild(postMetaDiv);

                        // Insert new post at the top before the first post element
                        const firstPost = postsContainer.querySelector('.post');
                        if (firstPost) {
                            postsContainer.insertBefore(postDiv, firstPost);
                        } else {
                            postsContainer.appendChild(postDiv);
                        }

                        // Attach event listeners for new post
                        postMenuSpan.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const dropdown = this.nextElementSibling;
                            document.querySelectorAll('.post-dropdown').forEach(d => {
                                if (d !== dropdown) d.style.display = 'none';
                            });
                            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                        });

                        if (postDiv.querySelector('.edit-post')) {
                            postDiv.querySelector('.edit-post').addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                const postElement = e.target.closest('.post');
                                const postId = postElement.getAttribute('data-id');
                                const title = postElement.querySelector('.post-title a').textContent;
                                const content = postElement.querySelector('.post-content').textContent;
                                document.getElementById('editPostId').value = postId;
                                document.getElementById('editForumTitle').value = title;
                                document.getElementById('editForumContent').value = content;
                                editPostModal.style.display = 'block';
                            });
                        }

                        if (postDiv.querySelector('.delete-post')) {
                            postDiv.querySelector('.delete-post').addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                if (!confirm('Are you sure you want to delete this post?')) {
                                    return;
                                }
                                const postElement = e.target.closest('.post');
                                const postId = postElement.getAttribute('data-id');
                                fetch('delete_post.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ post_id: postId }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert('Post deleted successfully!');
                                        postElement.remove();
                                    } else {
                                        alert('Failed to delete post: ' + (data.error || 'Unknown error.'));
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while deleting the post. Please try again later.');
                                });
                            });
                        }

                        if (postDiv.querySelector('.report-post')) {
                            postDiv.querySelector('.report-post').addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                const postElement = e.target.closest('.post');
                                currentReportPostId = postElement.getAttribute('data-id');
                                reportPostModal.style.display = 'block';
                            });
                        }

                        // Add click event to postDiv to redirect to discussion.php?post_id=...
                        postDiv.addEventListener('click', (e) => {
                            // Prevent redirect if click is on a link or menu
                            if (e.target.tagName.toLowerCase() === 'a' || e.target.classList.contains('post-menu') || e.target.closest('.post-dropdown')) {
                                e.stopPropagation();
                                return;
                            }
                            window.location.href = `discussion.php?post_id=${postId}`;
                        });

                    } else {
                        alert('Failed to create post: ' + (data.error || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating the post. Please try again later.');
                });
            });

            // Attach event listeners for edit, delete, and report post actions
document.getElementById('postsContainer').addEventListener('click', function(e) {
                const target = e.target;
                const postElement = target.closest('.post');
                if (!postElement) return;

                // Toggle post dropdown menu
                if (target.classList.contains('post-menu')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dropdown = target.nextElementSibling;
                    document.querySelectorAll('.post-dropdown').forEach(d => {
                        if (d !== dropdown) d.style.display = 'none';
                    });
                    dropdown.style.display = 'block';
                    return;
                }
            
                // Edit post
                if (target.classList.contains('edit-post') || target.closest('.edit-post')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const postId = postElement.getAttribute('data-id');
                    const title = postElement.querySelector('.post-title a').textContent;
                    const content = postElement.querySelector('.post-content').textContent;

                    document.getElementById('editPostId').value = postId;
                    document.getElementById('editForumTitle').value = title;
                    document.getElementById('editForumContent').value = content;

                    // Set current attachment preview if exists
                    const currentAttachmentContainer = document.getElementById('currentAttachmentContainer');
                    const currentAttachmentPreview = document.getElementById('currentAttachmentPreview');
                    const changeAttachmentContainer = document.getElementById('changeAttachmentContainer');
                    const removeAttachmentInput = document.getElementById('removeAttachment');
                    const editForumAttachmentInput = document.getElementById('editForumAttachment');
                    const removeAttachmentBtn = document.getElementById('removeAttachmentBtn');
                    const changeAttachmentBtn = document.getElementById('changeAttachmentBtn');

                    // Reset attachment controls
                    removeAttachmentInput.value = '0';
                    editForumAttachmentInput.value = '';
                    changeAttachmentContainer.style.display = 'none';

                    // Find attachment URL from post element
                    const attachmentDiv = postElement.querySelector('.post-attachment');
                    if (attachmentDiv) {
                        const img = attachmentDiv.querySelector('img');
                        const link = attachmentDiv.querySelector('a');
                        let attachmentUrl = '';
                        if (img) {
                            attachmentUrl = img.src;
                        } else if (link) {
                            attachmentUrl = link.href;
                        }
                        if (attachmentUrl) {
                            currentAttachmentPreview.innerHTML = '';
                            if (img) {
                                const newImg = document.createElement('img');
                                newImg.src = attachmentUrl;
                                newImg.style.maxWidth = '100%';
                                newImg.style.maxHeight = '150px';
                                newImg.style.borderRadius = '5px';
                                currentAttachmentPreview.appendChild(newImg);
                            } else if (link) {
                                const newLink = document.createElement('a');
                                newLink.href = attachmentUrl;
                                newLink.target = '_blank';
                                newLink.rel = 'noopener noreferrer';
                                newLink.textContent = 'View Attachment';
                                currentAttachmentPreview.appendChild(newLink);
                            }
                            currentAttachmentContainer.style.display = 'block';
                            changeAttachmentContainer.style.display = 'none';
                        } else {
                            currentAttachmentContainer.style.display = 'none';
                            changeAttachmentContainer.style.display = 'block';
                        }
                    } else {
                        currentAttachmentContainer.style.display = 'none';
                        changeAttachmentContainer.style.display = 'block';
                    }

                    // Add event listeners for remove and change buttons
                    removeAttachmentBtn.onclick = () => {
                        removeAttachmentInput.value = '1';
                        currentAttachmentPreview.innerHTML = '';
                        currentAttachmentContainer.style.display = 'none';
                        changeAttachmentContainer.style.display = 'block';
                    };

                    changeAttachmentBtn.onclick = () => {
                        changeAttachmentContainer.style.display = 'block';
                    };

                    document.getElementById('editPostModal').style.display = 'block';
                    return;
                }

                // Delete post
                if (target.classList.contains('delete-post') || target.closest('.delete-post')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!confirm('Are you sure you want to delete this post?')) {
                        return;
                    }
                    const postId = postElement.getAttribute('data-id');
                    fetch('delete_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ post_id: postId }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Post deleted successfully!');
                            postElement.remove();
                        } else {
                            alert('Failed to delete post: ' + (data.error || 'Unknown error.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the post. Please try again later.');
                    });
                    return;
                }

                // Report post
                if (target.classList.contains('report-post') || target.closest('.report-post')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const postElement = target.closest('.post');
                    currentReportPostId = postElement.getAttribute('data-id');
                    reportPostModal.style.display = 'block';
                    return;
                }

            // Redirect to discussion.php on post click except on links or menu or vote buttons
            if (!target.closest('a') && !target.classList.contains('post-menu') && !target.closest('.post-dropdown') && !target.closest('.vote-button')) {
                const postId = postElement.getAttribute('data-id');
                if (postId) {
                    window.location.href = `discussion.php?post_id=${postId}`;
                }
            }
            });

            // Hide post dropdown menus when clicking outside
            document.addEventListener('click', (e) => {
                const isClickInsideMenu = e.target.closest('.post-menu') || e.target.closest('.post-dropdown');
                if (!isClickInsideMenu) {
                    document.querySelectorAll('.post-dropdown').forEach(d => {
                        d.style.display = 'none';
                    });
                }
            });

        });

        // Voting functionality
        (function() {
            const postsContainer = document.getElementById('postsContainer');

            // Store user votes locally for quick UI update (optional)
            const userVotes = {};

            // Fetch votes for all posts on page load
            function fetchVotes() {
                const postVoteContainers = postsContainer.querySelectorAll('.post-votes');
                const postIds = Array.from(postVoteContainers).map(div => div.dataset.postId);
                if (postIds.length === 0) return;

                fetch(`get_post_votes.php?post_ids=${postIds.join(',')}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const counts = data.counts || {};
                            const votes = data.userVotes || {};

                            postVoteContainers.forEach(container => {
                                const postId = container.dataset.postId;
                                const upBtn = container.querySelector('.vote-up');
                                const downBtn = container.querySelector('.vote-down');
                            const voteCountDiv = container.querySelector('.vote-count');

                            const upVotes = counts[postId]?.upVotes || 0;
                            const downVotes = counts[postId]?.downVotes || 0;
                            const userVote = votes[postId];

                            voteCountDiv.textContent = upVotes - downVotes;

                            // Remove active class
                            upBtn.classList.remove('active');
                            downBtn.classList.remove('active');
                            upBtn.querySelector('.vote-icon').style.color = '';
                            downBtn.querySelector('.vote-icon').style.color = '';
                            voteCountDiv.style.color = '';

                            if (userVote === 1) {
                                upBtn.classList.add('active');
                                upBtn.querySelector('.vote-icon').style.color = '#40e0d0';
                                voteCountDiv.style.color = '#40e0d0';
                                downBtn.querySelector('.vote-icon').style.color = '';
                            } else if (userVote === 0) {
                                downBtn.classList.add('active');
                                downBtn.querySelector('.vote-icon').style.color = '#ff6b6b';
                                voteCountDiv.style.color = '#ff6b6b';
                                upBtn.querySelector('.vote-icon').style.color = '';
                            } else {
                                upBtn.querySelector('.vote-icon').style.color = '';
                                downBtn.querySelector('.vote-icon').style.color = '';
                                voteCountDiv.style.color = '';
                            }

                                userVotes[postId] = userVote;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching votes:', error);
                    });
            }

            // Handle vote button click
            postsContainer.addEventListener('click', (e) => {
                const target = e.target.closest('.vote-button');
                if (!target) return;

                e.stopPropagation();
                e.preventDefault();

                const postVotesDiv = target.closest('.post-votes');
                if (!postVotesDiv) return;

                const postId = postVotesDiv.dataset.postId;
                const isUpvote = target.classList.contains('vote-up') ? 1 : 0;
                const currentVote = userVotes[postId];

                let newVote;
                if (currentVote === isUpvote) {
                    // Toggle off
                    newVote = null;
                } else {
                    // Switch or new vote
                    newVote = isUpvote;
                }

                // Prepare form data
                const formData = new FormData();
                formData.append('post_id', postId);
                if (newVote === null) {
                    formData.append('is_upvote', currentVote);
                } else {
                    formData.append('is_upvote', newVote);
                }

                // For now, no user_id sent, backend uses session

                // Prevent post click redirect
                e.preventDefault();
                e.stopPropagation();

                fetch('vote_post.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Vote response:', data);
                    if (data.success) {
                        const upBtn = postVotesDiv.querySelector('.vote-up');
                        const downBtn = postVotesDiv.querySelector('.vote-down');
                        const voteCountDiv = postVotesDiv.querySelector('.vote-count');

                        voteCountDiv.textContent = data.upVotes - data.downVotes;

                        // Clear all active classes and colors first
                        upBtn.classList.remove('active');
                        downBtn.classList.remove('active');
                        upBtn.querySelector('.vote-icon').style.color = '';
                        downBtn.querySelector('.vote-icon').style.color = '';
                        voteCountDiv.style.color = '';

                        if (data.userVote === 1) {
                            upBtn.classList.add('active');
                            upBtn.querySelector('.vote-icon').style.color = '#40e0d0';
                            voteCountDiv.style.color = '#40e0d0';
                        } else if (data.userVote === 0) {
                            downBtn.classList.add('active');
                            downBtn.querySelector('.vote-icon').style.color = '#ff6b6b';
                            voteCountDiv.style.color = '#ff6b6b';
                        }

                        userVotes[postId] = data.userVote;
                    } else {
                        alert('Failed to submit vote: ' + (data.error || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error submitting vote:', error);
                    alert('An error occurred while submitting your vote. Please try again later.');
                });
            });

            // Initial fetch of votes
            fetchVotes();
        })();

    </script>
</body>
</html>