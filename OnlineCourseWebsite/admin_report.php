<?php
include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// --- GET TYPE ---
$type = isset($_GET['type']) ? $_GET['type'] : 'course';

// --- FUNCTION TO GET REPORTS ---
function getReports($conn, $type) {
    $table = '';
    $id_field = '';
    $name_fields = '';

    switch ($type) {
        case 'course':
            $table = 'course_report';
            $id_field = 'cr.course_id';
            $name_fields = "
                (SELECT cp.title FROM course c INNER JOIN course_proposal cp ON c.proposal_id = cp.proposal_id WHERE c.course_id = cr.course_id) AS item_name,
                (SELECT u.name FROM course c INNER JOIN lecturer l ON c.lecturer_id = l.lecturer_id INNER JOIN user u ON u.user_email = l.user_email WHERE c.course_id = cr.course_id) AS creator_name
            ";
            break;

        case 'post':
            $table = 'reports';
            $id_field = 'pr.post_id';
            $name_fields = "
                (SELECT fp.title FROM forum_post fp WHERE fp.post_id = pr.post_id) AS item_name,
                (SELECT u.name FROM forum_post fp INNER JOIN user u ON fp.author_email = u.user_email WHERE fp.post_id = pr.post_id) AS creator_name
            ";
            break;

        case 'review':
            $table = 'review_report';
            $id_field = 'rr.enrol_id';
            $name_fields = "
                (SELECT ce.review_title FROM course_enrolment ce WHERE ce.enrol_id = rr.enrol_id) AS item_name,
                (SELECT u.name 
                    FROM course_enrolment ce
                    INNER JOIN course c ON ce.course_id = c.course_id
                    INNER JOIN lecturer l ON c.lecturer_id = l.lecturer_id
                    INNER JOIN user u ON l.user_email = u.user_email
                    WHERE ce.enrol_id = rr.enrol_id
                ) AS creator_name
            ";
            break;

        case 'comment':
            $table = 'reply_reports';
            $id_field = 'crmt.reply_id';
            $name_fields = "
                (SELECT pc.reply_content FROM replies pc WHERE pc.reply_id = crmt.reply_id) AS item_name,
                (SELECT u.name FROM replies pc INNER JOIN user u ON pc.author_email = u.user_email WHERE pc.reply_id = crmt.reply_id) AS creator_name
            ";
            break;
    }

    if (!$table) return [];

    $alias = [
        'course' => 'cr',
        'post' => 'pr',
        'review' => 'rr',
        'comment' => 'crmt'
    ][$type];

    $query = "
        SELECT 
            $name_fields,
            COUNT(*) AS report_count,
            $id_field AS item_id
        FROM $table AS $alias
        GROUP BY $id_field
    ";

    $result = $conn->query($query);
    $reports = [];

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    return $reports;
}


// --- HANDLE DELETE COURSE ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_course' && isset($_GET['item_id']) && $type == 'course') {
    $course_id = intval($_GET['item_id']);
    $removal_reason = isset($_GET['reason']) ? $conn->real_escape_string($_GET['reason']) : '';

    if (empty($removal_reason)) {
        echo "Removal reason is required.";
        exit();
    }

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM course_report WHERE course_id = $course_id");
        $conn->query("UPDATE course SET status = 'Removed', removal_reason = '$removal_reason' WHERE course_id = $course_id");
        $conn->commit();
        header("Location: ?type=course");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}


// --- HANDLE DELETE POST ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_post' && isset($_GET['item_id']) && $type == 'post') {
    $post_id = intval($_GET['item_id']);

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM forum_post WHERE post_id = $post_id");
        $conn->query("DELETE FROM post_report WHERE post_id = $post_id");
        // Removed: DELETE FROM report_reason
        $conn->commit();
        header("Location: ?type=post");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// --- HANDLE DELETE COMMENT ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_comment' && isset($_GET['item_id']) && $type == 'comment') {
    $comment_id = intval($_GET['item_id']);

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM replies WHERE reply_id = $comment_id");
        $conn->query("DELETE FROM reply_report WHERE reply_id = $comment_id");
        // Removed: DELETE orphaned report reasons
        $conn->commit();
        header("Location: ?type=comment");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
