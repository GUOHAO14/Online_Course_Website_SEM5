<?php include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$search = $_GET['search'] ?? '';

// Fetch careers
$careerSql = "SELECT career_id, name, description FROM career";
if (!empty($search)) {
    $careerSql .= " WHERE name LIKE '%$search%'";
}
$careerResult = $conn->query($careerSql);

// Fetch fields
$fieldSql = "SELECT field_id, name, description FROM field";
$fieldResult = $conn->query($fieldSql);

// Handle insert/delete requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';

    if ($action === "delete") {
        $idField = ($type === "career") ? "career_id" : "field_id";
        $table = ($type === "career") ? "career" : "field";
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM $table WHERE $idField = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "deleted";
        } else {
            echo "error";
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // Handle insert (career or field)
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $table = ($type === "career") ? "career" : "field";

    $check = $conn->prepare("SELECT COUNT(*) FROM $table WHERE LOWER(name) = LOWER(?)");
    $check->bind_param("s", $name);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        echo ucfirst($type) . " name already exists.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO $table (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        echo ucfirst($type) . " added successfully.";
    } else {
        echo "Error adding $type.";
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
    <title>Admin Career & Field Management</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">

</head>
<body>
<div class="main-content">
    <h2>Career</h2>
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
    </form>

    <table id="careerTable">
        <tr><th>Career</th><th>Description</th><th>Action</th></tr>
        <?php
        $c = 0;
        while ($row = $careerResult->fetch_assoc()) {
            $isHidden = $c >= 10 ? "hidden-row" : "";
            echo "<tr class='career-row $isHidden' data-id='{$row["career_id"]}'>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
            echo "<td><button onclick=\"confirmRemove('career', {$row["career_id"]})\">Remove</button></td>";
            echo "</tr>";
            $c++;
        }
        ?>
        <tr id="careerSeeMoreRow" <?php if ($c <= 10) echo 'style="display:none;"'; ?>>
            <td colspan="3" style="text-align:center;">
                <button onclick="toggleRows('career', true)">See More</button>
            </td>
        </tr>
        <tr id="careerSeeLessRow" style="display:none;">
            <td colspan="3" style="text-align:center;">
                <button onclick="toggleRows('career', false)">See Less</button>
            </td>
        </tr>
        <tr><td colspan="3" style="text-align:center;"><button onclick="openModal('career')">Create</button></td></tr>
    </table>

    <h2>Field</h2>
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
    </form>

    <table id="fieldTable">
        <tr><th>Field</th><th>Description</th><th>Action</th></tr>
        <?php
        $f = 0;
        while ($row = $fieldResult->fetch_assoc()) {
            $isHidden = $f >= 10 ? "hidden-row" : "";
            echo "<tr class='field-row $isHidden' data-id='{$row["field_id"]}'>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
            echo "<td><button onclick=\"confirmRemove('field', {$row["field_id"]})\">Remove</button></td>";
            echo "</tr>";
            $f++;
        }
        ?>
        <tr id="fieldSeeMoreRow" <?php if ($f <= 10) echo 'style="display:none;"'; ?>>
            <td colspan="3" style="text-align:center;">
                <button onclick="toggleRows('field', true)">See More</button>
            </td>
        </tr>
        <tr id="fieldSeeLessRow" style="display:none;">
            <td colspan="3" style="text-align:center;">
                <button onclick="toggleRows('field', false)">See Less</button>
            </td>
        </tr>
        <tr><td colspan="3" style="text-align:center;"><button onclick="openModal('field')">Create</button></td></tr>
    </table>
</div>

<!-- Shared Modals -->
<div id="modal" class="modal">
    <div class="modal-content">
        <p id="modalTitle">Create Entry</p>
        <form id="entryForm" onsubmit="return validateForm()">
            <input type="hidden" name="type" id="entryType">
            <label for="entryName">Name:</label>
            <input type="text" id="entryName" name="name" required autocomplete="off"><br><br>
            <label for="entryDesc">Description:</label>
            <textarea id="entryDesc" name="description" class="fixed-textarea" required></textarea>
            <button type="button" onclick="submitEntry()">Approve</button>
            <button type="button" onclick="closeModal()">Cancel</button>
            <div id="errorMessage" class="error-message"></div>
        </form>
    </div>
</div>

<!-- Remove Confirmation Modal -->
<div id="removeModal" class="modal">
    <div class="modal-content">
        <h2 id="removeTitle">Confirm Removal</h2>
        <p id="removeMessage"></p>
        <input type="hidden" id="removeType">
        <input type="hidden" id="removeId">
        <button onclick="confirmRemoveEntry()">Remove</button>
        <button onclick="closeRemoveModal()">Cancel</button>
    </div>
</div>

<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeMessageModal()">&times;</span>
        <p id="messageText"></p>
    </div>
</div>

<script>
let currentRemoveType = '';
let currentRemoveId = null;

function validateForm() {
    const name = document.getElementById('entryName').value.trim();
    const description = document.getElementById('entryDesc').value.trim();
    const errorMessage = document.getElementById('errorMessage');

    if (!name || !description) {
        errorMessage.textContent = "Both Name and Description are required!";
        return false; // Prevent form submission
    }
    errorMessage.textContent = ""; // Clear any previous error message
    return true; // Allow form submission
}


function confirmRemove(type, id) {
    currentRemoveType = type;
    currentRemoveId = id;
    
    const removeMessage = `Are you sure you want to remove this ${type}?`;
    document.getElementById('removeMessage').innerText = removeMessage;
    document.getElementById('removeType').value = type;
    document.getElementById('removeId').value = id;

    // Show the remove confirmation modal
    document.getElementById('removeModal').style.display = 'flex';
}

function closeRemoveModal() {
    document.getElementById('removeModal').style.display = 'none';
}

function confirmRemoveEntry() {
    const type = currentRemoveType;
    const id = currentRemoveId;

    const data = new URLSearchParams({
        action: 'delete',
        type: type,
        id: id
    });

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data
    }).then(res => res.text()).then(response => {
        if (response.trim() === "deleted") {
            // Close the modal
            closeRemoveModal();

            // Remove the entry from the table
            const row = document.querySelector(`#${type}Table tr[data-id="${id}"]`);
            row.remove();

            // Show success message
            showMessage(type.charAt(0).toUpperCase() + type.slice(1) + " removed successfully.");

            // Update "See More / See Less" buttons
            updateSeeMoreLess(type);
        } else {
            showMessage("Failed to remove " + type + ".");
        }
    });
}

