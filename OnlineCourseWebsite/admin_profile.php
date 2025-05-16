    <?php
    include 'conn.php';

    // Get admin ID (from AJAX request or fallback to 1)
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header("Location: index.php");
        exit();
    }

    $admin_id = $_SESSION['admin_id'];


    $error = '';
    $success = '';
    $unlocked = false;

    // Allowed domains
    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];

    // Function to validate email format and domain
    function validateEmail($email, $allowed_domains) {
        // Check if the email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        // Extract the domain from the email
        $domain = substr(strrchr($email, "@"), 1);

        // Check if the domain is in the allowed list
        if (!in_array($domain, $allowed_domains)) {
            return "Email domain is not allowed. Only the following domains are allowed: " . implode(", ", $allowed_domains);
        }

        return true; // Valid email
    }

    // AJAX password check (for plain text comparison)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_password_only']) && !isset($_POST['update'])) {
        $input_pass = $_POST['password'];

        // Get admin ID dynamically from POST request
        $admin_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 1;

        // Prepare the SQL to fetch the hashed password for the admin
        $stmt = $conn->prepare("
            SELECT u.password 
            FROM admin a 
            JOIN user u ON a.user_email = u.user_email 
            WHERE a.admin_id = ?
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_pw = $result->fetch_assoc();

        // Check if the admin password exists and verify using password_verify
        if ($admin_pw) {
            if (password_verify($input_pass, $admin_pw['password'])) {
                echo "success";
            } else {
                echo "fail";
                error_log("Password mismatch for admin_id $admin_id");
            }
        } else {
            echo "fail";
            error_log("No admin found for ID $admin_id");
        }

        exit;
    }

    // Get admin data
    $sql = "SELECT a.admin_id, a.user_email, a.description, 
                u.name, u.password AS user_password, u.pfp, u.DOB
            FROM admin a
            JOIN user u ON a.user_email = u.user_email
            WHERE a.admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        echo "Admin not found."; exit();
    }

    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $description = $_POST['description'];
        $new_email = $_POST['user_email'];
        $password = $_POST['password'];

        // Validate the new email
        $email_validation_result = validateEmail($new_email, $allowed_domains);
        if ($email_validation_result !== true) {
            $error = $email_validation_result;
            $unlocked = true;
        } else {
                $check_user = $conn->prepare("SELECT 1 FROM user WHERE user_email = ? AND user_email != ?");
                $check_user->bind_param("ss", $new_email, $admin['user_email']);
                $check_user->execute();
                $user_exists = $check_user->get_result()->num_rows > 0;

                $check_removed = $conn->prepare("SELECT 1 FROM removed_user WHERE user_email = ?");
                $check_removed->bind_param("s", $new_email);
                $check_removed->execute();
                $removed_exists = $check_removed->get_result()->num_rows > 0;

                if ($user_exists || $removed_exists) {
                    $error = "Email is already in use by another account.";
                    $unlocked = true;
            } else {
                // Update user data first
                if (!empty($password)) {
                    $update_user = $conn->prepare("UPDATE user SET name=?, DOB=?, password=?, user_email=? WHERE user_email=?");
                    $update_user->bind_param("sssss", $name, $dob, $password, $new_email, $admin['user_email']);
                } else {
                    $update_user = $conn->prepare("UPDATE user SET name=?, DOB=?, user_email=? WHERE user_email=?");
                    $update_user->bind_param("ssss", $name, $dob, $new_email, $admin['user_email']);
                }
                $update_user->execute();

                // Update admin table after updating user table
                $update_admin = $conn->prepare("UPDATE admin SET description=?, user_email=? WHERE admin_id=?");
                $update_admin->bind_param("ssi", $description, $new_email, $admin_id);
                $update_admin->execute();

                $success = "Profile updated successfully!";
                header("Refresh:1");
                exit();
            }
        }
    }

    include 'admin_sidebar.php';
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Profile</title>
        <link rel="icon" type="image/x-icon" href="system_img/capstoneMiniLogo.ico">
        <style>
            .profile-container {
                text-align: center;
                max-width: 600px;
                margin: auto;
                padding: 20px;
                font-family: Arial, sans-serif;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #f5f5f5;
            }
            img.pfp {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
            }
            input, textarea {
                width: 100%;
                padding: 8px;
                margin: 6px 0;
                box-sizing: border-box;
            }
            button {
                padding: 10px 15px;
                margin-top: 10px;
            }
            .error { color: red; }
            .success { color: green; }

            .modal {
                display: none;
                position: fixed;
                z-index: 999;
                left: 0; top: 0;
                width: 100%; height: 100%;
                background: rgba(0,0,0,0.5);
            }
            .modal-content {
                background: #fff;
                padding: 20px;
                width: 300px;
                margin: 100px auto;
                border-radius: 10px;
            }

            .joshBtn {
                border-radius: 1rem;
                cursor: pointer;
                margin: 2rem;
                box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
            }

            .joshBtn:hover {
                background-color: grey;
                color: white;
                transform: scale(1.1);
                transition: all 0.3s ease;
            }

            * {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="main-content">
            

            <div class="profile-container">
                <h2>Admin Profile</h2>

                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php elseif ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>

                <img class="pfp" src="<?php echo htmlspecialchars($admin['pfp']); ?>" alt="Profile Picture"><br><br>

                <div id="readonly-view">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['user_email']); ?></p>
                    <p><strong>Password: *****</strong></p>
                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($admin['DOB']); ?></p>
                    <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($admin['description'])); ?></p>

                    <button class="joshBtn" onclick="openModal()">Edit Profile</button>
                    <button class="joshBtn" name="logout" onclick="window.location.href='logout.php'">Logout</button>
                </div>

                <div id="edit-form" style="display:none;">
                    <form method="post">
                        <label>Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>

                        <label>Email:</label>
                        <input type="text" name="user_email" value="<?php echo htmlspecialchars($admin['user_email']); ?>" required>

                        <label>New Password:</label>
                        <input type="password" name="password" placeholder="•••••••• (leave blank to keep current)">

                        <label>Date of Birth:</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($admin['DOB']); ?>" required>

                        <label>Description:</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($admin['description']); ?></textarea>

                        <button class="joshBtn" type="submit" name="update">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal" id="passwordModal">
                <div class="modal-content">
                    <h4>Enter your password</h4>
                    <input type="password" id="confirmPassword" />
                    <button class="joshBtn" style="margin: 0; font-size: 80%;" type="button" id="togglePassword" onclick="togglePasswordVisibility()">Show</button>
                    <p id="modalError" class="error"></p>
                    <button class="joshBtn" onclick="checkPassword()">Confirm</button>
                    <button class="joshBtn" onclick="closeModal()">Cancel</button>
                </div>
            </div>
        </div>
    <script>
    function openModal() {
        document.getElementById("passwordModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("passwordModal").style.display = "none";
        document.getElementById("confirmPassword").value = '';
        document.getElementById("modalError").innerText = '';
    }

    function checkPassword() {
        const password = document.getElementById("confirmPassword").value;
        const adminId = <?php echo $admin_id; ?>; // Dynamically pass the admin ID

        fetch("admin_profile.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "check_password_only=1&password=" + encodeURIComponent(password) + "&admin_id=" + adminId
        })
        .then(res => res.text())
        .then(response => {
            if (response.trim() === "success") {
                document.getElementById("readonly-view").style.display = "none";
                document.getElementById("edit-form").style.display = "block";
                closeModal();
            } else {
                document.getElementById("modalError").innerText = "Incorrect password.";
            }
        });
    }

    function togglePasswordVisibility() {
        const passwordField = document.getElementById("confirmPassword");
        const toggleButton = document.getElementById("togglePassword");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleButton.textContent = "Hide";
        } else {
            passwordField.type = "password";
            toggleButton.textContent = "Show";
        }
    }

    function setDOBMaxDate() {
            var today = new Date();
            var year = today.getFullYear() - 18; // Get the year 18 years ago
            var month = today.getMonth() + 1; // Get the current month (1-based index)
            var day = today.getDate(); // Get today's day

            // Format the date to YYYY-MM-DD
            var maxDate = year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;

            // Set the max attribute of the DOB input field
            document.getElementById("dob").setAttribute("max", maxDate);
        }

        // Call the function when the page loads
        window.onload = function() {
            setDOBMaxDate();
        }
    </script>

    </body>
    </html>
