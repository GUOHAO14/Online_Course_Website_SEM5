<?php include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// New Proposal Table
$sql_pending = 
"SELECT 
    course_proposal.proposal_id,
    course_proposal.title,
    user.name AS lecturer_name,
    course_proposal.submit_date,
    course_proposal.lecturer_id,
    course_proposal.approval_date
FROM 
    course_proposal
JOIN 
    lecturer ON course_proposal.lecturer_id = lecturer.lecturer_id
JOIN 
    user ON lecturer.user_email = user.user_email
WHERE 
    course_proposal.approval_status = 'Pending'
ORDER BY 
    course_proposal.submit_date DESC";

                
$result_pending = $conn->query($sql_pending);




$search = isset($_GET['search']) ? $_GET['search'] : '';
$search = $conn->real_escape_string($search); 


// Search
$sql = "SELECT 
    course_proposal.proposal_id,
    course_proposal.title,
    user.name AS lecturer_name,
    course.publish_date,
    course_proposal.approval_status,
    course_proposal.lecturer_id,
    course_proposal.approval_date
FROM 
    course_proposal
JOIN 
    lecturer ON course_proposal.lecturer_id = lecturer.lecturer_id
JOIN 
    user ON lecturer.user_email = user.user_email
LEFT JOIN 
    course ON course_proposal.proposal_id = course.proposal_id
WHERE 
    course_proposal.approval_status != 'Pending'";



if (!empty($search)) {
    $sql .= " AND course_proposal.title LIKE '%$search%'";
}

$sql .= " ORDER BY course_proposal.approval_date DESC";
$result = $conn->query($sql);

// Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_id'])) {
    $reject_id = intval($_POST['reject_id']); // sanitize input

    $approval_date = date('Y-m-d H:i:s');

    $update_sql = "UPDATE course_proposal SET approval_status = 'Rejected', approval_date = '$approval_date' WHERE proposal_id = $reject_id";

    if ($conn->query($update_sql) === TRUE) {
        // Optional: success message
    } else {
        echo "Error updating status: " . $conn->error;
    }

    // Refresh to update table view
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Approve
if (isset($_POST['approve'])) {
    $approve_id = $_POST['approve_id']; // Get proposal_id
    $lecturer_id = $_POST['lecturer_id']; // Get lecturer_id

    // Get current time for publish_date
    $publish_date = date('Y-m-d H:i:s');  // current date and time

    // Insert into course table
    $insert_sql = "INSERT INTO course (publish_date, status, lecturer_id, proposal_id) 
                   VALUES ('$publish_date', 'Unpublished', '$lecturer_id', '$approve_id')";

    if ($conn->query($insert_sql) === TRUE) {
        echo "Course record created successfully";
    } else {
        echo "Error: " . $insert_sql . "<br>" . $conn->error;
    }

    // Optionally, update the approval status of the proposal
    $update_sql = "UPDATE course_proposal SET approval_status = 'Approved', approval_date = '$publish_date' WHERE proposal_id = '$approve_id'";

    $conn->query($update_sql);

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Always use exit after header redirect to prevent further execution
}
include 'admin_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Pending Course Approval</title>
    <link rel="icon" type="image/x-icon" href="capstoneMiniLogo.ico">
</head>
<body>



<div class="main-content">
<br><br>
<h2>Pending Course Approval</h2>

<table>
    <tr>
        <th>Title</th>    
        <th>Lecturer</th>
        <th>Date</th>
        <th>View</th>
        <th colspan="2">Action</th>
    </tr>

    <?php
    if ($result_pending->num_rows > 0) {
        while ($row = $result_pending->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . $row["submit_date"] . "</td>";
            echo "<td><button type='button'>View</button></td>";
            
// Approve Button
echo "<td>
<button type='button' onclick=\"openApproveModal('" . $row["proposal_id"] . "', '" . $row["lecturer_id"] . "')\">
    Approve
</button>
</td>";

// Reject Button
echo "<td>
<button type='button' onclick=\"openRejectModal('" . $row["proposal_id"] . "')\">
    Reject
</button>
</td>";

echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No pending proposals found.</td></tr>";
    }
    ?>
</table>

<h2>Approval Logs</h2>

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
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . $row["approval_date"] . "</td>";
            echo "<td>" . $row["approval_status"] . "</td>";
            echo "<td><button class='viewProposalBtn' onclick='viewProposal(".$row["proposal_id"].");' type='button'>View</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No matching proposals found.</td></tr>";
    }

    $conn->close();
    ?>
</table>
</div>
<!-- Approve Modal -->
<div id="approveModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to approve this proposal?</p>
    <form method="POST" id="approveForm">
      <input type="hidden" name="approve_id" id="approveId">
      <input type="hidden" name="lecturer_id" id="approveLecturerId">
      <button type="submit" name="approve">Yes, Approve</button>
      <button type="button" onclick="closeModal('approveModal')">Cancel</button>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to reject this proposal?</p>
    <form method="POST" id="rejectForm">
      <input type="hidden" name="reject_id" id="rejectId">
      <button type="submit" name="reject">Yes, Reject</button>
      <button type="button" onclick="closeModal('rejectModal')">Cancel</button>
    </form>
  </div>
</div>

<script>
function openApproveModal(proposalId, lecturerId) {
  document.getElementById('approveId').value = proposalId;
  document.getElementById('approveLecturerId').value = lecturerId;
  document.getElementById('approveModal').style.display = 'flex'; // <-- change to flex
}

function openRejectModal(proposalId) {
  document.getElementById('rejectId').value = proposalId;
  document.getElementById('rejectModal').style.display = 'flex'; // <-- change to flex
}


function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Optional: Close when clicking outside modal
window.onclick = function(event) {
  const approveModal = document.getElementById('approveModal');
  const rejectModal = document.getElementById('rejectModal');
  if (event.target === approveModal) approveModal.style.display = "none";
  if (event.target === rejectModal) rejectModal.style.display = "none";
}

function viewProposal(proposalId) {
    window.location.href = "admin-view-proposal.php?proposal_id="+proposalId+"&originType=admin";
}
</script>

</body>

</html>