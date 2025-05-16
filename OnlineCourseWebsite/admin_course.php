<?php include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// New Proposal Table
$sql_pending = "SELECT 
                course_proposal.title,
                user.name AS lecturer_name,
                course.update_date,
                course.course_id
                FROM 
                course_proposal
                JOIN 
                lecturer ON course_proposal.lecturer_id = lecturer.lecturer_id
                JOIN 
                user ON lecturer.user_email = user.user_email
                JOIN
                course on course_proposal.proposal_id = course.proposal_id
                WHERE status = 'Removal Pending'
                ORDER BY 
                course_proposal.submit_date DESC";
                
$result_pending = $conn->query($sql_pending);


$search = isset($_GET['search']) ? $_GET['search'] : '';
$search = $conn->real_escape_string($search); 


// Build SQL query to fetch only non-pending proposals
// Build SQL query to fetch only non-pending proposals
$sql = "SELECT 
            course_proposal.title,
            user.name AS lecturer_name,
            course.publish_date,
            course.status,
            course.course_id
        FROM 
            course_proposal
        JOIN 
            lecturer ON course_proposal.lecturer_id = lecturer.lecturer_id
        JOIN 
            user ON lecturer.user_email = user.user_email
        JOIN
            course ON course_proposal.proposal_id = course.proposal_id
        WHERE 
            course.status NOT IN ('Removal Pending', 'Removed')";


if (!empty($search)) {
    $sql .= " AND course_proposal.title LIKE '%$search%'";
}

// After adding search, now order it:
$sql .= " ORDER BY course_proposal.submit_date DESC";

$result = $conn->query($sql);


//UPDATE REMOVE COURSE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["course_id"]) && isset($_POST["reason"])) {

    $course_id = $_POST["course_id"];
    $reason = $_POST["reason"];
    $update_date = date("Y-m-d H:i:s"); 

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update course status
        $stmt = $conn->prepare("UPDATE course SET status = 'Removed', removal_reason = ?, update_date = ? WHERE course_id = ?");
        $stmt->bind_param("ssi", $reason, $update_date, $course_id);
        $stmt->execute();
        $stmt->close();

        // Delete from course_report
        $stmt2 = $conn->prepare("DELETE FROM course_report WHERE course_id = ?");
        $stmt2->bind_param("i", $course_id);
        $stmt2->execute();
        $stmt2->close();

        // Commit transaction
        $conn->commit();

        echo "Course removed successfully and related reports deleted.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error during course removal: " . $e->getMessage();
    }

    $conn->close();
    exit; 
}

//UPDATE REMOVAL COURSE REQUEST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"]) && isset($_POST["course_id"])) {
    $course_id = $_POST["course_id"];
    $new_status = $_POST["update_status"];
    $update_date = date("Y-m-d H:i:s"); 

    $stmt = $conn->prepare("UPDATE course SET status = ?, update_date = ? WHERE course_id = ?");
    $stmt->bind_param("ssi", $new_status, $update_date, $course_id);

    if ($stmt->execute()) {
        echo "Course have benn set to $new_status successfully.";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit;
}


include 'admin_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Course</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
</head>



<body>
<div class="main-content">
<br><br>
<h2>Course Removal Request</h2>

<table>
    <tr>
        <th>Course Name</th>    
        <th>Lecturer</th>
        <th>Publish Date</th>
        <th>View</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result_pending->num_rows > 0) {
        while($row = $result_pending->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . $row["update_date"] . "</td>";
            echo "<td><button class='viewProposalBtn' onclick='viewCourse(".$row["course_id"].");' type='button'>View</button></td>";
            echo "<td>
            <button type='button' onclick=\"openApproveModal('" . $row["course_id"] . "')\">Approve</button>
          </td>";
          echo "<td>
            <button type='button' onclick=\"openRejectModal('" . $row["course_id"] . "')\">Reject</button>
          </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No pending proposals found.</td></tr>";
    }
    ?>

</table>

<h2>Courses</h2>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Search by title..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
</form>

<table>
    <tr>
        <th>Title</th>    
        <th>Lecturer</th>
        <th>Date</th>
        <th>Status</th>
        <th>View</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . $row["publish_date"] . "</td>";
            echo "<td>" . $row["status"] . "</td>";
            echo "<td><button class='viewProposalBtn' onclick='viewCourse(".$row["course_id"].");' type='button'>View</button></td>";
            echo "<td><button type='button' onclick='showRemovalPrompt(" . $row["course_id"] . ")'>Remove</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No matching proposals found.</td></tr>";
    }

    $conn->close();
    ?>
