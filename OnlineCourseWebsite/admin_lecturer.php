<?php include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// Application table
$sql_applicant = "SELECT 
                lecturer_applicant.job_title,
                lecturer_applicant.teaching_exp,
                lecturer_applicant.applicant_id,
                lecturer_applicant.application_date,
                lecturer_applicant.current_uni_name,
                user.user_email,
                user.name AS lecturer_name
                FROM 
                lecturer_applicant
                JOIN 
                user ON lecturer_applicant.user_email = user.user_email
                WHERE application_status = 'Pending'
                ORDER BY application_date DESC";
                
$result_applicant = $conn->query($sql_applicant);


$search = isset($_GET['search']) ? $_GET['search'] : '';
$search = $conn->real_escape_string($search); 


// Lecturer table
$sql = "SELECT 
            lecturer_applicant.job_title,
            lecturer_applicant.teaching_exp,
            lecturer_applicant.applicant_id,
            lecturer.approval_date,
            lecturer_applicant.current_uni_name,
            user.user_email,
            user.name AS lecturer_name
        FROM 
            lecturer_applicant
        JOIN 
            user ON lecturer_applicant.user_email = user.user_email
        JOIN 
            lecturer ON lecturer_applicant.applicant_id = lecturer.applicant_id
        WHERE 
            lecturer_applicant.application_status = 'Approved'";

if (!empty($search)) {
    $sql .= " AND user.name LIKE '%$search%'";
}

$sql .= " ORDER BY lecturer.approval_date DESC";