// --- HANDLE DELETE REVIEW ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_review' && isset($_GET['item_id']) && $type == 'review') {
    $enrol_id = intval($_GET['item_id']);

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE course_enrolment SET 
            rating = NULL, 
            review_title = NULL, 
            review_comment = NULL, 
            review_date = NULL 
            WHERE enrol_id = $enrol_id");

        $conn->query("DELETE FROM review_report WHERE enrol_id = $enrol_id");

        $conn->commit();
        header("Location: ?type=review");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'remove_report' && isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    if ($type == 'course') {
        $conn->query("DELETE FROM course_report WHERE course_id = $item_id");
        header("Location: ?type=course");
    }
    elseif ($type == 'post') {
        $conn->query("DELETE FROM post_report WHERE post_id = $item_id");
        header("Location: ?type=post");
    }
    elseif ($type == 'review') {
        $conn->query("DELETE FROM review_report WHERE enrol_id = $item_id");
        header("Location: ?type=review");
    }
    
    elseif ($type == 'comment') {
        $conn->query("DELETE FROM replies_report WHERE reply_id = $item_id");
        header("Location: ?type=comment");
    }
    exit();
}

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Report Management</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="main-content">
    <h2>Reports Dashboard</h2>

    <!-- Buttons -->
    <form method="get" style="margin-bottom: 20px;">
        <button type="submit" name="type" value="post">Post</button>
        <button type="submit" name="type" value="comment">Comment</button>
    </form>

<?php
$reports = getReports($conn, $type);
if (count($reports) > 0) {
    echo "<table>";
    echo "<tr>";
    echo "<th>Comment</th>";
    if ($type == 'course' || $type == 'post' || $type == 'comment') echo "<th>Creator Name</th>";

    echo "<th>Number of Reports</th>";
    echo "<th>View</th>";
    echo "<th>Delete</th>";
    echo "<th>Remove Report</th>";
    echo "</tr>";

    foreach ($reports as $report) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($report['item_name']) . "</td>";

        if ($type == 'course' || $type == 'post' || $type == 'comment') {
            echo "<td>" . htmlspecialchars($report['creator_name']) . "</td>";
        }
        

        echo "<td>" . htmlspecialchars($report['report_count']) . "</td>";
        echo "<td><a href='?type=$type&view_id=" . $report['item_id'] . "'>View</a></td>";

        echo "<td>";
        if ($type == 'course') {
            echo "<button onclick='openModal(\"delete_course\", " . $report['item_id'] . ")'>Delete Course</button>";
        } elseif ($type == 'post') {
            echo "<button onclick='openModal(\"delete_post\", " . $report['item_id'] . ")'>Delete Post</button>";
        } elseif ($type == 'comment') {
            echo "<button onclick='openModal(\"delete_comment\", " . $report['item_id'] . ")'>Delete Comment</button>";
        } elseif ($type == 'review') {
            echo "<button onclick='openModal(\"delete_review\", " . $report['item_id'] . ")'>Delete Review</button>";
        } else {
            echo "-";
        }
        echo "</td>";

        

        echo "<td><button onclick='openModal(\"remove_report\", " . $report['item_id'] . ")'>Remove Report</button></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No reports found.</p>";
}

