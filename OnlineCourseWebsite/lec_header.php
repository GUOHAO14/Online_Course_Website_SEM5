<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" />
    <script src="script.js" defer></script>
</head>
<div class="page-container">
    <div class="top_header">
        <a style="text-decoration: none;" class="brand-container" href="lec_home.php">
            <div class="logo">
                <img src="system_img/Capstone real logo.png" alt="Logo">
            </div>
            <div class="website_name">
                <p>Core of Course</p>
            </div>
        </a>
        
        <button class="mobile-menu-btn">☰</button>

        <div class="homepage_tab">
            <button onclick="window.location.href = 'chat.php'">Chat</button>
            <button onclick="window.location.href = 'forum.php'">Forum</button>
        </div>

        <div class="profile_container">
            <div class="profile_button" id="headerProfileButton">
                <img src="<?php echo $_SESSION["pfp"];?>" alt="Profile">
            </div>
            <div class="dropdown-menu">
                <a href="
                <?php 
                //toggle location to profile based on session to identify user type
                if (isset($_SESSION["student_id"])) echo "stu-profile.php"; 
                else if (isset($_SESSION["lecturer_id"])) echo "lec-profile.php"; 
                else if (isset($_SESSION["admin_id"])) echo "admin_profile.php"; 
                else echo "";
                ?>
                ">
                    <img src="system_img/Capstone_profile_logo_3.png" class="icon-user"></img>
                    My Profile
                </a>
                <a href="user_FAQ.php"><img src="system_img/Capstone small helpcentre logo.png" class="icon-settings"></img>Help Centre</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php"><img src="system_img/Capstone_small_logout_logo.png" class="icon-logout"></img>Log Out</a>
            </div>
        </div>
    </div>
</div>
</html>