function openModal(type) {
    document.getElementById('entryForm').reset();
    document.getElementById('entryType').value = type;
    document.getElementById('modalTitle').innerText = "Create " + type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
}

function showMessage(msg) {
    document.getElementById('messageText').innerText = msg;
    document.getElementById('messageModal').style.display = 'flex';
}

function submitEntry() {
    // First validate the form
    if (!validateForm()) {
        return; // If validation fails, stop the form submission
    }

    const form = document.getElementById('entryForm');
    const formData = new FormData(form);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(res => res.text()).then(response => {
        showMessage(response);
        if (response.includes("successfully")) {
            setTimeout(() => location.reload(), 1500);
        }
    }).catch(err => showMessage("Error: " + err));
}

function toggleRows(type, show) {
    const rows = document.querySelectorAll(`.${type}-row.hidden-row`);
    rows.forEach(row => row.style.display = show ? "table-row" : "none");
    document.getElementById(type + "SeeMoreRow").style.display = show ? "none" : "table-row";
    document.getElementById(type + "SeeLessRow").style.display = show ? "table-row" : "none";
}

function updateSeeMoreLess(type) {
    const rows = document.querySelectorAll(`.${type}-row`);
    const hiddenRows = Array.from(rows).filter(row => row.style.display === "none" || row.classList.contains("hidden-row"));
    const visibleCount = rows.length - hiddenRows.length;

    if (visibleCount <= 10) {
        document.getElementById(type + "SeeMoreRow").style.display = "none";
        document.getElementById(type + "SeeLessRow").style.display = "none";
    }
}
</script>
</body>
</html>
