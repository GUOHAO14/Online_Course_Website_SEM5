<?php
include "conn.php";

if (isset($_REQUEST["user_email"])) {
    $userEmail = $_GET["user_email"];
}

$idSQL = "SELECT lecturer_id FROM lecturer WHERE user_email = '$userEmail';";
$idExe = mysqli_query($conn, $idSQL);
$lecturerId = mysqli_fetch_assoc($idExe)["lecturer_id"];

//obtain user's personal info from user table
$userSQL = 
"SELECT * FROM user WHERE user_email = '$userEmail'";
$userExe = mysqli_query($conn, $userSQL);
while ($row = mysqli_fetch_assoc($userExe)) {
    $infoRow = $row;
}

$lecSQL = 
"SELECT description FROM lecturer WHERE user_email = '$userEmail'";
$lecExe = mysqli_query($conn, $lecSQL);
$lecRow = mysqli_fetch_assoc($lecExe);

$lecAppSQL = 
"SELECT teaching_exp, current_uni_name, job_title FROM lecturer_applicant WHERE user_email = '$userEmail'";
$lecAppExe = mysqli_query($conn, $lecAppSQL);

while ($row = mysqli_fetch_assoc($lecAppExe)) {
    $lecAppRow = $row;
}

include 'admin_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="stu-home.css">
    <link rel="stylesheet" href="stu-profile.css">
    <link rel="stylesheet" href="stu-shared.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
    <style>
        body {
            padding-top: 0;
        }
        .contentSection {
            padding: 3rem 0;
            width: 100%;
        }

        #mainContainer {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: max-content;
            column-gap: 0;
            row-gap: 1.5rem;
        }

        #mainContainer > div {
            border-radius: 0;
            padding: 4% 10%;
        }

        .personalLecturerInfo {
            display: grid;
            grid-template-rows: repeat(9, max-content);
            grid-template-columns: 2fr 5fr;
            row-gap: 1rem;
            column-gap: 1rem;
        }

        .personalLecturerInfo > .editContainer, #profileHeader, #desc {
            grid-column: 1 / 3;
        }

        .personalLecturerInfo > .profileContainer {
            grid-column: 1 / 2;
            grid-row: 3 / 7;
        }

        #social {
            display: flex;
            justify-content: space-between;
            align-items: center;
            grid-column: 1 / 3;
            grid-row: 7 / 8;
            margin: auto;
        }

        #desc {
            margin-top: 1rem;
        }

        #chat {
            margin: 0;
        }

        #lecDoc {
            margin-top: auto;
            margin-bottom: auto;
            margin-left: 0;
            grid-column: 1 / 2;
            grid-row: 8 / 9;
        }

        #karma {
            width: fit-content;
            height: fit-content;
        }

        .editBtn {
            width: 2rem;
            height: 2rem;
        }

        .contentSection {
            padding: 0;
        }

        @media screen and (max-width: 630px) {
            #mainContainer > div {
                border-radius: 0;
                padding: 4% 6%;
            }
            .personalLecturerInfo {
                display: grid;
                grid-template-rows: repeat(11, max-content);
                grid-template-columns: 3fr 4fr;
                row-gap: 1rem;
                column-gap: 1rem;
            }

            .personalLecturerInfo > div:not(#social):not(#lecDoc) {
                grid-column: 1 / 3;
            }

            .personalLecturerInfo > .profileContainer {
                grid-column: 1 / 2;
                grid-row: 3 / 4;
            }

            #social {
                justify-content: none;
                grid-column: 2 / 3;
                grid-row: 4 / 5;
            }

            #lecDoc {
                margin-left: unset;
                margin-right: 0;
                grid-column: 1 / 2;
                grid-row: 4 / 5;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="contentSection">
            <button style="margin-bottom: 1.5rem;" class="purpleBtn" id="backButton" onclick="window.history.back();">Back</button>
            <div id="mainContainer">
                <div class="personalLecturerInfo">
                    <div id="profileHeader">
                        <h1>Personal Details</h1>
                    </div>
                    <div class="profileContainer">
                        <img class="profile" src=<?php echo $infoRow['pfp'];?> alt="Profile Picture">
                    </div>
                    <div id="social">
                        <div onclick="chat();" id="chat">
                            <p>Chat</p>
                            <img id="chatImg" src="system_img/chat.png" alt="" title="Message">
                        </div>
                    <?php
                    $dob = new DateTime($infoRow['DOB']);
                    $dobFormatted = $dob->format('F j, Y');
                    $personalInfoTemplate = 
                    '</div>
                    <div id="name">'.$infoRow['name'].'</div>
                    <div id="email">'.$infoRow['user_email'].'</div>
                    <div id="dob">DOB: '.$dobFormatted.'</div>
                    <div id="job">'.$lecAppRow['job_title'].'</div>
                    <div id="uni">'.ucfirst($lecAppRow['current_uni_name']).'</div>
                    <div id="exp">Teaching Experience: '.$lecAppRow['teaching_exp'].'</div>
                    <div id="desc">'.$lecRow['description'].'</div>';

                    echo $personalInfoTemplate;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chat() {
            //go ivan's chat page
        }

        function ucfirst(str) {
            if (!str) return str; // return the string if it's empty
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        document.querySelectorAll('.courseMain').forEach(main => {
            main.addEventListener('mouseenter', () => {
                main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
            });
            main.addEventListener('mouseleave', () => {
                main.closest('.courseCard').style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>