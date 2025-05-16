<?php
include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$faqSearch = $_GET['faq_search'] ?? '';

$faqSql = "SELECT question, answer, support_id FROM help_support_qna";
if (!empty($faqSearch)) {
    $faqSql .= " WHERE question LIKE '%$faqSearch%'";
}
$faqResult = $conn->query($faqSql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST['action'] === 'add_faq') {
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);

        if (!empty($question) && !empty($answer)) {
            $stmt = $conn->prepare("INSERT INTO help_support_qna (question, answer) VALUES (?, ?)");
            $stmt->bind_param("ss", $question, $answer);
            $success = $stmt->execute();
            $stmt->close();
            echo $success ? "success" : "error";
        } else {
            echo "error";
        }
        exit;
    }

    // Handle FAQ removal
    if ($_POST['action'] === 'remove_faq') {
        $supportId = $_POST['support_id'];
        if (!empty($supportId)) {
            $stmt = $conn->prepare("DELETE FROM help_support_qna WHERE support_id = ?");
            $stmt->bind_param("i", $supportId);
            $success = $stmt->execute();
            $stmt->close();
            echo $success ? "success" : "error";
        } else {
            echo "error";
        }
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
    <title>Admin FAQ Management</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
</head>
<body>
<div class="main-content">
<h2 id="career">FAQ Management</h2>

<form method="GET" >
    <input type="text" name="faq_search" placeholder="Search by question..." value="<?php echo htmlspecialchars($faqSearch); ?>">
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
</form>
<br>
<button onclick="openModal()">Create</button>
<br>
    <table id="faqTable">
    <tr><th>Question</th><th>Answer</th><th>Action</th></tr>
        <?php
        while ($row = $faqResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["question"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["answer"]) . "</td>";
            echo "<td><button type='button' onclick=\"removeFaq({$row['support_id']})\">Remove</button></td>";
            echo "</tr>";
        }
        ?>
    </table>

<!-- Create FAQ Modal -->
<div id="faqModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px; position:relative;">
        <span onclick="closeFaqModal()" style="position:absolute; top:10px; right:15px; cursor:pointer;">&times;</span>
        <h3>Create FAQ</h3>
        <form id="faqForm" onsubmit="return false;">
            <label>Question:</label><br>
            <textarea id="faqQuestion" name="question" required style="width:100%; height:auto; overflow:hidden; resize:none; line-height:1.2em; padding:0.4em 0.5em;" rows="1" oninput="autoResize(this)"></textarea>

            <label>Answer:</label><br>
            <textarea id="faqAnswer" name="answer" required style="width:100%; height:100px;"></textarea><br><br>
            <button type="button" onclick="submitFaq()">Confirm</button>
            <button type="button" onclick="closeFaqModal()">Cancel</button>
            <div id="faqError" style="color:red; margin-top:10px;"></div>
        </form>
    </div>
</div>

<!-- Remove FAQ Confirmation Modal -->
<div id="removeModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px; position:relative;">
        <span onclick="closeRemoveModal()" style="position:absolute; top:10px; right:15px; cursor:pointer;">&times;</span>
        <h3>Are you sure you want to remove this FAQ?</h3>
        <button type="button" id="confirmRemoveBtn">Yes, Remove</button>
        <button type="button" onclick="closeRemoveModal()">Cancel</button>
    </div>
</div>

<!-- Success/Error Message Modal -->
<div id="messageModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:400px; position:relative;">
        <span onclick="closeMessageModal()" style="position:absolute; top:10px; right:15px; cursor:pointer;">&times;</span>
        <h3 id="messageText"></h3>
        <button type="button" onclick="closeMessageModal()">OK</button>
    </div>
</div>

</body>

<script>
function openModal() {
    document.getElementById('faqForm').reset();
    document.getElementById('faqModal').style.display = 'flex';
    document.getElementById('faqError').innerText = '';
}

function closeFaqModal() {
    document.getElementById('faqModal').style.display = 'none';
}

function submitFaq() {
    const question = document.getElementById('faqQuestion').value.trim();
    const answer = document.getElementById('faqAnswer').value.trim();

    if (!question || !answer) {
        showMessage("Both fields are required!");
        return;
    }

    const formData = new URLSearchParams();
    formData.append('action', 'add_faq');
    formData.append('question', question);
    formData.append('answer', answer);

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === "success") {
            showMessage("FAQ added successfully.");
        } else {
            showMessage("Error adding FAQ.");
        }
    })
    .catch(() => {
        showMessage("An error occurred while adding the FAQ.");
    });
}

function autoResize(textarea) {
    textarea.style.height = 'auto';  // reset to auto first
    textarea.style.height = textarea.scrollHeight + 'px';  // then set to scroll height
}

function showMessage(msg) {
    document.getElementById('messageText').innerText = msg;
    document.getElementById('messageModal').style.display = 'flex'; // Show the message modal
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';  // Close the message modal only when OK is clicked
    location.reload();  // Reload the page after closing the modal
}

// Open Remove Confirmation Modal
function removeFaq(supportId) {
    // Store the supportId for later use
    document.getElementById('confirmRemoveBtn').onclick = function() {
        deleteFaq(supportId);
    };
    document.getElementById('removeModal').style.display = 'flex';  // Show the modal
}

// Close the Remove Modal
function closeRemoveModal() {
    document.getElementById('removeModal').style.display = 'none'; // Hide the modal
}

// Delete the FAQ from the database after confirmation
function deleteFaq(supportId) {
    const formData = new URLSearchParams();
    formData.append('action', 'remove_faq');
    formData.append('support_id', supportId);

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === "success") {
            showMessage("FAQ removed successfully.");
        } else {
            showMessage("Error removing FAQ.");
        }
        closeRemoveModal();  // Close the modal after the action
    })
    .catch(() => {
        showMessage("An error occurred while removing the FAQ.");
    });
}

</script>

</html>
