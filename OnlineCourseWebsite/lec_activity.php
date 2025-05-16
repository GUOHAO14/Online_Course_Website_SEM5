<?php
session_start();
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include "conn.php";

//to wy: set session variable
$lecturerId = $_SESSION["lecturer_id"];

//student and guest (lecturer pending) can retrieve and view courses
$courseSQL = "
SELECT * 
FROM course_proposal 
WHERE lecturer_id = $lecturerId;
";

$courseExe1 = mysqli_query($conn, $courseSQL);
$pending_course = [];
$rejected_course = [];
while ($row = mysqli_fetch_assoc($courseExe1)) {
    if($row["approval_status"] == "Pending"){
        $pending_course[] = $row;
    }else if($row["approval_status"] == "Rejected"){
        $rejected_course[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Learning</title>
    <link rel="stylesheet" href="lec_home.css">
    <style>
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 10000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* optional nice effect */
        }

        .whiteSectionBg {
            background-color: white;
            padding: 2.5rem 0;
            margin: 0 0 2.5rem 0;
        }

        body {
            position: relative; 
            padding-top: 7rem;
        }

        @media (max-width: 1160px) {
            body {
                padding-top: 5.6rem
            }
        }

        @media (max-width: 1010px) {
            body {
                padding-top: 5.2rem
            }
        }

        @media (max-width: 326px) {
            body {
                padding-top: 10.2rem
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include "lec_header.php";?>
    </header>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <div class="title">
                <button class="back" title="Back" onclick="back()"><img src="images/back.png" alt="Back" width="35px" height= "35px"></button>
            </div>

            <h1>Proposal</h1>

            <div class="course_area">
                <div class="status_area">
                    <button class="status_btn" onclick="pending()">Pending</button>
                    <button class="status_btn" onclick="rejected()">Rejected</button>
                </div>
                <div class="courseList1 tabCourse"> 
                    <?php
                    $count1 = 0; 
                    foreach ($pending_course as $course) {
                        echo 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['proposal_id'].'" onclick="clickCourse(\''.$course['proposal_id'].'\', \'pending\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                </div>
                            </div>
                        </div>';
                        $count1++;
                    }
                    if ($count1 === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>

                <div class="courseList2 tabCourse"> 
                    <?php
                    $count2 = 0; 
                    foreach ($rejected_course as $course) {
                        echo 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['proposal_id'].'" onclick="clickCourse(\''.$course['proposal_id'].'\', \'rejected\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                </div>
                            </div>
                        </div>';
                        $count2++;
                    }
                    if ($count2 === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.courseMain').forEach(main => {
            main.addEventListener('mouseenter', () => {
                main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
            });
            main.addEventListener('mouseleave', () => {
                main.closest('.courseCard').style.boxShadow = 'none';
            });
        });

        function back(){
            window.location.href = "lec_home.php";
        }
        
        //to wy: u change this function
        function clickCourse(proposal_id, status) {
            window.location.href = "lec_proposal.php?proposal_id=" + proposal_id + "&status=" + status;
        }

        function pending(){
            let publish_course = document.getElementsByClassName("courseList1")[0];
            publish_course.style.display = "grid";

            let unpublish_course = document.getElementsByClassName("courseList2")[0];
            unpublish_course.style.display = "none";
        }

        function rejected(){
            let publish_course = document.getElementsByClassName("courseList1")[0];
            publish_course.style.display = "none";

            let unpublish_course = document.getElementsByClassName("courseList2")[0];
            unpublish_course.style.display = "grid";
        }
    </script>
</body>
</html>