// --- VIEW DETAILS ---
if (isset($_GET['view_id'])) {
    $view_id = intval($_GET['view_id']);

    if ($type == 'course') {
        $result = $conn->query("
            SELECT u.name AS student_name, rr.report_type, cr.reason_desc
            FROM course_report cr
            INNER JOIN student s ON cr.student_id = s.student_id
            INNER JOIN user u ON s.user_email = u.user_email
            INNER JOIN report_reason rr ON cr.reason_id = rr.reason_id
            WHERE cr.course_id = $view_id

        ");
    } elseif ($type == 'post') {
        $result = $conn->query("
            SELECT u.name AS student_name, rr.report_type, pr.reason_desc
            FROM post_report pr
            INNER JOIN user u ON pr.user_email = u.user_email
            INNER JOIN report_reason rr ON pr.reason_id = rr.reason_id
            WHERE pr.post_id = $view_id

        ");
    }elseif ($type == 'comment') {
        $result = $conn->query("
            SELECT u.name AS student_name, rr.report_type, crmt.reason_desc
            FROM comment_report crmt
            INNER JOIN user u ON crmt.user_email = u.user_email
            INNER JOIN report_reason rr ON crmt.reason_id = rr.reason_id
            WHERE crmt.comment_id = $view_id

        ");
    }elseif ($type == 'review') {
        $result = $conn->query("
            SELECT u.name AS student_name, rr.report_type, rvr.reason_desc
            FROM review_report rvr
            INNER JOIN student s ON rvr.student_id = s.student_id
            INNER JOIN user u ON s.user_email = u.user_email
            INNER JOIN report_reason rr ON rvr.reason_id = rr.reason_id
            WHERE rvr.enrol_id = $view_id

        ");
    }

    if ($result && $result->num_rows > 0) {
        echo "<div id='detailSection' style='margin-top: 50px;'>
        <h3>Detailed Reports</h3>
        <table>";
        echo "<tr><th>Reporter Name</th><th>Report Type</th><th>Report Description</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['report_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['reason_desc']) . "</td>";
            echo "</tr>";
        }
        echo "</table></div>";
    } else {
        echo "<p>No detailed reports found.</p>";
    }
}
?>

</div>

<!-- Modal -->
<!-- Modal -->
<div id="confirmationModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Confirm Action</h2>
    <p id="modalMessage"></p>

    <div id="removalReasonSection" style="display: none; margin-top: 10px;">
      <label for="removalReason">Reason for Removal (required):</label><br>
      <textarea id="removalReason" rows="4" style="width: 100%; resize: none;"></textarea>
    </div>

    <div style="margin-top: 20px;">
      <button onclick="confirmAction()">Confirm</button>
      <button onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
let actionType = '';
let itemId = '';

function openModal(action, id) {
    actionType = action;
    itemId = id;
    const modal = document.getElementById('confirmationModal');
    const reasonSection = document.getElementById('removalReasonSection');
    const reasonInput = document.getElementById('removalReason');

    modal.style.display = 'flex';

    reasonInput.value = ''; // Clear on open
    reasonSection.style.display = (action === 'delete_course') ? 'block' : 'none';

    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');

    if (action === 'delete_course') {
        modalTitle.textContent = "Delete Course";
        modalMessage.textContent = "Are you sure you want to delete this course?";
    } else if (action === 'delete_post') {
        modalTitle.textContent = "Delete Post";
        modalMessage.textContent = "Are you sure you want to delete this post?";
    } else if (action === 'delete_comment') {
        modalTitle.textContent = "Delete Comment";
        modalMessage.textContent = "Are you sure you want to delete this comment?";
    } else if (action === 'delete_review') {
        modalTitle.textContent = "Delete Review";
        modalMessage.textContent = "Are you sure you want to delete this review? This action cannot be undone.";
    } else if (action === 'remove_report') {
        modalTitle.textContent = "Remove Report";
        modalMessage.textContent = "Are you sure you want to remove this report?";
    }
}


function closeModal() {
    document.getElementById('confirmationModal').style.display = 'none';
}

function confirmAction() {
    window.location.href = "?type=<?php echo $type; ?>&action=" + actionType + "&item_id=" + itemId;
    closeModal();
}

window.onclick = function(event) {
    const modal = document.getElementById('confirmationModal');
    if (event.target == modal) {
        closeModal();
    }
}
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('view_id')) {
        const detailSection = document.getElementById('detailSection');
        if (detailSection) {
            detailSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
}
function confirmAction() {
    if (actionType === 'delete_course') {
        const reason = document.getElementById('removalReason').value.trim();
        if (reason === '') {
            alert('Please provide a reason for removal.');
            return;
        }

        const encodedReason = encodeURIComponent(reason);
        window.location.href = `?type=course&action=delete_course&item_id=${itemId}&reason=${encodedReason}`;
    } else {
        window.location.href = `?type=<?php echo $type; ?>&action=${actionType}&item_id=${itemId}`;
    }
    closeModal();
}


</script>

</body>
</html>
