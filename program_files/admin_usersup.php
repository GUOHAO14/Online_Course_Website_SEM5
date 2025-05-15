<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat</title>
    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, sans-serif;
        }

        .purpleBtn {
            width: fit-content;
            padding: 0.7rem 1.4rem;
            border: none;
            border-radius: 0.4rem;
            background-color: rgb(84, 0, 200);
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease; /* animate everything */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        /* Hover effect */
        .purpleBtn:hover {
            background-color: rgb(65, 31, 111);
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .purpleBtn:active {
            background-color: rgb(84, 0, 200);
            transform: scale(1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #adminChat > p {

        }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem; height: fit-content;"  class="purpleBtn" onclick="window.location.href='admin_proposal.php';">Back</div>
    <?php include "chat-for-admin.php";?>
</body>
</html>