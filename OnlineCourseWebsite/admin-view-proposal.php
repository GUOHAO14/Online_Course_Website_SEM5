<?php
include "conn.php";
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['admin_id'])) {
    $proposalId = $_GET['proposal_id'];
    $fromAdmin = $_GET['originType'];
} else {
    header("Location: admin_proposal.php");
    exit();
}

$detailSQL = 
"SELECT submit_date, ul.pfp as lecturer_icon, ul.name as lecturer_name, user_email as lecturer_user_email, cp.*
FROM course_proposal as cp 
JOIN (SELECT pfp, lecturer_id, name, l.user_email FROM user as u RIGHT JOIN lecturer as l on u.user_email = l.user_email) as ul
on cp.lecturer_id = ul.lecturer_id
WHERE cp.proposal_id = $proposalId;"; //all course details
$detailExe = mysqli_query($conn, $detailSQL);
$detailRow = mysqli_fetch_assoc($detailExe);

$fieldSQL = "SELECT name as field_name FROM field as f RIGHT JOIN course_field as cf on f.field_id = cf.field_id WHERE cf.proposal_id = $proposalId;"; //obtain course's fields
$fieldExe = mysqli_query($conn, $fieldSQL);

$conn->close();
include "admin_sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $detailRow["title"];?></title>
    <link rel="icon" type="image/x-icon" href="system_img/Capstone real logo.png">
    <link rel="stylesheet" href="lec_course.css">
    <link rel="stylesheet" href="lec_course2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .courseImgContainer {
            grid-row: 1 / 5;
        }

        #courseDetails {
            grid-template-columns: repeat(3, 1fr);
        }

        .topic:hover {
            margin: 1.5rem 1.5rem 0 0;
        }

        body {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="whiteSectionBg">
            <div class="contentSection">
                <button class="purpleBtn" id="backButton" onclick="window.location.href = 'admin_proposal.php';">
                Back</button>
                
                <div id="courseHeader">
                    <?php
                    if ($detailRow["lecturer_icon"] === null) {
                        $icon = "profile/defaultProfile.jpg";
                    } else {
                        $icon = $detailRow["lecturer_icon"];
                    }

                    $date = $detailRow["submit_date"];

                    $dateFormatted = date('F j, Y', strtotime($date));

                    $headerTemplate = '';

                    $headerTemplate .= 
                    '<div id="courseTitle">

                        <h1>'.$detailRow["title"].'</h1>
                    </div>
                    <div class="courseImgContainer" id="coursePicture">
                        <img class="courseImg" src="'.$detailRow["cover_img_url"].'" alt="">
                    </div>
                    <div id="courseDescriptionContainer">
                        <span id="courseDescription">'.$detailRow["description"].'</span>
                        <a href="#description" id="seeMoreButton">... See More</a>
                    </div>
                    
                    <div id="courseLecturer">
                        <span>Created by</span>
                        <a class="profileLink" href="admin-view-lec-profile.php?user_email='.$detailRow["lecturer_user_email"].'">
                            <div class="lecturerIconContainer">
                                <img class="lecturerIcon" src="'.$icon.'" alt="">
                            </div>
                            <span id="lecturerName">'.$detailRow["lecturer_name"].'</span>
                        </a>
                        
                    </div> 
                    <div id="courseDate">
                        <span>Proposal submitted on </span>'.$dateFormatted.'
                    </div>';

                    echo $headerTemplate; //print template
                    ?> 
                </div>
                <div id="courseDetails">
                    <?php
                    $detailsTemplate = 
                    '<div>
                        <div class="detailHeader">
                            <b>Difficulty</b>
                        </div>
                        <div>'.ucfirst($detailRow["difficulty"]).'</div>
                    </div>
                    <div>
                        <div class="detailHeader">
                            <b>Course Style</b>
                        </div>
                        <div>'.$detailRow["course_style"].' Learning</div>
                    </div>
                    <div>
                        <div class="detailHeader">
                            <b>Completion Time</b>
                        </div>
                        <div>'.$detailRow["completion_time"].'</div>
                    </div>';

                    echo $detailsTemplate; //print template
                    ?>
                </div>
                <div id="courseFields">
                    <h3>Topics Covered:</h3>
                    <div id="topicList">
                        <?php
                        while ($row = mysqli_fetch_assoc($fieldExe)) {
                            $topicTemplate = 
                            '<div class="topic">'.$row["field_name"].'</div>';
                            echo $topicTemplate;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="whiteSectionBg">
            <div class="contentSection">
                <div id="courseInfo">
                    <div id="infoSidebar">
                        <a class="tablinks" href="#courseHeader">Overview</a>
                        <a class="tablinks" href="#objective">Objective</a>
                        <a class="tablinks" href="#scope">Scope</a>
                        <a class="tablinks" href="#description">Description</a>
                    </div>
                    <div id="infoDisplay">
                        <?php
                        $overviewTemplate = 
                        '<div id="objective" class="tabContent">
                            <h2>Objective of This Course</h2><br>
                            '.$detailRow["objective"].'
                        </div>
                        <div id="scope" class="tabContent">
                            <h2>What Will You Learn?</h2><br>
                            '.$detailRow["scope"].'
                        </div>
                        <div id="description" class="tabContent">
                            <h2>Description</h2><br>'.$detailRow["description"].'
                        </div>';
                        echo $overviewTemplate;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showDescription() {
            let description = document.getElementById("courseDescription");
            let descriptionContent = description.textContent;
            if ((descriptionContent.length > 270)) {
                shortText = descriptionContent.substr(0, 260);
                description.textContent = shortText;
                document.getElementById("seeMoreButton").style.visibility = "visible";
                document.getElementById("seeMoreButton").textContent = "... See More";
            }
            return descriptionContent;
        }

        showDescription();

        function ucfirst(str) {
            if (!str) return str; // return the string if it's empty
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Close menu if clicking elsewhere
        document.addEventListener('click', () => {
            document.querySelectorAll('.moreMenu').forEach(m => m.style.display = 'none');
        });
    </script>
</body>
</html>