$result = $conn->query($sql);


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["course_id"])) {
    $applicant_id = $_POST["course_id"];
    $update_status = $_POST["update_status"] ?? '';
    $custom_reason = $_POST["reason"] ?? '';

    // Fetch user_email and name together
    $selectSql = "
        SELECT lecturer_applicant.user_email, user.name
        FROM lecturer_applicant
        JOIN user ON lecturer_applicant.user_email = user.user_email
        WHERE lecturer_applicant.applicant_id = ?
    ";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param("s", $applicant_id);
    $stmt->execute();
    $stmt->bind_result($user_email, $lecturer_name);
    $stmt->fetch();
    $stmt->close();

    if ($update_status === "Approved") {
        // 1. Update application status
        $updateSql = "UPDATE lecturer_applicant SET application_status = 'Approved' WHERE applicant_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("s", $applicant_id);
        $stmt->execute();
        $stmt->close();

        // 2. Insert into lecturer
        $insertSql = "INSERT INTO lecturer (description, applicant_id, user_email, approval_date) VALUES (NULL, ?, ?, NOW())";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ss", $applicant_id, $user_email);
        $stmt->execute();
        $stmt->close();

        // 3. Update user role
        $roleUpdateSql = "UPDATE user SET role = 'lecturer' WHERE user_email = ?";
        $stmt = $conn->prepare($roleUpdateSql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->close();

        echo "Application approved, user role updated, and lecturer added.";
        exit;

    } elseif ($update_status === "Removed") {
        // 1. Delete the user account
        $deleteUserSql = "DELETE FROM user WHERE user_email = ?";
        $stmt = $conn->prepare($deleteUserSql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->close();

        // 2. Log the removal
        $insertRemovedSql = "INSERT INTO removed_user (user_email, removed_status, removed_reason, name) VALUES (?, 'Removed', ?, ?)";
        $stmt = $conn->prepare($insertRemovedSql);
        $stmt->bind_param("sss", $user_email, $custom_reason, $lecturer_name);
        $stmt->execute();
        $stmt->close();

        echo "User has been removed as a lecturer.";
        exit;
    }
}

include 'admin_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Lecturer Management</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
</head>


<body>
<div class="main-content">
<br><br>
<h2>Lecturer Application</h2>

<table>
    <tr>
        <th>Lecturer Name</th>
        <th>Lecturer Email</th>
        <th>Date</th>    
        <th>Position</th>
        <th>University Name</th>
        <th>Teaching Years</th>
        <th>Document</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result_applicant->num_rows > 0) {
        while($row = $result_applicant->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["user_email"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["application_date"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["job_title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["current_uni_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["teaching_exp"]) . "</td>";
            echo "<td><button class='viewLecBtn' type='button' onclick=\"openViewModal('" . $row["applicant_id"] . "')\">View</button></td>";
            echo "<td>
                    <button style='margin-bottom: 0.5rem;' type='button' onclick=\"openApproveModal('" . $row["applicant_id"] . "')\">Approve</button>
                    <button style='margin-top: 0.5rem;' type='button' onclick=\"openRemoveModal('" . $row["applicant_id"] . "')\">Reject</button>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No pending proposals found.</td></tr>";
    }
    ?>
</table>


<h2>Lecturer</h2>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Search by name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
</form>

<table>
    <tr>
        <th>Lecturer Name</th>
        <th>Lecturer Email</th>
        <th>Date</th>    
        <th>Position</th>
        <th>University Name</th>
        <th>Teaching Years</th>
        <th>Document</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["lecturer_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["user_email"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["approval_date"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["job_title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["current_uni_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["teaching_exp"]) . "</td>";
            echo "<td><button class='viewLecBtn' type='button' onclick=\"openViewModal('" . $row["applicant_id"] . "')\">View</button></td>";
            echo "<td>
            <button type='button' onclick=\"openRemoveModal('" . $row["applicant_id"] . "')\">Remove</button>
        </td>";
        
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No matching proposals found.</td></tr>";
    }

    $conn->close();
    ?>
</table>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
  <div class="modal-content" id="approveModalContent">
    <p>Are you sure you want to approve this course removal?</p>
    <input type="hidden" id="approveCourseId">
    <button onclick="submitApproval()">Yes, Approve</button>
    <button type="button" onclick="closeModal('approveModal')">Cancel</button>
  </div>
</div>



<div id="removeModal" class="modal">
  <div class="modal-content" id="removeModalContent">
    <p>Please provide a reason for removal:</p>
    <textarea id="removeReason" rows="4" cols="50" placeholder="Enter removal reason..."class="fixed-textarea"></textarea>
    <input type="hidden" id="removeCourseId">
    <button onclick="submitRemoval()">Submit Removal</button>
    <button type="button" onclick="closeModal('removeModal')">Cancel</button>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
  <div class="modal-content" id="viewModalContent">
    <h3>Application Documents</h3>
    <div id="pdfList"></div> <!-- This will display the PDFs -->
    <button onclick="closeModal('viewModal')">Close</button>
  </div>
</div>


</body>

<script>
let selectedCourseId = null;

function openApproveModal(courseId) {
  document.getElementById('approveCourseId').value = courseId;
  document.getElementById('approveModal').style.display = 'flex';
}

function openRemoveModal(courseId) {
  document.getElementById('removeCourseId').value = courseId;
  document.getElementById('removeModal').style.display = 'flex';
}

function submitApproval() {
    const courseId = document.getElementById("approveCourseId").value;

    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `course_id=${encodeURIComponent(courseId)}&update_status=Approved`
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

function submitRemoval() {
    const courseId = document.getElementById("removeCourseId").value;
    const reason = document.getElementById("removeReason").value;

    if (!reason.trim()) {
        alert("Please provide a reason for removal.");
        return;
    }

    fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `course_id=${encodeURIComponent(courseId)}&update_status=Removed&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.text())
    .then(data => {
        const modalContent = document.getElementById("removeModalContent");
        modalContent.innerHTML = `
            <h3>Removed</h3>
            <p>${data}</p>
            <button onclick="confirmAndReload()">OK</button>
        `;
    });
}

function confirmAndReload() {
    location.reload();
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Optional: close modal when clicking outside
window.onclick = function(event) {
  const approveModal = document.getElementById('approveModal');
  const removeModal = document.getElementById('removeModal');
  const viewModal = document.getElementById('viewModal');
  if (event.target === approveModal) approveModal.style.display = "none";
  if (event.target === removeModal) removeModal.style.display = "none";
  if (event.target === viewModal) viewModal.style.display = "none";
}

function openViewModal(applicantId) {
    fetch(`get_applicant_pdfs.php?applicant_id=${encodeURIComponent(applicantId)}`)
        .then(response => response.json())
        .then(pdfs => {
            const pdfList = document.getElementById("pdfList");
            pdfList.innerHTML = ""; // Clear previous content

            if (pdfs.error) {
                console.error("Error from PHP:", pdfs.error);
                alert("Error: No documents can be found for this lecturer.");
                pdfList.innerHTML = "<p>Error: " + pdfs.error + "</p>";
                return;
            }

            if (pdfs.length === 0) {
                pdfList.innerHTML = "<p>No documents found.</p>";
            } else {
                pdfs.forEach(pdf => {
                    const fileName = pdf.name;
                    const filePath = `${pdf.directory}`;

                    const fileItem = document.createElement("div");
                    fileItem.innerHTML = `
                        <p>${fileName}</p>
                        <a href="${filePath}" target="_blank" class="download-btn">View / Download</a>
                        <hr>
                    `;
                    pdfList.appendChild(fileItem);
                });
            }

            document.getElementById("viewModal").style.display = "flex";
        })
        .catch(error => {
            alert("Failed to load documents.");
            console.error("Error loading documents:", error);
        });
}
</script>


</html>