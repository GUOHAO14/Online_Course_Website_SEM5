<?php 
include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Handle Unban Action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_email'])) {
    $user_email = $_POST['user_email'];

    $sql = "DELETE FROM removed_user WHERE user_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error deleting user.";
    }
    $stmt->close();
    $conn->close();
    exit(); // Important: stop further HTML rendering
}

// If not POST, continue displaying the page normally
$sql_remove = "SELECT name, user_email, removed_reason FROM removed_user WHERE removed_status = 'Banned'";
$result_remove = $conn->query($sql_remove);

include 'admin_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Banlist</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <style>
        /* Simple modal styling */
        #confirmModal, #successModal {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); align-items: center; justify-content: center;
        }
        .modal-content {
            background: white; padding: 20px; border-radius: 10px; text-align: center;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Banlist</h2>

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Removed Reason</th>    
            <th>Action</th>
        </tr>

        <?php
        if ($result_remove->num_rows > 0) {
            while($row = $result_remove->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["user_email"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["removed_reason"]) . "</td>";
                echo "<td>
                        <button type='button' onclick=\"openApproveModal('" . $row["user_email"] . "')\">Unban</button>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No banned users found.</td></tr>";
        }
        ?>
    </table>
</div>  

<!-- Confirmation Modal -->
<div id="confirmModal">
    <div class="modal-content">
        <p>Are you sure you want to unban this user?</p>
        <button onclick="confirmUnban()">Yes, Unban</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal">
    <div class="modal-content">
        <p>User has been successfully unbanned!</p>
        <button onclick="closeSuccessModal()">OK</button>
    </div>
</div>

<script>
let selectedUserEmail = '';

function openApproveModal(userEmail) {
    selectedUserEmail = userEmail;
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function confirmUnban() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "admin_banlist.php", true); // call itself
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            closeModal();
            document.getElementById('successModal').style.display = 'flex';
        } else {
            alert('Error unbanning user.');
        }
    };
    xhr.send("user_email=" + encodeURIComponent(selectedUserEmail));
}

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    window.location.reload();
}
</script>

</body>
</html>
