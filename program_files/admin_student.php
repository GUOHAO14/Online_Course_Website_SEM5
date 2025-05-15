<?php include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// Build SQL query to fetch only non-pending proposals
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query to fetch students based on the search term for student name
$sql = "SELECT 
            student.edu_level,
            student.learning_style,
            student.recent_course_list,
            student.sign_up_date,
            student.student_id,
            user.user_email,
            user.name AS student_name 
        FROM 
            student
        JOIN 
            user ON student.user_email = user.user_email
        WHERE 1";

// Add the search condition if a search term is provided
if (!empty($search)) {
    $sql .= " AND user.name LIKE '%$search%'";
}

$sql .= " ORDER BY student.sign_up_date DESC";

$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["student_id"]) && $_POST["update_status"] === "Banned") {
    $student_id = $_POST["student_id"];
    $removed_reason = $_POST["removed_reason"] ?? '';

    // Get user_email and name from student_id
    $selectSql = "SELECT user.user_email, user.name 
                  FROM student 
                  JOIN user ON student.user_email = user.user_email 
                  WHERE student.student_id = ?";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->bind_result($user_email, $student_name);
    $stmt->fetch();
    $stmt->close();

    if (!empty($user_email)) {
        // Delete the user (CASCADE will take care of student and others)
        $deleteSql = "DELETE FROM user WHERE user_email = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->close();

        // Log the removal
        $insertSql = "INSERT INTO removed_user (user_email, name, removed_status, removed_reason) 
                      VALUES (?, ?, 'Banned', ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sss", $user_email, $student_name, $removed_reason);
        $stmt->execute();
        $stmt->close();

        echo "Student has been banned successfully.";
    } else {
        echo "Student not found.";
    }

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
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <title>Admin Student Management</title>
</head>



<body>
<div class="main-content">
<h2>Students</h2>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Search by name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
</form>

<table>
    <tr>
        <th>Student Name</th>
        <th>Student Email</th> 
        <th>Sign Up Date</th>    
        <th>Education Level</th>
        <th>Learning Style</th>
        <th>View</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["student_name"]) . "</td>";    
            echo "<td>" . htmlspecialchars($row["user_email"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["sign_up_date"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["edu_level"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["learning_style"]) . "</td>";
            echo "<td><button class='viewStuBtn' onclick='viewStuProfile(\"".$row["user_email"]."\")' type='button'>View</button></td>";
            echo "<td>
            <button type='button' onclick=\"openRemoveModal('" . $row["student_id"] . "')\">Ban</button>
        </td>";
        
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No matching proposals found.</td></tr>";
    }

    $conn->close();
    ?>
</table>

<div id="removeModal" class="modal">
  <div class="modal-content" id="removeModalContent">
    <p>Are you sure you want to ban this student?</p>
    <input type="hidden" id="removeCourseId">
    <label for="removalReason">Reason for ban:</label>
    <textarea id="removalReason" rows="3" style="width: 100%;" class="fixed-textarea"></textarea>
    <button onclick="submitRemoval()">Yes, Ban</button>
    <button type="button" onclick="closeModal('removeModal')">Cancel</button>
  </div>
</div>

</body>

<script>
    function openRemoveModal(studentId) {
        document.getElementById('removeCourseId').value = studentId;
        document.getElementById('removalReason').value = '';
        document.getElementById('removeModal').style.display = 'flex';
    }

    function submitRemoval() {
        const studentId = document.getElementById("removeCourseId").value;
        const removalReason = document.getElementById("removalReason").value.trim();

        if (!removalReason) {
            alert("Please enter a reason for the ban.");
            return;
        }

        fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `student_id=${encodeURIComponent(studentId)}&update_status=Banned&removed_reason=${encodeURIComponent(removalReason)}`
        })
        .then(response => response.text())
        .then(data => {
            const modalContent = document.getElementById("removeModalContent");
            modalContent.innerHTML = `
                <h3>Banned</h3>
                <p>${data}</p>
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

    function closeModal() {
        document.getElementById('removeModal').style.display = 'none'; // Hide the modal
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('removeModal')) {
            closeModal(); // Close the modal if the background is clicked
        }
    }

    function viewStuProfile(email) {
        window.location.href = "admin-view-stu-profile.php?user_email="+email+"&type=admin";
    }
</script>

</html>