</table>
</div>
<div id="removalModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3>Enter Removal Reason</h3>
    <textarea id="removalReason" rows="4" style="width:100%; resize: none;" placeholder="Reason for removal..."></textarea>
    <br><br>
    <button onclick="submitRemoval()">Confirm</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
  <div class="modal-content" id="approveModalContent">
    <p>Are you sure you want to approve this course removal?</p>
    <input type="hidden" id="approveCourseId">
    <button onclick="submitApproval()">Yes, Approve</button>
    <button type="button" onclick="closeModal('approveModal')">Cancel</button>
  </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
  <div class="modal-content" id="rejectModalContent">
    <p>Are you sure you want to reject this course removal?</p>
    <input type="hidden" id="rejectCourseId">
    <button onclick="submitRejection()">Yes, Reject</button>
    <button type="button" onclick="closeModal('rejectModal')">Cancel</button>
  </div>
</div>



</body>

<script>
let selectedCourseId = null;

function showRemovalPrompt(courseId) {
    selectedCourseId = courseId;
    document.getElementById("removalReason").value = "";
    document.getElementById("removalModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("removalModal").style.display = "none";
}

function submitApproval() {
    const courseId = document.getElementById("approveCourseId").value;

    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `course_id=${encodeURIComponent(courseId)}&update_status=Removed`
    })
    .then(response => response.text())
    .then(data => {
        const modalContent = document.getElementById("approveModalContent");
        modalContent.innerHTML = `
            <h3>Approved</h3>
            <p>${data}</p>
            <button onclick="confirmAndReload()">OK</button>
        `;
    })
    .catch(error => {
        alert("An error occurred: " + error);
    });
}

function submitRejection() {
    const courseId = document.getElementById("rejectCourseId").value;

    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `course_id=${encodeURIComponent(courseId)}&update_status=Active`
    })
    .then(response => response.text())
    .then(data => {
        const modalContent = document.getElementById("rejectModalContent");
        modalContent.innerHTML = `
            <h3>Rejected</h3>
            <p>${data}</p>
            <button onclick="confirmAndReload()">OK</button>
        `;
    })
    .catch(error => {
        alert("An error occurred: " + error);
    });
}

function submitRemoval() {
    const reason = document.getElementById("removalReason").value.trim();
    if (!reason) {
        alert("Please enter a reason.");
        return;
    }

    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `course_id=${encodeURIComponent(selectedCourseId)}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.text())
    .then(data => {
        const modalContent = document.querySelector("#removalModal .modal-content");
        modalContent.innerHTML = `
            <h3>Success</h3>
            <p>${data}</p>
            <br>
            <button onclick="confirmAndReload()">OK</button>
        `;
    })
    .catch(error => {
        alert("An error occurred: " + error);
    });
}

function confirmAndReload() {
    location.reload();
}

function openApproveModal(courseId) {
  document.getElementById('approveCourseId').value = courseId;
  document.getElementById('approveModal').style.display = 'flex';
}

function openRejectModal(courseId) {
  document.getElementById('rejectCourseId').value = courseId;
  document.getElementById('rejectModal').style.display = 'flex';
}

function closeModal(modalId = null) {
    if (modalId) {
        document.getElementById(modalId).style.display = 'none';
    } else {
        document.getElementById("removalModal").style.display = "none";
    }
}


// Optional: close modal when clicking outside
window.onclick = function(event) {
  const approveModal = document.getElementById('approveModal');
  const rejectModal = document.getElementById('rejectModal');
  if (event.target === approveModal) approveModal.style.display = "none";
  if (event.target === rejectModal) rejectModal.style.display = "none";
}

function viewCourse(proposalId) {
    window.location.href = "admin_view_course.php?course_id="+proposalId+"&originType=admin";
}
</script>

</html>